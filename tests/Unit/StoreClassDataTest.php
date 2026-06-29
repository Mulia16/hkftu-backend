<?php

use Modules\ClassScheduling\DTOs\StoreClassData;
use Tests\TestCase;

uses(TestCase::class);

it('has correct validation rules for required fields', function () {
    $rules = StoreClassData::rules();

    expect($rules)->toHaveKeys([
        'season_id',
        'subject_id',
        'class_code',
        'centre_id',
        'capacity',
        'start_date',
        'end_date',
    ]);

    expect($rules['season_id'])->toContain('required');
    expect($rules['subject_id'])->toContain('required');
    expect($rules['class_code'])->toContain('required');
    expect($rules['centre_id'])->toContain('required');
    expect($rules['capacity'])->toContain('required');
    expect($rules['start_date'])->toContain('required');
    expect($rules['end_date'])->toContain('required');
});

it('has correct validation rules for optional fields', function () {
    $rules = StoreClassData::rules();

    expect($rules['classroom_id'])->toContain('nullable');
    expect($rules['instructor_id'])->toContain('nullable');
    expect($rules['min_students'])->toContain('nullable');
    expect($rules['schedule_pattern'])->toContain('nullable');
});

it('has schedule_pattern nested validation rules', function () {
    $rules = StoreClassData::rules();

    expect($rules)->toHaveKeys([
        'schedule_pattern.type',
        'schedule_pattern.days_of_week',
        'schedule_pattern.start_time',
        'schedule_pattern.end_time',
    ]);

    expect($rules['schedule_pattern.type'])->toContain('in:weekly,one_off');
});
