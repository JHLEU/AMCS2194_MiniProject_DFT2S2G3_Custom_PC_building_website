@extends('layouts.app')

@section('title', 'Secure Checkout & Payment Gateway')

@section('content')
<div style="max-width: 1200px; margin: 30px auto; padding: 0 20px;">
    <div style="margin-bottom: 24px;">
        <span style="font-size: 0.8rem; color: var(--accent); font-weight: 700; text-transform: uppercase;">Direct Hardware Checkout</span>
        <h1 style="font-size: 1.8rem; font-weight: 800; color: #fff; margin-top: 4px;">
            💳 Secure Cashier & Payment Gateway
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem;">
            Review your custom PC configuration, enter delivery details, and settle payment via our sandbox checkout.
        </p>
    </div>

    <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr 400px; gap: 28px; align-items: start;">
            <!-- Left Column: Shipping & Payment Method -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <!-- 1. Customer Shipping Details -->
                <div class="card">
                    <h3 class="card-title" style="margin-bottom: 16px;">📦 1. Delivery & Contact Information</h3>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                        <div class="form-group">
                            <label for="customer_name">Full Name *</label>
                            <input type="text" name="customer_name" id="customer_name" class="form-input"
                                   value="{{ old('customer_name', $defaultCustomer['name']) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="customer_phone">Phone / WhatsApp *</label>
                            <input type="text" name="customer_phone" id="customer_phone" class="form-input"
                                   value="{{ old('customer_phone', $defaultCustomer['phone']) }}" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 14px;">
                        <label for="customer_email">Email Address (For Invoices & Tracking) *</label>
                        <input type="email" name="customer_email" id="customer_email" class="form-input"
                               value="{{ old('customer_email', $defaultCustomer['email']) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="shipping_address">Delivery Address / Pickup Location *</label>
                        <textarea name="shipping_address" id="shipping_address" class="form-input" rows="3" required>{{ old('shipping_address', $defaultCustomer['address']) }}</textarea>
                    </div>
                </div>

                <!-- 2. Payment Method Selector -->
                <div class="card">
                    <h3 class="card-title" style="margin-bottom: 16px;">💳 2. Payment Gateway Method</h3>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 18px;">
                        Select a payment simulator. Real Stripe credit card tokenization will be enabled after the initial system rollout.
                    </p>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 12px; margin-bottom: 18px;">
                        @foreach($paymentMethods as $idx => $pm)
                            <label class="payment-method-card {{ $pm['enabled'] ? '' : 'disabled' }}"
                                   style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 10px; padding: 16px; display: flex; align-items: center; justify-content: space-between; cursor: {{ $pm['enabled'] ? 'pointer' : 'not-allowed' }}; transition: border-color 0.2s; opacity: {{ $pm['enabled'] ? '1' : '0.6' }};">
                                <div style="display: flex; align-items: center; gap: 14px;">
                                    <input type="radio" name="payment_method" value="{{ $pm['id'] }}"
                                           {{ $idx === 0 ? 'checked' : '' }}
                                           {{ $pm['enabled'] ? '' : 'disabled' }}
                                           style="accent-color: var(--primary); width: 18px; height: 18px;">
                                    <div>
                                        <div style="font-weight: 700; color: #fff; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                                            <span>{{ $pm['icon'] }}</span>
                                            <span>{{ $pm['name'] }}</span>
                                            <span class="badge {{ $pm['badge_class'] }}" style="font-size: 0.7rem;">{{ $pm['badge'] }}</span>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">
                                            {{ $pm['desc'] }}
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <!-- Sandbox Notice -->
                    <div class="alert alert-info" style="font-size: 0.85rem;">
                        <strong>ℹ️ Developer Notice:</strong> This checkout uses a local mock transaction sandbox. All hardware serial numbers are authentically reserved and allocated upon placement without live billing charges.
                    </div>
                </div>
            </div>

            <!-- Right Column: Sticky Order Summary & Action -->
            <div style="position: sticky; top: 20px;">
                <div class="card">
                    <h3 class="card-title" style="margin-bottom: 16px;">🧾 Hardware Items</h3>

                    <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; max-height: 380px; overflow-y: auto;">
                        @foreach($items as $i => $item)
                            <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item['product']->id }}">
                            <input type="hidden" name="items[{{ $i }}][quantity]" value="{{ $item['quantity'] }}">
                            <input type="hidden" name="items[{{ $i }}][unit_price]" value="{{ $item['unit_price'] }}">

                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed rgba(255,255,255,0.08); padding-bottom: 8px; font-size: 0.85rem;">
                                <div style="max-width: 240px;">
                                    <div style="font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $item['product']->name }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        {{ $item['product']->category }} &bull; Qty: {{ $item['quantity'] }}
                                    </div>
                                </div>
                                <div style="font-weight: 700; color: var(--accent);">
                                    RM {{ number_format($item['subtotal'], 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div style="border-top: 1px solid var(--border-color); padding-top: 14px; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.85rem; color: var(--text-muted);">
                            <span>Subtotal (Hardware)</span>
                            <span>RM {{ number_format($totalAmount, 2) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.85rem; color: var(--text-muted);">
                            <span>Assembly & Stress Testing</span>
                            <span style="color: var(--success); font-weight: 600;">FREE (Included)</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.85rem; color: var(--text-muted);">
                            <span>Express Insured Shipping</span>
                            <span style="color: var(--success); font-weight: 600;">FREE (Covered)</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 12px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 10px;">
                            <span style="font-size: 1rem; font-weight: 700; color: #fff;">Grand Total (MYR)</span>
                            <span style="font-size: 1.5rem; font-weight: 800; color: var(--accent);">
                                RM {{ number_format($totalAmount, 2) }}
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="btnPlaceOrder" style="width: 100%; justify-content: center; padding: 14px; font-size: 1rem; font-weight: 800;">
                        💳 Confirm & Settle Order &rarr;
                    </button>

                    <div style="text-align: center; margin-top: 12px; font-size: 0.75rem; color: var(--text-muted);">
                        🔒 256-Bit SSL Encrypted Sandbox Flow
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
