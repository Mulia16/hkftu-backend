<?php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\ClassScheduling\Models\Centre;

class LearnerProfile extends Model
{
    protected $table = 'auth.learner_profiles';

    protected $fillable = [
        'user_id',
        'name_en',
        'name_zh',
        'id_type',
        'id_no_encrypted',
        'dob',
        'gender',
        'phone',
        'email',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'membership_no',
        'membership_status',
        'membership_verified_at',
        'status',
        'centre_id',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'membership_verified_at' => 'datetime',
            'id_no_encrypted' => 'encrypted',
        ];
    }

    protected $hidden = ['id_no_encrypted'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function centre(): BelongsTo
    {
        return $this->belongsTo(Centre::class);
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(DependentProfile::class);
    }

    public function memberSnapshots(): HasMany
    {
        return $this->hasMany(MemberStatusSnapshot::class);
    }

    public function latestSnapshot(): HasOne
    {
        return $this->hasOne(MemberStatusSnapshot::class)->latestOfMany();
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(MemberVerification::class);
    }

    public function pricingEligibilities(): HasMany
    {
        return $this->hasMany(MemberPricingEligibility::class);
    }
}
