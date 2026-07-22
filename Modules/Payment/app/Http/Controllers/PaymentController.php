<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiError;
use App\Support\Ownership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\AuditLogger;
use Modules\Enrolment\Models\Enrolment;
use Modules\Enrolment\Models\SeatReservation;
use Modules\Payment\DTOs\ManualUploadData;
use Modules\Payment\DTOs\StorePaymentIntentData;
use Modules\Payment\Models\PaymentIntent;
use Modules\Payment\Services\PaymentService;
use Modules\Payment\Services\RazerMsService;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private AuditLogger $auditLogger,
    ) {}

    public function processRazerMs(Request $request): JsonResponse
    {
        $request->validate(['reservation_id' => ['required', 'integer']]);

        $reservation = SeatReservation::findOrFail($request->input('reservation_id'));
        $user = $request->user();

        if (! Ownership::canAccessLearner($user, $reservation->learner_id)) {
            return Ownership::forbidden();
        }

        $enrolment = $this->paymentService->ensureEnrolmentForReservation($reservation);

        $orderId = 'HKFTU-'.time().'-'.$reservation->id;

        $intent = PaymentIntent::create([
            'enrolment_id' => $enrolment->id,
            'amount' => $enrolment->price_snapshot_json['total'] ?? 0,
            'currency' => 'HKD',
            'method' => 'razerms',
            'status' => 'pending',
            'gateway' => 'razerms',
            'gateway_intent_id' => $orderId,
            'expires_at' => now()->addMinutes(30),
        ]);

        $rms = app(RazerMsService::class);
        $returnUrl = url('/payment/return');

        try {
            $paymentUrl = $rms->getPaymentUrl(
                orderId: $orderId,
                amount: (float) $intent->amount,
                currency: 'HKD',
                billName: $user->name,
                billEmail: $user->email,
                billDesc: 'HKFTU Class Registration',
                returnUrl: $returnUrl,
            );

            return response()->json(['data' => ['payment_url' => $paymentUrl]]);
        } catch (\Exception $e) {
            $intent->update(['status' => 'failed']);

            return ApiError::respond('GATEWAY_ERROR', 'Payment gateway error: '.$e->getMessage(), 502);
        }
    }

    public function createIntent(StorePaymentIntentData $data, Request $request): JsonResponse
    {
        $ownerLearnerId = $data->reservation_id
            ? SeatReservation::find($data->reservation_id)?->learner_id
            : Enrolment::find($data->enrolment_id)?->learner_id;

        if (! Ownership::canAccessLearner($request->user(), $ownerLearnerId)) {
            return Ownership::forbidden();
        }

        $intent = $this->paymentService->createIntent($data, $request->user()?->id);

        $this->auditLogger->record('payment.intent.create', 'payment_intent', $intent->id, after: $intent->toArray());

        return response()->json(['data' => $intent], 201);
    }

    public function showIntent(Request $request, int $id): JsonResponse
    {
        $intent = PaymentIntent::with(['enrolment.courseClass.course.subject', 'transactions', 'receipt'])->findOrFail($id);

        if (! Ownership::canAccessLearner($request->user(), $intent->enrolment?->learner_id)) {
            return Ownership::forbidden();
        }

        return response()->json(['data' => $intent]);
    }

    public function uploadProof(Request $request): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => ['required', 'integer'],
            'payment_proof' => ['required', 'image', 'max:5120'],
        ]);

        $intent = PaymentIntent::findOrFail($request->input('payment_intent_id'));

        if ($intent->status !== 'pending') {
            return ApiError::respond('INVALID_STATUS', 'Payment intent is not pending.', 422);
        }

        $transaction = $this->paymentService->uploadProof(
            new ManualUploadData(
                payment_intent_id: (int) $request->input('payment_intent_id'),
                payment_proof: $request->file('payment_proof'),
            ),
            $request->user()->id,
        );

        $this->auditLogger->record('payment.proof.upload', 'payment_transaction', $transaction->id, after: $transaction->toArray());

        return response()->json(['data' => $transaction], 201);
    }

    public function myPayments(Request $request): JsonResponse
    {
        $learner = $request->user()->learnerProfile;

        if (! $learner) {
            return ApiError::respond('NO_LEARNER_PROFILE', 'No learner profile found.', 404);
        }

        $payments = $this->paymentService->listForLearner($learner->id);

        return response()->json($payments);
    }

    public function index(Request $request): JsonResponse
    {
        $payments = $this->paymentService->listAll(
            $request->input('status'),
            $request->integer('enrolment_id'),
        );

        return response()->json($payments);
    }

    public function handleGatewayCallback(Request $request): JsonResponse
    {
        $intent = PaymentIntent::where('gateway_intent_id', $request->input('orderid'))
            ->firstOrFail();

        if ($intent->status === 'paid') {
            return response()->json(['data' => $intent->load('receipt')]);
        }

        $rms = app(RazerMsService::class);

        $key = md5(
            $request->input('tranID').
            $request->input('orderid').
            $request->input('status').
            $request->input('domain').
            $request->input('amount').
            $request->input('currency')
        );

        $isValid = $rms->verifySignature(
            $request->input('paydate'),
            $request->input('domain'),
            $key,
            $request->input('appcode'),
            $request->input('skey'),
        );

        if (! $isValid) {
            $intent->update(['status' => 'failed']);

            return ApiError::respond('INVALID_SIGNATURE', 'Payment signature verification failed.', 422);
        }

        if ($request->input('status') != '00') {
            $intent->update(['status' => 'failed']);

            return ApiError::respond('PAYMENT_FAILED', 'Payment was not completed.', 422);
        }

        if ((float) $request->input('amount') != (float) $intent->amount) {
            $intent->update(['status' => 'failed']);

            return ApiError::respond('AMOUNT_MISMATCH', 'Payment amount mismatch.', 422);
        }

        $this->paymentService->confirmGatewayPayment($intent, $request->input('tranID'));

        return response()->json(['data' => $intent->fresh(['enrolment', 'receipt'])]);
    }

    public function handleGatewayReturn(Request $request): JsonResponse
    {
        $intent = PaymentIntent::with('receipt')
            ->where('gateway_intent_id', $request->input('orderid'))
            ->first();

        if (! $intent) {
            return ApiError::respond('INTENT_NOT_FOUND', 'Payment intent not found.', 404);
        }

        if ($intent->status !== 'paid' && $request->filled('skey')) {
            $rms = app(RazerMsService::class);

            $key = md5(
                $request->input('tranID').
                $request->input('orderid').
                $request->input('status').
                $request->input('domain').
                $request->input('amount').
                $request->input('currency')
            );

            $isValid = $rms->verifySignature(
                $request->input('paydate'),
                $request->input('domain'),
                $key,
                $request->input('appcode'),
                $request->input('skey'),
            );

            if ($isValid
                && $request->input('status') == '00'
                && (float) $request->input('amount') === (float) $intent->amount) {
                $this->paymentService->confirmGatewayPayment($intent, $request->input('tranID'));
                $intent->refresh();
            }
        }

        return response()->json(['data' => [
            'status' => $intent->status,
            'paid' => $intent->status === 'paid',
            'receipt' => $intent->fresh('receipt')->receipt,
        ]]);
    }
}
