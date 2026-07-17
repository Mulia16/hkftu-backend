<?php

namespace Modules\CourseCatalogue\Enums;

enum CourseTextStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Approved = 'approved';
    case Published = 'published';
    case Archived = 'archived';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Review],
            self::Review => [self::Approved, self::Draft],
            self::Approved => [self::Published, self::Review],
            self::Published => [self::Archived],
            self::Archived => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}
