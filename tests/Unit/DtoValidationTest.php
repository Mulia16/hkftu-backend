<?php

use Modules\Auth\DTOs\LoginRequestData;
use Tests\TestCase;

uses(TestCase::class);

it('creates login request data with valid input', function () {
    $data = LoginRequestData::from([
        'email' => 'test@example.com',
        'password' => 'secret123',
    ]);

    expect($data->email)->toBe('test@example.com');
    expect($data->password)->toBe('secret123');
});
