<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProcessPaymentRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\AfterpayPaymentService;
use App\Services\Payment\CashOnDeliveryService;
use App\Services\Payment\PayPalPaymentService;
use App\Services\Payment\StripePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Payment Controller
 *
 * Handles payment processing for different gateways.
 *
 * @requirement CHK-009 Stripe payment
 * @requirement CHK-010 PayPal payment
 * @requirement CHK-011 Afterpay payment
 * @requirement CHK-012 Cash on Delivery
 */
class PaymentController extends Controller
{
    public function __construct(
        private readonly StripePaymentService $stripeService,
        private readonly PayPalPaymentService $paypalService,
        private readonly AfterpayPaymentService $afterpayService,
        private readonly CashOnDeliveryService $codService,
    ) {}

    /**
     * Get available payment methods.
     */
    public function getMethods(Request $request): JsonResponse
    {
        $user = $request->user();
        $subtotal = (float) $request->input('subtotal', 0);
        $currency = $request->input('currency', 'AUD');

        $methods = [
            [
                'id' => 'stripe',
                'name' => 'Credit/Debit Card',
                'description' => 'Pay securely with Visa, Mastercard, or American Express',
                'icon' => 'credit-card',
                'enabled' => $this->stripeService->isEnabled(),
                'currencies' => ['AUD', 'USD'],
            ],
            [
                'id' => 'paypal',
                'name' => 'PayPal',
                'description' => 'Pay with your PayPal account',
                'icon' => 'paypal',
                'enabled' => $this->paypalService->isEnabled(),
                'currencies' => ['AUD', 'USD'],
            ],
            [
                'id' => 'afterpay',
                'name' => 'Afterpay',
                'description' => 'Buy now, pay later in 4 interest-free installments',
                'icon' => 'afterpay',
                'enabled' => $this->afterpayService->isEnabled() && $currency === 'AUD',
                'currencies' => ['AUD'],
                'installments' => $subtotal > 0 ? AfterpayPaymentService::calculateInstallments($subtotal) : null,
                'min_amount' => 35.00,
                'max_amount' => 2000.00,
            ],
            [
                'id' => 'cod',
                'name' => 'Cash on Delivery',
                'description' => 'Pay with cash when your order arrives',
                'icon' => 'cash',
                'enabled' => $this->codService->isEnabled() && $currency === 'AUD',
                'currencies' => ['AUD'],
                'max_amount' => config('services.cod.max_amount', 500.00),
            ],
        ];

        return response()->json([
            'success' => true,
            'methods' => array_filter($methods, fn($m) => $m['enabled']),
        ]);
    }

    /**
     * Process Stripe payment.
     *
     * @requirement CHK-009 Integrate Stripe payment
     */
    public function processStripe(ProcessPaymentRequest $request): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('user_id', $user->id)
            ->where('id', $request->input('order_id'))
            ->where('status', Order::STATUS_PENDING)
            ->firstOrFail();

        $result = $this->stripeService->initiatePayment($order, $request->validated());

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * Confirm Stripe payment after 3D Secure or card confirmation.
     */
    public function confirmStripe(Request $request): JsonResponse
    {
        $request->validate([
            'payment_intent_id' => ['required', 'string'],
        ]);

        $payment = Payment::where('transaction_id', $request->input('payment_intent_id'))->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found.',
            ], 404);
        }

        // Verify user owns this order
        $user = $request->user();
        if ($user && $payment->order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $result = $this->stripeService->confirmPayment($payment, $request->all());

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed successfully.',
            'order' => new OrderResource($payment->order->fresh(['items', 'address', 'payment'])),
        ]);
    }

    /**
     * Process PayPal payment.
     *
     * @requirement CHK-010 Integrate PayPal payment
     */
    public function processPayPal(ProcessPaymentRequest $request): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('user_id', $user->id)
            ->where('id', $request->input('order_id'))
            ->where('status', Order::STATUS_PENDING)
            ->firstOrFail();

        $result = $this->paypalService->initiatePayment($order, $request->validated());

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * Confirm PayPal payment after redirect.
     */
    public function confirmPayPal(Request $request): JsonResponse
    {
        $request->validate([
            'paypal_order_id' => ['required', 'string'],
        ]);

        $payment = Payment::where('transaction_id', $request->input('paypal_order_id'))->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found.',
            ], 404);
        }

        $result = $this->paypalService->confirmPayment($payment, $request->all());

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'PayPal payment confirmed successfully.',
            'order' => new OrderResource($payment->order->fresh(['items', 'address', 'payment'])),
        ]);
    }

    /**
     * Process Afterpay payment.
     *
     * @requirement CHK-011 Integrate Afterpay payment
     */
    public function processAfterpay(ProcessPaymentRequest $request): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('user_id', $user->id)
            ->where('id', $request->input('order_id'))
            ->where('status', Order::STATUS_PENDING)
            ->firstOrFail();

        // Afterpay requires authenticated user
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Please log in to use Afterpay.',
            ], 401);
        }

        $result = $this->afterpayService->initiatePayment($order, $request->validated());

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * Confirm Afterpay payment after redirect.
     */
    public function confirmAfterpay(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'status' => ['sometimes', 'string'],
        ]);

        // Check if user cancelled
        if ($request->input('status') === 'CANCELLED') {
            return response()->json([
                'success' => false,
                'message' => 'Afterpay payment was cancelled.',
            ], 422);
        }

        $payment = Payment::where('transaction_id', $request->input('token'))->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found.',
            ], 404);
        }

        $result = $this->afterpayService->confirmPayment($payment, $request->all());

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Afterpay payment confirmed successfully.',
            'order' => new OrderResource($payment->order->fresh(['items', 'address', 'payment'])),
        ]);
    }

    /**
     * Process Cash on Delivery.
     *
     * @requirement CHK-012 Implement Cash on Delivery
     */
    public function processCod(ProcessPaymentRequest $request): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('user_id', $user->id)
            ->where('id', $request->input('order_id'))
            ->where('status', Order::STATUS_PENDING)
            ->firstOrFail();

        $result = $this->codService->initiatePayment($order, $request->validated());

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'order' => new OrderResource($order->fresh(['items', 'address', 'payment'])),
        ]);
    }

    /**
     * Get payment status for an order.
     */
    public function getStatus(Request $request, int $orderId): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('user_id', $user->id)
            ->where('id', $orderId)
            ->with('payment')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'order_status' => $order->status,
            'payment' => $order->payment ? new PaymentResource($order->payment) : null,
        ]);
    }
}
