<?php

namespace App\Services\Payment;

use App\Models\Payment\PaymentProvider;
use App\Services\Payment\Adapters\BankTransferManualAdapter;
use App\Services\Payment\Adapters\CashAdapter;
use App\Services\Payment\Adapters\EwalletDeeplinkAdapter;
use App\Services\Payment\Adapters\QrisDynamicAdapter;
use App\Services\Payment\Adapters\QrisStaticAdapter;
use App\Services\Payment\Adapters\RedirectCheckoutAdapter;
use App\Services\Payment\Adapters\VirtualAccountAdapter;
use App\Services\Payment\Contracts\PaymentAdapterInterface;
use App\Services\Payment\Support\GatewayHttpClient;
use App\Services\Payment\Support\SignatureVerifier;

class PaymentAdapterFactory
{
    public function for(PaymentProvider $provider): PaymentAdapterInterface
    {
        $http     = new GatewayHttpClient($provider);
        $verifier = new SignatureVerifier($provider);

        return match ($provider->api_format) {
            PaymentProvider::FORMAT_REDIRECT_CHECKOUT    => new RedirectCheckoutAdapter($provider, $http, $verifier),
            PaymentProvider::FORMAT_VIRTUAL_ACCOUNT      => new VirtualAccountAdapter($provider, $http, $verifier),
            PaymentProvider::FORMAT_EWALLET_DEEPLINK     => new EwalletDeeplinkAdapter($provider, $http, $verifier),
            PaymentProvider::FORMAT_QRIS_DYNAMIC         => new QrisDynamicAdapter($provider, $http, $verifier),
            PaymentProvider::FORMAT_QRIS_STATIC          => new QrisStaticAdapter($provider, $http, $verifier),
            PaymentProvider::FORMAT_BANK_TRANSFER_MANUAL => new BankTransferManualAdapter($provider, $http, $verifier),
            PaymentProvider::FORMAT_CASH                 => new CashAdapter($provider, $http, $verifier),
            default => throw new \InvalidArgumentException("Unsupported api_format: {$provider->api_format}"),
        };
    }
}
