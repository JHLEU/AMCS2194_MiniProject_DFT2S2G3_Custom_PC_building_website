@extends('layouts.app')

@section('title', 'Secure Checkout — GOPC')

@section('content')
<div class="container" style="padding-top: 36px; padding-bottom: 60px;">
    <!-- Section Title & Status Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--primary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--primary);"></span>
                Hardware Order Cashier
            </div>
            <h1 style="font-size: 2rem; font-weight: 700; color: #fff; margin: 0; letter-spacing: -0.5px;">
                Secure Checkout & Payment
            </h1>
        </div>
        <div>
            <a href="{{ route('builder.index') }}" class="btn btn-outline btn-sm" style="border-color: rgba(255,255,255,0.15);">
                &larr; Back to Builder
            </a>
        </div>
    </div>

    <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
        @csrf
        <div style="display: grid; grid-template-columns: 1fr 420px; gap: 28px; align-items: start;">
            <!-- Left Column: Shipping & Payment Method -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <!-- 1. Customer Shipping Details -->
                <div class="card" style="margin-bottom: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                        <h3 style="font-size: 1.1rem; font-weight: 700; color: #fff; margin: 0;">
                            1. Delivery & Contact Details
                        </h3>
                        <small style="color: var(--text-muted); font-size: 0.8rem;">* Required fields</small>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="customer_name" class="form-label">Full Name *</label>
                            <input type="text" name="customer_name" id="customer_name" class="form-input"
                                   value="{{ old('customer_name', $defaultCustomer['name']) }}" placeholder="Enter recipient full name" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="customer_phone" class="form-label">Phone / WhatsApp *</label>
                            <input type="text" name="customer_phone" id="customer_phone" class="form-input"
                                   value="{{ old('customer_phone', $defaultCustomer['phone']) }}" placeholder="+60 12-345 6789" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="customer_email" class="form-label">Email Address (For Serial Manifest & Tracking) *</label>
                        <input type="email" name="customer_email" id="customer_email" class="form-input"
                               value="{{ old('customer_email', $defaultCustomer['email']) }}" placeholder="name@domain.com" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="shipping_address" class="form-label">Shipping Destination / Workshop Pickup Address *</label>
                        <textarea name="shipping_address" id="shipping_address" class="form-input" rows="3" placeholder="Unit, Street Address, Postcode, City, State" required>{{ old('shipping_address', $defaultCustomer['address']) }}</textarea>
                    </div>
                </div>

                <!-- 2. Payment Method Selector -->
                <div class="card" style="margin-bottom: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                        <h3 style="font-size: 1.1rem; font-weight: 700; color: #fff; margin: 0;">
                            2. Payment Gateway Mode
                        </h3>
                        <span class="badge badge-primary" style="font-size: 0.7rem;">Verified Protocol</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr; gap: 12px; margin-bottom: 18px;">
                        @foreach($paymentMethods as $idx => $pm)
                            <label class="payment-method-card {{ $pm['enabled'] ? '' : 'disabled' }}"
                                   style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 10px; padding: 16px; display: flex; align-items: center; justify-content: space-between; cursor: {{ $pm['enabled'] ? 'pointer' : 'not-allowed' }}; transition: all 0.2s; opacity: {{ $pm['enabled'] ? '1' : '0.5' }};">
                                <div style="display: flex; align-items: center; gap: 14px;">
                                    <input type="radio" name="payment_method" value="{{ $pm['id'] }}"
                                           {{ $idx === 0 ? 'checked' : '' }}
                                           {{ $pm['enabled'] ? '' : 'disabled' }}
                                           style="accent-color: var(--primary); width: 18px; height: 18px;">
                                    <div>
                                        <div style="font-weight: 600; color: #fff; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                                            <span>{{ $pm['name'] }}</span>
                                            <span class="badge {{ $pm['badge_class'] }}" style="font-size: 0.7rem;">{{ $pm['badge'] }}</span>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                            {{ $pm['desc'] }}
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="alert alert-info" style="font-size: 0.825rem; margin-bottom: 0;">
                        <strong>Developer Notice:</strong> This checkout runs in a dedicated local sandbox. Authentic component serial numbers will be allocated without live credit card charges.
                    </div>
                </div>
            </div>

            <!-- Right Column: Sticky Order Summary & Action -->
            <div style="position: sticky; top: 80px;">
                <div class="card" style="box-shadow: 0 10px 30px rgba(0,0,0,0.5); border-color: rgba(34,197,94,0.25);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                        <h3 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin: 0;">
                            Hardware Summary
                        </h3>
                        <span class="badge badge-secondary" style="font-family: monospace;">{{ count($items) }} Parts</span>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; max-height: 340px; overflow-y: auto;">
                        @foreach($items as $i => $item)
                            <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item['product']->id }}">
                            <input type="hidden" name="items[{{ $i }}][quantity]" value="{{ $item['quantity'] }}">

                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed rgba(255,255,255,0.06); padding-bottom: 8px; font-size: 0.85rem;">
                                <div style="max-width: 250px;">
                                    <div style="font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $item['product']->name }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">
                                        {{ $item['product']->category }} • Qty: {{ $item['quantity'] }}
                                    </div>
                                </div>
                                <div style="font-weight: 600; color: #fff; font-family: monospace;">
                                    RM {{ number_format($item['subtotal'], 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div style="border-top: 1px solid var(--border-color); padding-top: 14px; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.85rem; color: var(--text-muted);">
                            <span>Subtotal (Hardware)</span>
                            <span style="font-family: monospace;">RM {{ number_format($totalAmount, 2) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.85rem; color: var(--text-muted);">
                            <span>Assembly & 24hr Stress Test</span>
                            <span style="color: var(--primary); font-weight: 600;">FREE (Included)</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.85rem; color: var(--text-muted);">
                            <span>Insured Expandable Shipping</span>
                            <span style="color: var(--primary); font-weight: 600;">FREE (Covered)</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 12px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 12px;">
                            <span style="font-size: 1rem; font-weight: 700; color: #fff;">Grand Total</span>
                            <span style="font-size: 1.6rem; font-weight: 800; color: var(--primary); font-family: monospace;">
                                RM {{ number_format($totalAmount, 2) }}
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="btnPlaceOrder" style="width: 100%; justify-content: center; padding: 14px; font-size: 1rem; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 16px rgba(34,197,94,0.3);">
                        Place & Confirm Order &rarr;
                    </button>

                    <div style="text-align: center; margin-top: 12px; font-size: 0.75rem; color: var(--text-muted);">
                        256-Bit SSL Encrypted Sandbox Session
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
