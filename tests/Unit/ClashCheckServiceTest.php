<?php

use Modules\ClassScheduling\Services\ClashCheckService;
use Tests\TestCase;

uses(TestCase::class);

it('has run method that accepts CourseClass', function () {
    $service = new ClashCheckService;

    expect(method_exists($service, 'run'))->toBeTrue();
});

it('has all 8 clash check methods via reflection', function () {
    $reflection = new ReflectionClass(ClashCheckService::class);

    $expectedMethods = [
        'checkClassroomDoubleBooking',
        'checkInstructorDoubleBooking',
        'checkCapacityVsRoom',
        'checkPageNoMissing',
        'checkRegistrationAfterClassStart',
        'checkDurationMismatch',
        'checkHolidayConflict',
        'checkInstructorMissingProfile',
    ];

    foreach ($expectedMethods as $method) {
        expect($reflection->hasMethod($method))->toBeTrue("Missing method: {$method}");
    }
});
