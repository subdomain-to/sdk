<?php
namespace SubdomainTo\Resource;
use SubdomainTo\Model\CheckoutSession;
final class BillingResource extends Resource
{
    public function createCheckoutSession(string $plan, ?string $successUrl = null, ?string $cancelUrl = null): CheckoutSession
    {
        $body = ['plan' => $plan];
        if ($successUrl !== null) { $body['success_url'] = $successUrl; }
        if ($cancelUrl !== null) { $body['cancel_url'] = $cancelUrl; }
        return new CheckoutSession($this->data($this->client->send('POST', '/billing/checkout-session', [], $body)));
    }
}
