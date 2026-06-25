<?php

namespace Modules\Auth\DTOs;

use Spatie\LaravelData\Data;

class StoreLearnerData extends Data
{
    public function __construct(
        public int $user_id,
        public string $name_en,
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
    ) {}

    public static function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:auth.users,id'],
            'name_en' => ['required', 'string', 'max:255'],
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
        ];
    }
}
