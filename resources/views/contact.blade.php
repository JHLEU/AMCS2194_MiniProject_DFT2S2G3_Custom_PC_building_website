@extends('layouts.app')

@section('title', 'Contact Us & Workshop Location')

@section('content')
<div class="container" style="padding-top: 40px; padding-bottom: 60px; max-width: 1000px;">
    <!-- Breadcrumb & Title -->
    <div style="text-align: center; margin-bottom: 40px;">
        <span class="badge badge-primary" style="margin-bottom: 8px;">Support & Customer Service</span>
        <h1 style="font-size: 2.2rem; font-weight: 800; color: #fff; margin-bottom: 10px;">
            Get in Touch With Our Hardware Specialists
        </h1>
        <p style="color: var(--text-muted); font-size: 1.05rem; max-width: 600px; margin: 0 auto;">
            Need help selecting components, tracking your order, or booking a walk-in workbench diagnostic? We are here to assist.
        </p>
    </div>

    <!-- Quick Action Channels -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 40px;">
        <!-- WhatsApp Direct -->
        <div class="card" style="margin-bottom: 0; padding: 28px; text-align: center; border-top: 4px solid #25D366;">
            <div style="font-size: 2.5rem; margin-bottom: 12px;">💬</div>
            <h3 style="color: #fff; font-size: 1.2rem; margin-bottom: 6px;">WhatsApp Instant Chat</h3>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 18px;">
                Chat live with our PC building consultants for instant quotations and compatibility checks.
            </p>
            <a href="{{ $contactInfo['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer" class="btn" style="background-color: #25D366; color: #fff; font-weight: 600; width: 100%;">
                Chat on WhatsApp ({{ $contactInfo['whatsapp_phone'] }})
            </a>
        </div>

        <!-- Phone Hotline -->
        <div class="card" style="margin-bottom: 0; padding: 28px; text-align: center; border-top: 4px solid #3b82f6;">
            <div style="font-size: 2.5rem; margin-bottom: 12px;">📞</div>
            <h3 style="color: #fff; font-size: 1.2rem; margin-bottom: 6px;">Phone Hotline</h3>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 18px;">
                Speak with our technical workshop desk for immediate inquiries during operating hours.
            </p>
            <a href="tel:{{ str_replace(' ', '', $contactInfo['hotline']) }}" class="btn btn-secondary" style="width: 100%; font-weight: 600;">
                Call: {{ $contactInfo['hotline'] }}
            </a>
        </div>

        <!-- Support Ticket -->
        <div class="card" style="margin-bottom: 0; padding: 28px; text-align: center; border-top: 4px solid #8b5cf6;">
            <div style="font-size: 2.5rem; margin-bottom: 12px;">🛠️</div>
            <h3 style="color: #fff; font-size: 1.2rem; margin-bottom: 6px;">RMA & Warranty Ticket</h3>
            <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 18px;">
                Have a hardware defect or technical issue? File a 2-year warranty claim in our support portal.
            </p>
            <a href="{{ route('customer.support.create') }}" class="btn btn-primary" style="width: 100%; font-weight: 600;">
                File RMA Claim Ticket →
            </a>
        </div>
    </div>

    <!-- Store & Workshop Details Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- Location & Hours -->
        <div class="card" style="padding: 28px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 16px;">
                📍 Workshop Location & Hours
            </h3>

            <div style="margin-bottom: 16px;">
                <span style="color: var(--text-muted); font-size: 0.85rem; display: block;">Physical Store Address</span>
                <strong style="color: #fff; font-size: 0.95rem; line-height: 1.5; display: block; margin-top: 2px;">
                    {{ $contactInfo['address'] }}
                </strong>
            </div>

            <div style="margin-bottom: 16px;">
                <span style="color: var(--text-muted); font-size: 0.85rem; display: block;">Workshop Operating Hours</span>
                <span style="color: #10b981; font-weight: 600; font-size: 0.9rem; display: block; margin-top: 2px;">
                    {{ $contactInfo['operating_hours'] }}
                </span>
            </div>

            <div style="border-top: 1px solid var(--border-color); padding-top: 16px; margin-top: 16px;">
                <span style="color: var(--text-muted); font-size: 0.85rem; display: block;">On-Site Services Available</span>
                <p style="color: var(--text-light); font-size: 0.875rem; margin-top: 4px; line-height: 1.5;">
                    {{ $contactInfo['workshop_service'] }}
                </p>
            </div>
        </div>

        <!-- Electronic Inquiries & Tracking -->
        <div class="card" style="padding: 28px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: #fff; margin-bottom: 16px;">
                    ✉️ Department Inquiries
                </h3>

                <div style="margin-bottom: 14px;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Sales & Corporate Fleet Quotes:</span>
                    <div>
                        <a href="mailto:{{ $contactInfo['sales_email'] }}" style="color: #60a5fa; font-weight: 600; text-decoration: none;">
                            {{ $contactInfo['sales_email'] }}
                        </a>
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Technical Support & Warranty RMA:</span>
                    <div>
                        <a href="mailto:{{ $contactInfo['support_email'] }}" style="color: #60a5fa; font-weight: 600; text-decoration: none;">
                            {{ $contactInfo['support_email'] }}
                        </a>
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <span style="color: var(--text-muted); font-size: 0.85rem;">Courier Dispatch Status:</span>
                    <div>
                        <a href="{{ route('orders.track') }}" style="color: var(--info); font-weight: 600; text-decoration: none;">
                            Check Live Order Tracking →
                        </a>
                    </div>
                </div>
            </div>

            <div style="background: rgba(0,0,0,0.25); border-radius: 8px; padding: 14px; font-size: 0.85rem; color: var(--text-muted);">
                💡 <strong>Walk-in advice:</strong> For warranty RMA hardware drops, please bring along your order tracking number or serial number code for instant processing.
            </div>
        </div>
    </div>
</div>
@endsection
