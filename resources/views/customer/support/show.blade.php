@extends('layouts.app')

@section('title', 'Ticket #' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT))

@section('content')
<div class="container" style="padding-top: 40px; padding-bottom: 60px; max-width: 900px;">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 24px;">
        <a href="{{ route('customer.support.index') }}" style="color: var(--text-muted); text-decoration: none; font-size: 0.9rem;">
            ← Back to Support Tickets
        </a>
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 10px; flex-wrap: wrap; gap: 12px;">
            <div>
                <span class="font-mono" style="font-size: 1rem; color: #60a5fa; font-weight: 600;">
                    Case #TK-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                </span>
                <h1 style="font-size: 1.6rem; font-weight: 700; margin-top: 4px; margin-bottom: 4px;">
                    {{ $ticket->subject }}
                </h1>
                <p style="color: var(--text-muted); font-size: 0.9rem;">
                    Opened on {{ $ticket->created_at->format('M d, Y \a\t H:i') }}
                </p>
            </div>
            <div>
                <span class="badge {{ $ticket->status_badge_class }}" style="font-size: 0.95rem; padding: 6px 14px;">
                    {{ $ticket->status }}
                </span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- Left: Case Details & Issue Description -->
        <div>
            <div class="card" style="padding: 24px; margin-bottom: 24px;">
                <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 14px;">Problem Description</h3>
                <div style="background: rgba(0,0,0,0.25); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; line-height: 1.6; white-space: pre-wrap; font-size: 0.95rem;">{{ $ticket->issue_description }}</div>
            </div>

            <!-- Technician Appointment / Dispatch Status -->
            <div class="card" style="padding: 24px;">
                <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 14px;">🛠️ Workshop Diagnosis & Dispatch</h3>
                
                @if($ticket->schedules->isEmpty())
                    <div style="background: rgba(255,255,255,0.03); border: 1px dashed var(--border-color); border-radius: var(--radius-md); padding: 24px; text-align: center;">
                        <div style="font-size: 1.8rem; margin-bottom: 8px;">⏳</div>
                        <h4 style="font-weight: 600; font-size: 1rem; margin-bottom: 6px;">Triage in Progress</h4>
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">
                            A support technician is evaluating your case. Once assigned to a bench slot, your scheduled appointment date and workshop notes will appear here.
                        </p>
                    </div>
                @else
                    @foreach($ticket->schedules as $schedule)
                        <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: var(--radius-md); padding: 18px; margin-bottom: 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <div style="font-weight: 600; color: #10b981;">
                                    📅 Scheduled: {{ $schedule->scheduled_datetime->format('l, M d, Y \a\t h:i A') }}
                                </div>
                                <span class="badge badge-info">{{ $schedule->estimated_duration_hours }} hr inspection slot</span>
                            </div>
                            <div style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 8px;">
                                <strong>Assigned Technician:</strong> {{ $schedule->technician->name ?? 'Workshop Engineer' }}
                            </div>
                            @if($schedule->notes)
                                <div style="background: rgba(0,0,0,0.25); border-radius: 6px; padding: 12px; font-size: 0.875rem; color: #e2e8f0; line-height: 1.5;">
                                    <strong>Workbench Notes:</strong><br>
                                    {{ $schedule->notes }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Right Sidebar: Hardware Info & Warranty Status -->
        <div>
            <div class="card" style="padding: 20px; margin-bottom: 24px;">
                <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 12px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                    Hardware Coverage
                </h4>

                @if($ticket->warranty)
                    @php
                        $warranty = $ticket->warranty;
                        $prod = $warranty->serialNumber->product ?? null;
                    @endphp
                    <div style="margin-bottom: 12px;">
                        <small style="color: var(--text-muted);">Component</small>
                        <div style="font-weight: 600;">{{ $prod->name ?? 'Hardware' }}</div>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <small style="color: var(--text-muted);">Serial Number</small>
                        <div class="font-mono" style="font-size: 0.9rem; color: #60a5fa;">
                            {{ $warranty->serialNumber->sn_code ?? 'N/A' }}
                        </div>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <small style="color: var(--text-muted);">Warranty Status</small>
                        <div>
                            @if($warranty->status === 'Active' && !$warranty->isExpired())
                                <span class="badge badge-success">Active Protection</span>
                                <small style="display: block; color: var(--text-muted); margin-top: 4px;">
                                    {{ $warranty->daysRemaining() }} days remaining (until {{ $warranty->end_date->format('M d, Y') }})
                                </small>
                            @else
                                <span class="badge badge-secondary">Expired</span>
                            @endif
                        </div>
                    </div>
                    @if($warranty->order)
                        <div style="border-top: 1px solid var(--border-color); padding-top: 12px; margin-top: 12px;">
                            <small style="color: var(--text-muted);">Original Order</small>
                            <div class="font-mono">
                                <a href="{{ route('orders.track', ['order_number' => $warranty->order->order_number, 'phone' => $warranty->order->customer_phone]) }}" style="color: #93c5fd; text-decoration: none;">
                                    {{ $warranty->order->order_number }}
                                </a>
                            </div>
                        </div>
                    @endif
                @else
                    <p style="color: var(--text-muted); font-size: 0.875rem;">
                        This ticket is logged as a general technical consultation or full-system custom PC inquiry.
                    </p>
                @endif
            </div>

            <!-- Customer assistance info -->
            <div class="card" style="padding: 20px; font-size: 0.875rem; color: var(--text-muted);">
                <h4 style="font-size: 0.95rem; font-weight: 600; color: var(--text-light); margin-bottom: 8px;">Need urgent walk-in help?</h4>
                <p style="margin-bottom: 8px;">
                    Our hardware service desk is open Monday through Saturday from 10:00 AM to 7:00 PM.
                </p>
                <div style="color: #60a5fa; font-weight: 600;">
                    📞 Support Hotline: +60 3-8888 1234
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
