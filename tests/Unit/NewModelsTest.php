<?php

use Modules\Auth\Models\ConsentRecord;
use Modules\Auth\Models\DataAccessLog;
use Modules\Auth\Models\MemberPricingEligibility;
use Modules\Auth\Models\MemberVerification;
use Tests\TestCase;

uses(TestCase::class);

it('ConsentRecord model exists with correct table', function () {
    $model = new ConsentRecord;

    expect($model->getTable())->toBe('auth.consent_records');
    expect($model->getUpdatedAtColumn())->toBeNull();
});

it('DataAccessLog model exists with correct table', function () {
    $model = new DataAccessLog;

    expect($model->getTable())->toBe('auth.data_access_logs');
    expect($model->getUpdatedAtColumn())->toBeNull();
});

it('MemberVerification model exists with correct table', function () {
    $model = new MemberVerification;

    expect($model->getTable())->toBe('auth.member_verifications');
});

it('MemberVerification casts raw arrays correctly', function () {
    $model = new MemberVerification;

    $casts = $model->getCasts();

    expect($casts)->toHaveKey('request_data');
    expect($casts['request_data'])->toBe('array');
    expect($casts)->toHaveKey('response_data');
    expect($casts['response_data'])->toBe('array');
});

it('MemberPricingEligibility model exists with correct table', function () {
    $model = new MemberPricingEligibility;

    expect($model->getTable())->toBe('auth.member_pricing_eligibilities');
});

it('MemberPricingEligibility has correct fillable fields', function () {
    $model = new MemberPricingEligibility;

    expect($model->getFillable())->toContain('learner_profile_id');
    expect($model->getFillable())->toContain('member_verification_id');
    expect($model->getFillable())->toContain('member_type');
    expect($model->getFillable())->toContain('pricing_rule');
    expect($model->getFillable())->toContain('discount_percentage');
    expect($model->getFillable())->toContain('is_active');
});
