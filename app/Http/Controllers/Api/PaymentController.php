<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CoursePurchase;
use App\Models\Event;
use App\Models\EventBooking;
use App\Models\MembershipTier;
use App\Models\Transaction;
use App\Models\UserMembership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function checkout(Request $request)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }

        $data = $request->validate([
            'success_url' => 'nullable|string|max:500',
            'cancel_url' => 'nullable|string|max:500',
            'tax_amount' => 'nullable|numeric|min:0',
        ]);

        $cart = Cart::with('items')->where('user_id', $user->id)->first();
        if (!$cart || $cart->items->isEmpty()) {
            return ApiResponse::error('Your cart is empty.', [], 422);
        }

        $subtotal = (float) $cart->items->sum('price');
        $taxAmount = (float) ($data['tax_amount'] ?? 0);
        $totalAmount = $subtotal + $taxAmount;
        $currency = 'GBP';

        $successUrl = $data['success_url'] ?? rtrim(env('FRONTEND_URL', config('app.url')), '/') . '/account/cart/checkout?checkout=success&session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = $data['cancel_url'] ?? rtrim(env('FRONTEND_URL', config('app.url')), '/') . '/account/cart/checkout?checkout=cancel';

        if (!$this->isValidRedirectUrl($successUrl, true)) {
            return ApiResponse::error('The success url field must be a valid URL.', [], 422);
        }

        if (!$this->isValidRedirectUrl($cancelUrl, false)) {
            return ApiResponse::error('The cancel url field must be a valid URL.', [], 422);
        }

        $reference = 'TXN-' . strtoupper(Str::random(12));

        $transaction = DB::transaction(function () use ($user, $cart, $reference, $currency, $subtotal, $taxAmount, $totalAmount) {
            $tx = Transaction::create([
                'user_id' => $user->id,
                'cart_id' => $cart->id,
                'reference' => $reference,
                'gateway' => 'stripe',
                'currency' => $currency,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'metadata' => [
                    'items_count' => $cart->items->count(),
                ],
            ]);

            foreach ($cart->items as $item) {
                $tx->items()->create([
                    'item_type' => $item->item_type,
                    'item_id' => $item->item_id,
                    'title' => $item->title,
                    'unit_price' => (float) $item->price,
                    'quantity' => 1,
                    'line_total' => (float) $item->price,
                    'metadata' => $item->metadata,
                ]);
            }

            return $tx->load('items');
        });

        $stripeSecret = env('STRIPE_SECRET_KEY');
        if (!$stripeSecret) {
            return ApiResponse::error('Stripe is not configured on server.', [], 500);
        }

        $fields = [
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => $transaction->reference,
            'customer_email' => $user->email,
            'metadata[transaction_id]' => (string) $transaction->id,
            'metadata[reference]' => $transaction->reference,
            'payment_method_types[0]' => 'card',
        ];

        $lineIndex = 0;
        foreach ($transaction->items as $item) {
            $amountInMinor = (int) round(((float) $item->unit_price) * 100);
            if ($amountInMinor <= 0) {
                continue;
            }

            $fields["line_items[{$lineIndex}][quantity]"] = $item->quantity;
            $fields["line_items[{$lineIndex}][price_data][currency]"] = strtolower($currency);
            $fields["line_items[{$lineIndex}][price_data][unit_amount]"] = $amountInMinor;
            $fields["line_items[{$lineIndex}][price_data][product_data][name]"] = $item->title;
            $fields["line_items[{$lineIndex}][price_data][product_data][metadata][item_type]"] = $item->item_type;
            $fields["line_items[{$lineIndex}][price_data][product_data][metadata][item_id]"] = (string) $item->item_id;
            $lineIndex++;
        }

        if ($taxAmount > 0) {
            $fields["line_items[{$lineIndex}][quantity]"] = 1;
            $fields["line_items[{$lineIndex}][price_data][currency]"] = strtolower($currency);
            $fields["line_items[{$lineIndex}][price_data][unit_amount]"] = (int) round($taxAmount * 100);
            $fields["line_items[{$lineIndex}][price_data][product_data][name]"] = 'Tax';
        }

        try {
            $response = Http::asForm()
                ->withToken($stripeSecret)
                ->post('https://api.stripe.com/v1/checkout/sessions', $fields);

            if ($response->failed()) {
                $transaction->update([
                    'status' => 'failed',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'stripe_error' => $response->json(),
                    ]),
                ]);

                return ApiResponse::error('Unable to initiate payment with Stripe.', $response->json(), 422);
            }

            $session = $response->json();
            $transaction->update([
                'status' => 'processing',
                'stripe_session_id' => $session['id'] ?? null,
                'metadata' => array_merge($transaction->metadata ?? [], [
                    'stripe_checkout_url' => $session['url'] ?? null,
                ]),
            ]);

            return ApiResponse::success([
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference,
                'checkout_url' => $session['url'] ?? null,
                'session_id' => $session['id'] ?? null,
            ], 'Checkout session created successfully.');
        } catch (\Throwable $e) {
            Log::error('Stripe checkout session creation failed', [
                'message' => $e->getMessage(),
                'transaction_id' => $transaction->id,
            ]);

            $transaction->update(['status' => 'failed']);

            return ApiResponse::error('Failed to create Stripe checkout session.', [], 500);
        }
    }

    public function checkoutStatus(Request $request, string $sessionId)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return ApiResponse::error('Unauthenticated.', [], 401);
        }

        $transaction = Transaction::with('items')
            ->where('user_id', $user->id)
            ->where('stripe_session_id', $sessionId)
            ->first();

        if (!$transaction) {
            return ApiResponse::error('Transaction not found for this session.', [], 404);
        }

        $stripeSecret = env('STRIPE_SECRET_KEY');
        if ($stripeSecret) {
            try {
                $sessionResponse = Http::withToken($stripeSecret)
                    ->get("https://api.stripe.com/v1/checkout/sessions/{$sessionId}");

                if ($sessionResponse->ok()) {
                    $session = $sessionResponse->json();
                    $this->syncTransactionFromSession($transaction, $session);
                }
            } catch (\Throwable $e) {
                Log::warning('Stripe session status fetch failed', [
                    'session_id' => $sessionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return ApiResponse::success($transaction->fresh(['items']), 'Transaction status fetched.');
    }

    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        if (!$endpointSecret || !$this->isValidStripeSignature($payload, $signature, $endpointSecret)) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);
        $eventType = $event['type'] ?? null;
        $object = $event['data']['object'] ?? [];
        $sessionId = $object['id'] ?? null;

        if (!$sessionId) {
            return response()->json(['message' => 'No session id'], 200);
        }

        $transaction = Transaction::where('stripe_session_id', $sessionId)->first();
        if (!$transaction && !empty($object['metadata']['reference'])) {
            $transaction = Transaction::where('reference', $object['metadata']['reference'])->first();
        }

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 200);
        }

        if ($eventType === 'checkout.session.completed') {
            $this->syncTransactionFromSession($transaction, $object);
        } elseif (in_array($eventType, ['checkout.session.expired', 'payment_intent.payment_failed'], true)) {
            $transaction->update(['status' => 'failed']);
        }

        return response()->json(['received' => true]);
    }

    private function syncTransactionFromSession(Transaction $transaction, array $session): void
    {
        $paymentStatus = $session['payment_status'] ?? null;
        $paymentIntent = $session['payment_intent'] ?? null;

        if ($paymentStatus === 'paid') {
            $transaction->update([
                'status' => 'paid',
                'stripe_payment_intent_id' => is_string($paymentIntent) ? $paymentIntent : ($paymentIntent['id'] ?? null),
                'paid_at' => $transaction->paid_at ?: now(),
            ]);

            $this->fulfillTransaction($transaction->fresh('items'));
            $this->sendReceiptIfNeeded($transaction->fresh(['user', 'items']));
            return;
        }

        if (in_array($paymentStatus, ['unpaid', 'no_payment_required'], true)) {
            $transaction->update(['status' => 'processing']);
        }
    }

    private function fulfillTransaction(Transaction $transaction): void
    {
        $meta = $transaction->metadata ?? [];
        if (!empty($meta['fulfilled_at'])) {
            return;
        }

        DB::transaction(function () use ($transaction, $meta) {
            foreach ($transaction->items as $item) {
                if ($item->item_type === 'course') {
                    $course = Course::find($item->item_id);
                    if (!$course) {
                        continue;
                    }

                    CoursePurchase::firstOrCreate(
                        [
                            'user_id' => $transaction->user_id,
                            'course_id' => $course->id,
                        ],
                        [
                            'amount' => $item->unit_price,
                            'currency' => $transaction->currency,
                            'status' => 'paid',
                            'payment_reference' => $transaction->reference . '-C' . $course->id,
                            'metadata' => [
                                'gateway' => 'stripe',
                                'transaction_id' => $transaction->id,
                            ],
                            'paid_at' => $transaction->paid_at ?: now(),
                        ]
                    );

                    CourseEnrollment::firstOrCreate(
                        [
                            'user_id' => $transaction->user_id,
                            'course_id' => $course->id,
                        ],
                        [
                            'status' => 'enrolled',
                            'completion_percentage' => 0,
                            'time_spent_minutes' => 0,
                            'enrolled_at' => now(),
                        ]
                    );
                }

                if ($item->item_type === 'membership') {
                    $tier = MembershipTier::find($item->item_id);
                    if (!$tier) {
                        continue;
                    }

                    $userMembership = UserMembership::create([
                        'user_id' => $transaction->user_id,
                        'membership_id' => $tier->membership_id,
                        'membership_tier_id' => $tier->id,
                        'transaction_id' => $transaction->id,
                        'status' => 'active',
                        'approval_status' => 'pending',
                        'started_at' => now(),
                        'expires_at' => now()->addYear(),
                        'metadata' => [
                            'gateway' => 'stripe',
                            'reference' => $transaction->reference,
                        ],
                    ]);

                    $this->sendMembershipApprovalEmail($userMembership);
                }

                if ($item->item_type === 'event') {
                    $event = Event::find($item->item_id);
                    if (!$event) {
                        continue;
                    }

                    if ($event->capacity !== null) {
                        $confirmedCount = $event->confirmedBookings()->count();
                        if ($confirmedCount >= (int) $event->capacity) {
                            continue;
                        }
                    }

                    EventBooking::updateOrCreate(
                        [
                            'event_id' => $event->id,
                            'user_id' => $transaction->user_id,
                        ],
                        [
                            'status' => 'confirmed',
                        ]
                    );
                }
            }

            if ($transaction->cart_id) {
                $cart = Cart::with('items')->find($transaction->cart_id);
                if ($cart) {
                    $cart->items()->delete();
                    $cart->delete();
                }
            }

            $meta['fulfilled_at'] = now()->toDateTimeString();
            $transaction->metadata = $meta;
            $transaction->save();
        });
    }

    private function isValidStripeSignature(string $payload, string $signatureHeader, string $secret): bool
    {
        if (empty($signatureHeader)) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $segment) {
            [$k, $v] = array_pad(explode('=', $segment, 2), 2, null);
            if ($k && $v) {
                $parts[$k] = $v;
            }
        }

        $timestamp = $parts['t'] ?? null;
        $signature = $parts['v1'] ?? null;
        if (!$timestamp || !$signature) {
            return false;
        }

        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $signedPayload = $timestamp . '.' . $payload;
        $expected = hash_hmac('sha256', $signedPayload, $secret);

        return hash_equals($expected, $signature);
    }

    private function isValidRedirectUrl(string $url, bool $allowStripePlaceholder): bool
    {
        $normalized = trim($url);
        if ($normalized === '') {
            return false;
        }

        if ($allowStripePlaceholder) {
            $normalized = str_replace('{CHECKOUT_SESSION_ID}', 'cs_test_placeholder', $normalized);
        }

        if (!preg_match('/^https?:\/\//i', $normalized)) {
            return false;
        }

        return filter_var($normalized, FILTER_VALIDATE_URL) !== false;
    }

    private function sendReceiptIfNeeded(Transaction $transaction): void
    {
        $meta = $transaction->metadata ?? [];
        if (!empty($meta['receipt_emailed_at'])) {
            return;
        }

        $user = $transaction->user;
        if (!$user || empty($user->email)) {
            return;
        }

        $itemsBlock = $transaction->items
            ->map(function ($item) use ($transaction) {
                return "- {$item->title} ({$item->item_type}) - {$transaction->currency} " . number_format((float) $item->line_total, 2);
            })
            ->implode("\n");

        $paidAt = optional($transaction->paid_at)->format('Y-m-d H:i:s') ?: now()->format('Y-m-d H:i:s');
        $message = "Hello {$user->first_name},\n\n"
            . "Your payment has been received successfully.\n\n"
            . "Receipt Reference: {$transaction->reference}\n"
            . "Payment Date: {$paidAt}\n"
            . "Gateway: Stripe\n"
            . "Status: {$transaction->status}\n\n"
            . "Items:\n{$itemsBlock}\n\n"
            . "Subtotal: {$transaction->currency} " . number_format((float) $transaction->subtotal, 2) . "\n"
            . "Tax: {$transaction->currency} " . number_format((float) $transaction->tax_amount, 2) . "\n"
            . "Total: {$transaction->currency} " . number_format((float) $transaction->total_amount, 2) . "\n\n"
            . "Thank you for your payment.\nWGRCFP";

        try {
            Mail::raw($message, function ($mail) use ($user, $transaction) {
                $mail->to($user->email)
                    ->subject("WGRCFP Receipt - {$transaction->reference}");
            });

            $meta['receipt_emailed_at'] = now()->toDateTimeString();
            $transaction->metadata = $meta;
            $transaction->save();
        } catch (\Throwable $e) {
            Log::warning('Failed to send payment receipt email', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendMembershipApprovalEmail(UserMembership $userMembership): void
    {
        $adminEmail = config('mail.admin.address', 'enquiries@wgrcfp.org');
        if (!$adminEmail) {
            return;
        }

        $userMembership->loadMissing(['user', 'membership', 'tier', 'transaction']);
        $user = $userMembership->user;
        if (!$user) {
            return;
        }

        $approvalUrl = \URL::temporarySignedRoute(
            'memberships.email-approve',
            now()->addDays(7),
            ['userMembership' => $userMembership->id]
        );

        $data = [
            'user' => $user,
            'membership' => $userMembership->membership,
            'tier' => $userMembership->tier,
            'transaction' => $userMembership->transaction,
            'approvalUrl' => $approvalUrl,
        ];

        try {
            \Mail::send('emails.admin.membership-approval', $data, function ($mail) use ($adminEmail, $userMembership) {
                $mail->to($adminEmail)
                    ->subject("Membership Approval Needed #{$userMembership->id}");
            });
        } catch (\Throwable $e) {
            \Log::warning('Failed to send membership approval email', [
                'user_membership_id' => $userMembership->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
