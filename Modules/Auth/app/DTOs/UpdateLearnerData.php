<?php

namespace Modules\Auth\DTOs;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateLearnerData extends Data
{
    public function __construct(
        public string|Optional $name_en,
        public ?string $name_zh = null,
        public ?string $id_type = null,
        public ?string $id_no = null,
        public ?string $dob = null,
        public ?string $gender = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $address = null,
        public ?string $emergency_contact_name = null,
        public ?string $emergency_contact_phone = null,
        public ?string $membership_no = null,
        public ?string $membership_status = null,
        public ?string $status = null,
    ) {}

    public static function rules(): array
    {
        return [
            'name_en' => ['sometimes', 'string', 'max:255'],
            'name_zh' => ['nullable', 'string', 'max:255'],
            'id_type' => ['nullable', 'string', 'max:20'],
            'id_no' => ['nullable', 'string', 'max:50'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'membership_no' => ['nullable', 'string', 'max:50'],
            'membership_status' => ['nullable', 'in:none,pending,active,expired'],
            'status' => ['nullable', 'in:active,inactive,archived'],
        ];
    }
}
