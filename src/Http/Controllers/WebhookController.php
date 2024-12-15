<?php

namespace AMohamed\OfflineCashier\Http\Controllers;

use AMohamed\OfflineCashier\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function __construct(protected StripeService $stripeService)
    {
    }

    public function handleWebhook(Request $request): Response
    {
        try {
            $this->verifySignature($request);
            $this->stripeService->handleWebhook($request->all());

            return response('Webhook handled', 200);
        } catch (SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        } catch (\Exception $e) {
            return response('Webhook failed: ' . $e->getMessage(), 500);
        }
    }

    protected function verifySignature(Request $request): void
    {
        $signature = $request->header('Stripe-Signature');
        $secret = config('offline-cashier.stripe.webhook.secret');

        try {
            Webhook::constructEvent(
                $request->getContent(),
                $signature,
                $secret
            );
        } catch (\Exception $e) {
            throw new SignatureVerificationException('Invalid signature', $signature);
        }
    }
} 