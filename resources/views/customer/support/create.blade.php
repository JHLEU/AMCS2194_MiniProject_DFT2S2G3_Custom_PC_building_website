@extends('layouts.app')

@section('title', 'Submit Support & RMA Claim — GOPC')

@section('content')
<div class="container" style="padding-top: 36px; padding-bottom: 60px; max-width: 820px;">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 28px;">
        <a href="{{ route('customer.support.index') }}" class="btn btn-outline btn-sm" style="border-color: rgba(255,255,255,0.15); margin-bottom: 16px;">
            &larr; Back to Support Tickets
        </a>
        <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--primary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 6px;">
            Workshop Service & RMA Claim
        </div>
        <h1 style="font-size: 2rem; font-weight: 700; color: #fff; margin: 0; letter-spacing: -0.5px;">
            Submit Support & RMA Claim
        </h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 6px;">
            Describe the hardware issue you are experiencing. Our certified workshop technicians will diagnose and contact you.
        </p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 24px; border-radius: 8px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card" style="padding: 32px; border-color: rgba(255,255,255,0.08); background: var(--bg-card); position: relative; overflow: hidden; border-radius: 12px;">
        <!-- Top Green Accent Light Strip -->
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, transparent, var(--primary), transparent);"></div>

        <form action="{{ route('customer.support.store') }}" method="POST">
            @csrf

            <!-- Hardware / Warranty Selection -->
            <div class="form-group" style="margin-bottom: 22px;">
                <label for="warranty_id" class="form-label" style="font-size: 0.85rem; font-weight: 600;">Linked Hardware / Active Warranty (Optional)</label>
                <select name="warranty_id" id="warranty_id" class="form-input" style="height: 44px;">
                    <option value="">-- General Technical Consultation / Full PC Issue --</option>
                    @foreach($warranties as $warranty)
                        @php
                            $prod = $warranty->serialNumber->product ?? null;
                            $sn = $warranty->serialNumber->sn_code ?? '';
                            $isSelected = old('warranty_id', $selectedWarrantyId) == $warranty->id;
                        @endphp
                        <option value="{{ $warranty->id }}" {{ $isSelected ? 'selected' : '' }}>
                            {{ $prod->name ?? 'Hardware' }} [SN: {{ $sn }}] (Expires {{ $warranty->end_date ? $warranty->end_date->format('M Y') : '' }})
                        </option>
                    @endforeach
                </select>
                <small style="color: var(--text-muted); display: block; margin-top: 6px; font-size: 0.78rem;">
                    Select the specific component if you are filing a hardware defect or RMA replacement claim.
                </small>
            </div>

            <!-- Subject -->
            <div class="form-group" style="margin-bottom: 22px;">
                <label for="subject" class="form-label" style="font-size: 0.85rem; font-weight: 600;">Subject / Issue Summary <span style="color: var(--primary);">*</span></label>
                <input type="text" name="subject" id="subject" class="form-input" 
                       placeholder="e.g. PC powers on but no display signal / GPU artifacting during gaming"
                       value="{{ old('subject') }}" required maxlength="200" style="height: 44px;">
            </div>

            <!-- Description -->
            <div class="form-group" style="margin-bottom: 28px;">
                <label for="issue_description" class="form-label" style="font-size: 0.85rem; font-weight: 600;">Detailed Symptoms & Troubleshooting Steps Taken <span style="color: var(--primary);">*</span></label>
                <textarea name="issue_description" id="issue_description" rows="6" class="form-input"
                          placeholder="Please provide specifics: when did it start, error codes (BSOD), steps to reproduce, or any beeps/debug LEDs on the motherboard..."
                          required minlength="10" style="padding: 12px; line-height: 1.5;"></textarea>
            </div>

            <!-- Action buttons -->
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 14px; border-top: 1px solid var(--border-color); padding-top: 24px;">
                <a href="{{ route('customer.support.index') }}" class="btn btn-outline" style="border-color: rgba(255,255,255,0.15);">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 28px; font-weight: 700; border-radius: 8px; box-shadow: 0 4px 16px rgba(34,197,94,0.3);">
                    Submit Support Ticket &rarr;
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
