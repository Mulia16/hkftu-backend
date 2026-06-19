<?php

namespace Modules\CourseCatalogue\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\CourseCatalogue\Models\CourseTextVersion;
use Modules\CourseCatalogue\Models\Subject;

class CourseTextController extends Controller
{
    private const ALLOWED_TAGS = '<p><br><strong><em><u><s><ul><ol><li><h2><h3><h4><blockquote><a><span>';

    public function index(int $subjectId): JsonResponse
    {
        $subject = Subject::findOrFail($subjectId);

        $versions = CourseTextVersion::with('subject')
            ->where('subject_id', $subject->id)
            ->orderByDesc('version_no')
            ->get();

        return response()->json(['data' => $versions]);
    }

    public function store(Request $request, int $subjectId): JsonResponse
    {
        $subject = Subject::findOrFail($subjectId);

        $data = $request->validate([
            'content_html' => 'required|string',
            'status'       => 'sometimes|in:draft,review,approved,published,archived',
        ]);

        $lastVersion = CourseTextVersion::where('subject_id', $subject->id)->max('version_no') ?? 0;

        $version = CourseTextVersion::create([
            'subject_id'   => $subject->id,
            'version_no'   => $lastVersion + 1,
            'content_html' => strip_tags($data['content_html'], self::ALLOWED_TAGS),
            'status'       => $data['status'] ?? 'draft',
        ]);

        return response()->json(['data' => $version], 201);
    }

    public function update(Request $request, int $subjectId, int $versionId): JsonResponse
    {
        $version = CourseTextVersion::where('subject_id', $subjectId)->findOrFail($versionId);

        $data = $request->validate([
            'status' => 'required|in:draft,review,approved,published,archived',
        ]);

        if ($data['status'] === 'published') {
            CourseTextVersion::where('subject_id', $subjectId)
                ->where('status', 'published')
                ->where('id', '!=', $version->id)
                ->update(['status' => 'archived']);

            $version->published_at = now();
        }

        $version->update($data);

        return response()->json(['data' => $version]);
    }
}
