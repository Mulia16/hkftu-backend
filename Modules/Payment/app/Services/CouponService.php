<?php

namespace Modules\Payment\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Payment\Models\CouponCampaign;
use Modules\Payment\Models\CouponCode;
use Modules\Payment\Models\CouponRedemption;

class CouponService
{
    public function validate(string $code, float $baseAmount, ?int $userId = null): array
    {
        $couponCode = CouponCode::with('campaign')->where('code', $code)->first();

        if (! $couponCode) {
            $campaign = CouponCampaign::where('code', $code)->first();
            if ($campaign) {
                $couponCode = $campaign->codes()->first();
            }
        }

        if (! $couponCode) {
            return ['valid' => false, 'reason' => 'Coupon code not found.'];
        }

        if (! $couponCode->isUsable()) {
            return ['valid' => false, 'reason' => 'Coupon code is not available.'];
        }

        $campaign = $couponCode->campaign;

        if (! $campaign || ! $campaign->isAvailable()) {
            return ['valid' => false, 'reason' => 'Coupon campaign is not active.'];
        }

        if ($couponCode->assigned_to && $userId && $couponCode->assigned_to !== $userId) {
            return ['valid' => false, 'reason' => 'This coupon is assigned to another user.'];
        }

        $rules = $campaign->rules_json ?? [];

        if (isset($rules['per_user_limit']) && $userId) {
            $userRedemptions = CouponRedemption::where('coupon_code_id', $couponCode->id)
                ->where('redeemed_by', $userId)
                ->whereNull('voided_at')
                ->count();

            if ($userRedemptions >= $rules['per_user_limit']) {
                return ['valid' => false, 'reason' => 'You have reached the usage limit for this coupon.'];
            }
        }

        $discount = $campaign->calculateDiscount($baseAmount);

        return [
            'valid' => true,
            'coupon_code_id' => $couponCode->id,
            'campaign_id' => $campaign->id,
            'discount_type' => $campaign->discount_type,
            'discount_value' => $campaign->value,
            'amount_discounted' => $discount,
            'final_amount' => max(0, $baseAmount - $discount),
        ];
    }

    public function redeem(string $code, int $enrolmentId, float $baseAmount, int $userId, ?int $paymentIntentId = null): CouponRedemption
    {
        $validation = $this->validate($code, $baseAmount, $userId);

        if (! $validation['valid']) {
            throw new \RuntimeException($validation['reason']);
        }

        return DB::transaction(function () use ($code, $enrolmentId, $userId, $paymentIntentId, $validation) {
            $couponCode = CouponCode::where('code', $code)->lockForUpdate()->firstOrFail();

            $couponCode->increment('used_count');

            $couponCode->campaign()->increment('used_count');

            return CouponRedemption::create([
                'coupon_code_id' => $couponCode->id,
                'enrolment_id' => $enrolmentId,
                'payment_intent_id' => $paymentIntentId,
                'amount_discounted' => $validation['amount_discounted'],
                'redeemed_by' => $userId,
                'redeemed_at' => now(),
            ]);
        });
    }

    public function void(int $redemptionId): void
    {
        $redemption = CouponRedemption::findOrFail($redemptionId);

        if ($redemption->voided_at) {
            return;
        }

        $redemption->update(['voided_at' => now()]);

        $couponCode = $redemption->couponCode;
        if ($couponCode) {
            $couponCode->decrement('used_count');
            $couponCode->campaign()->decrement('used_count');
        }
    }

    public function listAll(?string $status = null): LengthAwarePaginator
    {
        return CouponCampaign::with('season')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(25);
    }

    public function create(array $data): CouponCampaign
    {
        $campaign = CouponCampaign::create($data);

        CouponCode::create([
            'campaign_id' => $campaign->id,
            'code' => $campaign->code,
            'code_hash' => hash('sha256', $campaign->code),
            'status' => 'active',
            'usage_limit' => $data['max_usage'] ?? 1,
        ]);

        return $campaign;
    }

    public function update(int $id, array $data): CouponCampaign
    {
        $campaign = CouponCampaign::findOrFail($id);
        $campaign->update($data);

        return $campaign;
    }

    public function generateCodes(int $campaignId, int $count, int $usageLimit = 1): array
    {
        $campaign = CouponCampaign::findOrFail($campaignId);
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(bin2hex(random_bytes(4)));
            $codes[] = CouponCode::create([
                'campaign_id' => $campaign->id,
                'code' => $code,
                'code_hash' => hash('sha256', $code),
                'status' => 'active',
                'usage_limit' => $usageLimit,
            ]);
        }

        return $codes;
    }
}
