<?php

namespace Modules\CourseCatalogue\Services;

use Modules\CourseCatalogue\Enums\CourseTextStatus;
use Modules\CourseCatalogue\Models\CourseTextVersion;

class CourseTextService
{
    private const ALLOWED_TAGS = '<p><br><strong><em><u><s><ul><ol><li><h2><h3><h4><blockquote><a><span>';

    public function create(int $subjectId, string $contentHtml, ?CourseTextStatus $status = null): CourseTextVersion
    {
        $lastVersion = CourseTextVersion::where('subject_id', $subjectId)->max('version_no') ?? 0;

        return CourseTextVersion::create([
            'subject_id' => $subjectId,
            'version_no' => $lastVersion + 1,
            'content_html' => $this->sanitizeHtml($contentHtml),
            'status' => ($status ?? CourseTextStatus::Draft)->value,
        ]);
    }

    public function transition(CourseTextVersion $version, CourseTextStatus $target, int $userId): CourseTextVersion
    {
        $current = CourseTextStatus::from($version->status);

        if (! $current->canTransitionTo($target)) {
            throw new \DomainException(
                "Cannot transition from '{$current->value}' to '{$target->value}'."
            );
        }

        $updates = ['status' => $target->value];

        if ($target === CourseTextStatus::Approved) {
            $updates['approved_by'] = $userId;
        }

        if ($target === CourseTextStatus::Published) {
            $this->archiveOtherPublished($version->subject_id, $version->id);
            $updates['published_at'] = now();
        }

        $version->update($updates);

        return $version->refresh();
    }

    private function archiveOtherPublished(int $subjectId, int $excludeId): void
    {
        CourseTextVersion::where('subject_id', $subjectId)
            ->where('status', CourseTextStatus::Published->value)
            ->where('id', '!=', $excludeId)
            ->update(['status' => CourseTextStatus::Archived->value]);
    }

    private function sanitizeHtml(string $html): string
    {
        $cleaned = strip_tags($html, self::ALLOWED_TAGS);

        $cleaned = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $cleaned);
        $cleaned = preg_replace('/on\w+\s*=\s*\S+/i', '', $cleaned);
        $cleaned = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', 'href="#"', $cleaned);
        $cleaned = preg_replace('/style\s*=\s*["\'][^"\']*expression\s*\([^"\']*["\']/i', '', $cleaned);

        return $cleaned;
    }
}
