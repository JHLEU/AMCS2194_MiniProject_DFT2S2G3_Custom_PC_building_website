@extends('layouts.app')

@section('title', 'Submit Support Ticket')

@section('content')
<div class="container" style="padding-top: 40px; padding-bottom: 60px; max-width: 800px;">
    <!-- Breadcrumb -->
    <div style="margin-bottom: 24px;">
        <a href="{{ route('customer.support.index') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;">
            ← Back to Support Tickets
        </a>
        <h1 style="font-size: 1.8rem; font-weight: 700; margin-top: 8px; margin-bottom: 6px;">
            🛠️ Submit Support & RMA Claim
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">
            Describe the issue you are experiencing. Our certified workshop technicians will diagnose and contact you.
        </p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card" style="padding: 28px;">
        <form action="{{ route('customer.support.store') }}" method="POST">
            @csrf

            <!-- Hardware / Warranty Selection -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="warranty_id" class="form-label">Linked Hardware / Active Warranty (Optional)</label>
                <select name="warranty_id" id="warranty_id" class="form-control">
                    <option value="">-- General Technical Consultation / Full PC Issue --</option>
                    @foreach($warranties as $warranty)
                        @php
                            $prod = $warranty->serialNumber->product ?? null;
                            $sn = $warranty->serialNumber->serial_number ?? '';
                            $isSelected = old('warranty_id', $selectedWarrantyId) == $warranty->id;
                        @endphp
                        <option value="{{ $warranty->id }}" {{ $isSelected ? 'selected' : '' }}>
                            {{ $prod->name ?? 'Hardware' }} [SN: {{ $sn }}] (Expires {{ $warranty->end_date ? $warranty->end_date->format('M Y') : '' }})
                        </option>
                    @endforeach
                </select>
                <small style="color: var(--text-muted); display: block; margin-top: 6px;">
                    Select the specific component if you are filing a hardware defect/RMA replacement claim.
                </small>
            </div>

            <!-- Subject -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="subject" class="form-label">Subject / Issue Summary <span style="color: var(--danger);">*</span></label>
                <input type="text" name="subject" id="subject" class="form-control" 
                       placeholder="e.g. PC powers on but no display signal / GPU artifacting during gaming"
                       value="{{ old('subject') }}" required maxlength="200">
            </div>

            <!-- Description -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label for="issue_description" class="form-label">Detailed Symptoms & Troubleshooting Steps Taken <span style="color: var(--danger);">*</span></label>
                <textarea name="issue_description" id="issue_description" rows="6" class="form-control"
                          placeholder="Please provide specifics: when did it start, error codes (BSOD), steps to reproduce, or any beeps/debug LEDs on the motherboard..."
                          required minlength="10">{{ old('issue_description') }}</textarea>
            </div>

            <!-- Action buttons -->
            <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                <a href="{{ route('customer.support.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                    Submit Support Ticket →
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
