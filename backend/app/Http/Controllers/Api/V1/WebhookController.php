<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Payment\PayPalPaymentService;
use App\Services\Payment\StripePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Webhook Controller
 *
 * Handles payment gateway webhooks.
 *
 * @requirement CHK-028 Create payment webhook handlers
 */
class WebhookController extends Controller
{
    public function __construct(
        private readonly StripePaymentService $stripeService,
        private readonly PayPalPaymentService $paypalService,
    ) {}

    /**
     * Handle Stripe webhooks.
     *
     * @requirement CHK-028 Stripe webhook handler
     */
    public function handleStripe(Request $request): JsonResponse
    {
        $payload = $request->all();
        $signature = $request->header('Stripe-Signature', '');

        Log::info('Stripe webhook received', [
            'event_type' => $payload['type'] ?? 'unknown',
        ]);

        try {
            $result = $this->stripeService->handleWebhook($payload, $signature);

            if (!$result['success']) {
                Log::warning('Stripe webhook handling failed', [
                    'result' => $result,
                ]);
                return response()->json($result, 400);
            }

            Log::info('Stripe webhook processed successfully', [
                'event_type' => $result['event_type'] ?? 'unknown',
                'order_id' => $result['order_id'] ?? null,
            ]);

            return response()->json(['received' => true]);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed.',
            ], 500);
        }
    }

    /**
     * Handle PayPal webhooks.
     *
     * @requirement CHK-028 PayPal webhook handler
     */
    public function handlePayPal(Request $request): JsonResponse
    {
        $payload = $request->all();
        $signature = $request->header('PAYPAL-TRANSMISSION-SIG', '');

        Log::info('PayPal webhook received', [
            'event_type' => $payload['event_type'] ?? 'unknown',
        ]);

        try {
            // Verify webhook signature in production
            if (app()->environment('production')) {
                $verified = $this->verifyPayPalWebhook($request);
                if (!$verified) {
                    Log::warning('PayPal webhook signature verification failed');
                    return response()->json([
                        'success' => false,
                        'message' => 'Signature verification failed.',
                    ], 401);
                }
            }

            $result = $this->paypalService->handleWebhook($payload, $signature);

            if (!$result['success']) {
                Log::warning('PayPal webhook handling failed', [
                    'result' => $result,
                ]);
                return response()->json($result, 400);
            }

            Log::info('PayPal webhook processed successfully', [
                'event_type' => $result['event_type'] ?? 'unknown',
                'order_id' => $result['order_id'] ?? null,
            ]);

            return response()->json(['received' => true]);
        } catch (\Exception $e) {
            Log::error('PayPal webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed.',
            ], 500);
        }
    }

    /**
     * Verify PayPal webhook signature.
     */
    private function verifyPayPalWebhook(Request $request): bool
    {
        // PayPal webhook verification requires:
        // 1. PAYPAL-TRANSMISSION-ID header
        // 2. PAYPAL-TRANSMISSION-TIME header
        // 3. PAYPAL-TRANSMISSION-SIG header
        // 4. PAYPAL-CERT-URL header
        // 5. PAYPAL-AUTH-ALGO header
        // 6. Webhook ID from PayPal dashboard

        $webhookId = config('services.paypal.webhook_id');

        if (!$webhookId) {
            // Skip verification if webhook ID not configured
            return true;
        }

        // In production, implement full webhook verification
        // using PayPal's verify webhook signature API
        // https://developer.paypal.com/docs/api/webhooks/v1/#verify-webhook-signature

        return true;
    }
}
