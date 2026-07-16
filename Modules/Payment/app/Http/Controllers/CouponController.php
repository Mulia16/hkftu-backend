<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\AuditLogger;
use Modules\Payment\Models\CouponCampaign;
use Modules\Payment\Services\CouponService;

class CouponController extends Controller
{
    public function __construct(
        private CouponService $couponService,
        private AuditLogger $auditLogger,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $coupons = $this->couponService->listAll($request->input('status'));

        return response()->json($coupons);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'season_id' => ['nullable', 'integer'],
            'discount_type' => ['required', 'in:fixed,percentage,full_subsidy,material_waiver'],
            'value' => ['required', 'numeric', 'min:0'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'max_usage' => ['nullable', 'integer', 'min:1'],
            'rules_json' => ['nullable', 'array'],
        ]);

        $campaign = $this->couponService->create($request->all());

        $this->auditLogger->record('coupon.create', 'coupon_campaign', $campaign->id, after: $campaign->toArray());

        return response()->json(['data' => $campaign], 201);
    }

    public function show(int $id): JsonResponse
    {
        $campaign = CouponCampaign::with(['season', 'codes'])->findOrFail($id);

        return response()->json(['data' => $campaign]);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'discount_type' => ['sometimes', 'in:fixed,percentage,full_subsidy,material_waiver'],
            'value' => ['sometimes', 'numeric', 'min:0'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date'],
            'max_usage' => ['nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'in:active,paused,expired'],
        ]);

        $campaign = $this->couponService->update($id, $request->all());

        $this->auditLogger->record('coupon.update', 'coupon_campaign', $id, after: $campaign->toArray());

        return response()->json(['data' => $campaign]);
    }

    public function generateCodes(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:100'],
            'usage_limit' => ['sometimes', 'integer', 'min:1'],
        ]);

        $codes = $this->couponService->generateCodes(
            $id,
            $request->input('count', 1),
            $request->input('usage_limit', 1),
        );

        return response()->json(['data' => $codes], 201);
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $result = $this->couponService->validate(
            $request->input('code'),
            (float) $request->input('amount'),
            $request->user()?->id,
        );

        return response()->json(['data' => $result]);
    }

    public function redeem(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
            'enrolment_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_intent_id' => ['nullable', 'integer'],
        ]);

        $redemption = $this->couponService->redeem(
            $request->input('code'),
            $request->input('enrolment_id'),
            (float) $request->input('amount'),
            $request->user()->id,
            $request->input('payment_intent_id'),
        );

        $this->auditLogger->record('coupon.redeem', 'coupon_redemption', $redemption->id, after: $redemption->toArray());

        return response()->json(['data' => $redemption], 201);
    }
}
