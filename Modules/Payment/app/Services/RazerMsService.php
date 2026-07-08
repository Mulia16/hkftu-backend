<?php

namespace Modules\Payment\Services;

use RazerMerchantServices\Payment;

class RazerMsService
{
    private Payment $rms;

    public function __construct()
    {
        $this->rms = new Payment(
            config('services.razor_ms.merchant_id'),
            config('services.razor_ms.verify_key'),
            config('services.razor_ms.secret_key'),
            config('services.razor_ms.environment'),
        );
    }

    public function getPaymentUrl(
        string $orderId,
        float $amount,
        string $currency,
        string $billName,
        string $billEmail,
        string $billDesc,
        string $returnUrl,
    ): string {
        return $this->rms->getPaymentUrl(
            orderid: $orderId,
            amount: $amount,
            bill_name: $billName,
            bill_email: $billEmail,
            bill_mobile: '',
            bill_desc: $billDesc,
            currency: $currency,
            returnUrl: $returnUrl,
        );
    }

    public function verifySignature(
        string $paydate,
        string $domain,
        string $key,
        string $appcode,
        string $skey,
    ): bool {
        return $this->rms->verifySignature($paydate, $domain, $key, $appcode, $skey);
    }
}
