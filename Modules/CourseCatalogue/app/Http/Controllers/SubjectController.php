<?php

namespace Modules\CourseCatalogue\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Services\AuditLogger;
use Modules\CourseCatalogue\DTOs\StoreSubjectData;
use Modules\CourseCatalogue\Models\Subject;

class SubjectController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function index(Request $request): JsonResponse
    {
        $query = Subject::with('categories')
            ->when($request->search, fn ($q) => $q->where('name', 'ilike', '%'.$request->search.'%')
                ->orWhere('subject_code', 'ilike', '%'.$request->search.'%'))
            ->when($request->status, fn ($q) => $q->where('status', $request->status));

        $subjects = $query->orderBy('subject_code')->paginate(25);

        return response()->json(['data' => $subjects]);
    }

    public function show(Subject $subject): JsonResponse
    {
        $subject->load('categories');

        return response()->json(['data' => $subject]);
    }

    public function store(StoreSubjectData $data): JsonResponse
    {
        $validated = $data->toArray();
        $categoryIds = $validated['category_ids'] ?? [];
        unset($validated['category_ids']);

        $subject = DB::transaction(function () use ($validated, $categoryIds) {
            DB::statement('SELECT pg_advisory_xact_lock(1001)');

            $maxNum = Subject::withTrashed()
                ->whereRaw("subject_code ~ '^S[0-9]+$'")
                ->selectRaw('COALESCE(MAX(CAST(SUBSTR(subject_code, 2) AS INTEGER)), 0) as max_num')
                ->value('max_num');

            $validated['subject_code'] = sprintf('S%03d', $maxNum + 1);
            $subject = Subject::create($validated);

            if ($categoryIds) {
                $subject->categories()->sync($categoryIds);
            }

            return $subject;
        });

        $subject->load('categories');

        $this->auditLogger->record('subject.create', 'subject', $subject->id, after: $subject->toArray());

        return response()->json(['data' => $subject], 201);
    }

    public function update(Request $request, Subject $subject): JsonResponse
    {
        $validated = $request->validate([
            'subject_code' => 'sometimes|string|max:20|unique:course_catalogue.subjects,subject_code,'.$subject->id,
            'name' => 'sometimes|string|max:255',
            'tuition_fee' => 'sometimes|numeric|min:0',
            'material_fee' => 'nullable|numeric|min:0',
            'instructor_fee_default' => 'nullable|numeric|min:0',
            'total_hours' => 'sometimes|numeric|min:0.5',
            'lesson_hours' => 'sometimes|numeric|min:0.5',
            'prerequisites' => 'nullable|array',
            'prerequisites.*' => 'string|max:100',
            'certificate_eligible' => 'nullable|boolean',
            'status' => 'nullable|in:draft,active,inactive',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:course_catalogue.categories,id',
        ]);

        $categoryIds = $validated['category_ids'] ?? null;
        unset($validated['category_ids']);

        $before = $subject->toArray();

        $subject->update($validated);

        if ($categoryIds !== null) {
            $subject->categories()->sync($categoryIds);
        }

        $subject->load('categories');

        $this->auditLogger->record('subject.update', 'subject', $subject->id, before: $before, after: $subject->toArray());

        return response()->json(['data' => $subject]);
    }

    public function destroy(Subject $subject): JsonResponse
    {
        $subject->delete();

        $this->auditLogger->record('subject.delete', 'subject', $subject->id);

        return response()->json(['data' => ['message' => 'Subject deleted.']]);
    }
}
