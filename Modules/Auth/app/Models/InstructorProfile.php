<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorProfile extends Model
{
    protected $table = 'auth.instructor_profiles';

    protected $fillable = [
        'user_id',
        'instructor_no',
        'name',
        'phone',
        'email',
        'bank_name',
        'bank_account_no',
        'bank_account_name',
        'cheque_payable_to',
        'qualifications',
        'categories',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'qualifications' => 'array',
            'categories' => 'array',
        ];
    }

    protected $hidden = ['bank_account_no'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
