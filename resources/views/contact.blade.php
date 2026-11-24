@extends('layouts.app')

@section('title', 'Contact Us & Technical Workshop — GOPC')

@section('content')
<div class="container" style="padding-top: 40px; padding-bottom: 70px; max-width: 1080px; margin: 0 auto;">
    <!-- Title & Subtitle -->
    <div style="text-align: center; margin-bottom: 44px;">
        <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.78rem; color: var(--primary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; font-family: monospace;">
            <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--primary); box-shadow: 0 0 8px var(--primary);"></span>
            Service Desk & Support Channels
        </div>
        <h1 style="font-size: 2.2rem; font-weight: 800; color: #fff; margin: 0 0 10px; letter-spacing: -0.5px;">
            Get in Touch With Our Hardware Specialists
        </h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 620px; margin: 0 auto; line-height: 1.5;">
            Need help configuring components, inspecting build compatibility, or scheduling a walk-in workbench diagnostic? We are at your service.
        </p>
    </div>

    <!-- Quick Action Channels Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(290px, 1fr)); gap: 20px; margin-bottom: 36px;">
        <!-- WhatsApp Direct -->
        <div class="card" style="margin-bottom: 0; padding: 28px 24px; text-align: center; border-color: rgba(37, 211, 102, 0.25); background: var(--bg-card); position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: #25D366;"></div>
            <div>
                <div style="width: 48px; height: 48px; margin: 0 auto 16px; border-radius: 12px; background: rgba(37, 211, 102, 0.1); border: 1px solid rgba(37, 211, 102, 0.2); display: flex; align-items: center; justify-content: center; color: #25D366;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                    </svg>
                </div>
                <h3 style="color: #fff; font-size: 1.15rem; font-weight: 700; margin-bottom: 6px;">WhatsApp Direct</h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 20px; line-height: 1.45;">
                    Live consultation with our certified PC building technicians for instant component quotes and advice.
                </p>
            </div>
            <a href="{{ $contactInfo['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer" class="btn" style="background-color: #25D366; color: #000; font-weight: 700; width: 100%; border-radius: 8px; padding: 10px 16px; font-size: 0.9rem;">
                Chat on WhatsApp ({{ $contactInfo['whatsapp_phone'] }})
            </a>
        </div>

        <!-- Phone Hotline -->
        <div class="card" style="margin-bottom: 0; padding: 28px 24px; text-align: center; border-color: rgba(59, 130, 246, 0.25); background: var(--bg-card); position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: #3b82f6;"></div>
            <div>
                <div style="width: 48px; height: 48px; margin: 0 auto 16px; border-radius: 12px; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); display: flex; align-items: center; justify-content: center; color: #60a5fa;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                </div>
                <h3 style="color: #fff; font-size: 1.15rem; font-weight: 700; margin-bottom: 6px;">Hotline Desk</h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 20px; line-height: 1.45;">
                    Direct hotline to our workshop technicians for active build queries, order questions, and urgent issues.
                </p>
            </div>
            <a href="tel:{{ str_replace(' ', '', $contactInfo['hotline']) }}" class="btn btn-outline" style="border-color: rgba(255,255,255,0.15); width: 100%; font-weight: 600; border-radius: 8px; padding: 10px 16px; font-size: 0.9rem;">
                Call: {{ $contactInfo['hotline'] }}
            </a>
        </div>

        <!-- Support Ticket -->
        <div class="card" style="margin-bottom: 0; padding: 28px 24px; text-align: center; border-color: rgba(34, 197, 94, 0.3); background: var(--bg-card); position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: space-between;">
            <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: var(--primary);"></div>
            <div>
                <div style="width: 48px; height: 48px; margin: 0 auto 16px; border-radius: 12px; background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                    </svg>
                </div>
                <h3 style="color: #fff; font-size: 1.15rem; font-weight: 700; margin-bottom: 6px;">RMA & Warranty Claim</h3>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 20px; line-height: 1.45;">
                    Need hardware replacement or workbench repairs? File an official RMA claim ticket online with rapid turnaround.
                </p>
            </div>
            <a href="{{ route('customer.support.create') }}" class="btn btn-primary" style="width: 100%; font-weight: 700; border-radius: 8px; padding: 10px 16px; font-size: 0.9rem; box-shadow: 0 4px 16px rgba(34,197,94,0.3);">
                Open RMA Ticket &rarr;
            </a>
        </div>
    </div>

    <!-- Store & Workshop Details Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
        <!-- Location & Hours -->
        <div class="card" style="padding: 28px; background: var(--bg-card); border-color: rgba(255,255,255,0.08);">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 8px;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary);"></span>
                Workshop Location & Operating Hours
            </h3>

            <div style="margin-bottom: 18px;">
                <span style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; font-family: monospace;">Storefront Address</span>
                <strong style="color: #fff; font-size: 0.95rem; line-height: 1.6; display: block; margin-top: 4px;">
                    {{ $contactInfo['address'] }}
                </strong>
            </div>

            <div style="margin-bottom: 18px;">
                <span style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; font-family: monospace;">Operating Hours</span>
                <span style="color: var(--primary); font-weight: 600; font-size: 0.95rem; display: block; margin-top: 4px; font-family: monospace;">
                    {{ $contactInfo['operating_hours'] }}
                </span>
            </div>

            <div style="border-top: 1px solid var(--border-color); padding-top: 16px;">
                <span style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; font-family: monospace;">On-Site Workbench Services</span>
                <p style="color: #cbd5e1; font-size: 0.88rem; margin-top: 6px; line-height: 1.5;">
                    {{ $contactInfo['workshop_service'] }}
                </p>
            </div>
        </div>

        <!-- Electronic Inquiries & Tracking -->
        <div class="card" style="padding: 28px; background: var(--bg-card); border-color: rgba(255,255,255,0.08); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h3 style="font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 8px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary);"></span>
                    Department Correspondence
                </h3>

                <div style="margin-bottom: 16px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; font-family: monospace;">Sales & Custom PC Build Inquiries</span>
                    <div style="margin-top: 4px;">
                        <a href="mailto:{{ $contactInfo['sales_email'] }}" style="color: var(--primary); font-weight: 600; text-decoration: none; font-size: 0.95rem; font-family: monospace;">
                            {{ $contactInfo['sales_email'] }}
                        </a>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; font-family: monospace;">Technical Diagnostics & Warranty RMA</span>
                    <div style="margin-top: 4px;">
                        <a href="mailto:{{ $contactInfo['support_email'] }}" style="color: var(--primary); font-weight: 600; text-decoration: none; font-size: 0.95rem; font-family: monospace;">
                            {{ $contactInfo['support_email'] }}
                        </a>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; font-family: monospace;">Real-time Assembly & Dispatch Tracking</span>
                    <div style="margin-top: 4px;">
                        <a href="{{ route('orders.track') }}" style="color: #60a5fa; font-weight: 600; text-decoration: none; font-size: 0.95rem;">
                            Live Order Tracking Portal &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <div style="background: rgba(0,0,0,0.3); border: 1px dashed var(--border-color); border-radius: 8px; padding: 14px; font-size: 0.82rem; color: var(--text-muted); margin-top: 12px; line-height: 1.5;">
                <strong style="color: #e2e8f0;">Walk-in Notice:</strong> For direct hardware drops or diagnosis, please bring your order number or product serial barcode for priority servicing.
            </div>
        </div>
    </div>
</div>
@endsection
