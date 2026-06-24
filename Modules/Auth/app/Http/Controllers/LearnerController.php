<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Models\DependentProfile;
use Modules\Auth\Models\LearnerProfile;
use Modules\Auth\Services\AuditLogger;

class LearnerController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function index(Request $request): JsonResponse
    {
        $query = LearnerProfile::with('user')
            ->when($request->search, function ($q) use ($request) {
                $q->where('name_en', 'ilike', "%{$request->search}%")
                    ->orWhere('name_zh', 'ilike', "%{$request->search}%")
                    ->orWhere('membership_no', 'ilike', "%{$request->search}%");
            })
            ->when($request->membership_status, fn ($q) => $q->where('membership_status', $request->membership_status))
            ->orderByDesc('created_at');

        return response()->json(['data' => $query->paginate($request->integer('per_page', 25))]);
    }

    public function show(int $id): JsonResponse
    {
        $profile = LearnerProfile::with(['user', 'dependents.learnerProfile', 'latestSnapshot'])->findOrFail($id);

        return response()->json(['data' => $profile]);
    }

    public function myProfile(Request $request): JsonResponse
    {
        $profile = LearnerProfile::with(['dependents.learnerProfile', 'latestSnapshot'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $profile) {
            return ApiError::respond('PROFILE_NOT_FOUND', 'Learner profile not found.', 404);
        }

        return response()->json(['data' => $profile]);
    }

    public function updateMyProfile(Request $request): JsonResponse
    {
        $profile = LearnerProfile::where('user_id', $request->user()->id)->first();

        if (! $profile) {
            return ApiError::respond('PROFILE_NOT_FOUND', 'Learner profile not found.', 404);
        }

        $validated = $request->validate([
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:30',
        ]);

        $before = $profile->only(array_keys($validated));
        $profile->update($validated);

        $this->auditLogger->record('learner.profile_update', 'learner_profile', $profile->id, $before, $validated);

        return response()->json(['data' => $profile->load('user')]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:auth.users,id',
            'name_en' => 'required|string|max:255',
            'name_zh' => 'nullable|string|max:255',
            'id_type' => 'nullable|string|max:20',
            'id_no' => 'nullable|string|max:50',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'membership_no' => 'nullable|string|max:50',
        ]);

        $existing = LearnerProfile::where('user_id', $validated['user_id'])->first();
        if ($existing) {
            return ApiError::respond('DUPLICATE_PROFILE', 'Learner profile already exists for this user.', 409);
        }

        if (isset($validated['id_no'])) {
            $validated['id_no_encrypted'] = encrypt($validated['id_no']);
            unset($validated['id_no']);
        }

        $profile = LearnerProfile::create($validated);

        $this->auditLogger->record('learner.create', 'learner_profile', $profile->id, after: $profile->toArray());

        return response()->json(['data' => $profile->load('user')], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $profile = LearnerProfile::findOrFail($id);

        $validated = $request->validate([
            'name_en' => 'sometimes|string|max:255',
            'name_zh' => 'nullable|string|max:255',
            'id_type' => 'nullable|string|max:20',
            'id_no' => 'nullable|string|max:50',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'membership_no' => 'nullable|string|max:50',
            'membership_status' => 'nullable|in:none,pending,active,expired',
            'status' => 'nullable|in:active,inactive,archived',
        ]);

        $before = $profile->toArray();

        if (isset($validated['id_no'])) {
            $validated['id_no_encrypted'] = encrypt($validated['id_no']);
            unset($validated['id_no']);
        }

        $profile->update($validated);

        $this->auditLogger->record('learner.update', 'learner_profile', $profile->id, $before, $profile->toArray());

        return response()->json(['data' => $profile->load('user')]);
    }

    public function storeDependent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'learner_profile_id' => 'required|exists:auth.learner_profiles,id',
            'relationship' => 'nullable|in:parent,guardian,other',
        ]);

        $dependent = DependentProfile::create([
            'guardian_user_id' => $request->user()->id,
            'learner_profile_id' => $validated['learner_profile_id'],
            'relationship' => $validated['relationship'] ?? 'parent',
            'consent_at' => now(),
        ]);

        $this->auditLogger->record('dependent.create', 'dependent_profile', $dependent->id);

        return response()->json(['data' => $dependent->load('learnerProfile')], 201);
    }

    public function myDependents(Request $request): JsonResponse
    {
        $dependents = DependentProfile::with('learnerProfile')
            ->where('guardian_user_id', $request->user()->id)
            ->get();

        return response()->json(['data' => $dependents]);
    }
}
