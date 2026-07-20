<?php

namespace Modules\CourseCatalogue\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\CourseCatalogue\Enums\CourseTextStatus;
use Modules\CourseCatalogue\Models\CourseTextVersion;
use Modules\CourseCatalogue\Models\Subject;
use Modules\CourseCatalogue\Services\CourseTextService;

class CourseTextController extends Controller
{
    public function __construct(
        private CourseTextService $courseTextService,
    ) {}

    public function index(int $subjectId): JsonResponse
    {
        $subject = Subject::findOrFail($subjectId);

        $versions = CourseTextVersion::with('subject')
            ->where('subject_id', $subject->id)
            ->orderByDesc('version_no')
            ->get();

        return response()->json(['data' => $versions]);
    }

    public function show(int $subjectId, int $versionId): JsonResponse
    {
        $version = CourseTextVersion::with('subject')
            ->where('subject_id', $subjectId)
            ->findOrFail($versionId);

        return response()->json(['data' => $version]);
    }

    public function store(Request $request, int $subjectId): JsonResponse
    {
        $subject = Subject::findOrFail($subjectId);

        $data = $request->validate([
            'content_html' => 'required|string',
            'status' => 'sometimes|in:draft,review,approved,published,archived',
        ]);

        $status = isset($data['status']) ? CourseTextStatus::from($data['status']) : null;

        $version = $this->courseTextService->create($subject->id, $data['content_html'], $status);

        return response()->json(['data' => $version], 201);
    }

    public function update(Request $request, int $subjectId, int $versionId): JsonResponse
    {
        $version = CourseTextVersion::where('subject_id', $subjectId)->findOrFail($versionId);

        $data = $request->validate([
            'status' => 'required|in:draft,review,approved,published,archived',
        ]);

        $target = CourseTextStatus::from($data['status']);
        $user = $request->user();
        $roles = $user->roles->pluck('name')->toArray();
        $currentStatus = $version->status->value;

        $isPlanner = in_array('course_planner', $roles) || in_array('system_admin', $roles);
        $isManager = in_array('centre_manager', $roles) || in_array('system_admin', $roles);

        $allowed = match (true) {
            $currentStatus === 'draft' && $target === CourseTextStatus::Review => $isPlanner,
            $currentStatus === 'review' && $target === CourseTextStatus::Draft => $isPlanner,
            $currentStatus === 'review' && $target === CourseTextStatus::Approved => $isManager,
            $currentStatus === 'approved' && $target === CourseTextStatus::Review => $isManager,
            $currentStatus === 'approved' && $target === CourseTextStatus::Published => $isManager,
            default => false,
        };

        if (!$allowed) {
            return response()->json([
                'error' => [
                    'code' => 'FORBIDDEN_TRANSITION',
                    'message' => "Your role cannot change status from '{$currentStatus}' to '{$target->value}'.",
                ],
            ], 403);
        }

        try {
            $version = $this->courseTextService->transition($version, $target, $user->id);
        } catch (\DomainException $e) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_TRANSITION',
                    'message' => $e->getMessage(),
                ],
            ], 422);
        }

        return response()->json(['data' => $version]);
    }
}
