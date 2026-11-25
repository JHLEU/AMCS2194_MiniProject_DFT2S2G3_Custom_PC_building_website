@extends('layouts.app')

@section('title', 'Ticket #TK-' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT) . ' — GOPC')

@section('content')
<div class="container" style="padding-top: 36px; padding-bottom: 60px; max-width: 960px;">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 28px;">
        <a href="{{ route('customer.support.index') }}" class="btn btn-outline btn-sm" style="border-color: rgba(255,255,255,0.15); margin-bottom: 16px;">
            &larr; Back to Support Tickets
        </a>
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 4px; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--primary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; font-family: monospace;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--primary);"></span>
                    Case #TK-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                </div>
                <h1 style="font-size: 1.8rem; font-weight: 700; color: #fff; margin: 4px 0; letter-spacing: -0.5px;">
                    {{ $ticket->subject }}
                </h1>
                <p style="color: var(--text-muted); font-size: 0.85rem; font-family: monospace; margin: 0;">
                    Opened on {{ $ticket->created_at->format('Y-m-d H:i') }}
                </p>
            </div>
            <div>
                <span class="badge {{ $ticket->status_badge_class }}" style="font-size: 0.85rem; padding: 6px 14px; font-family: monospace;">
                    ● {{ $ticket->status }}
                </span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 24px; border-radius: 8px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">
        <!-- Left: Case Details & Issue Description -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div class="card" style="padding: 24px; margin-bottom: 0; background: var(--bg-card); border-color: rgba(255,255,255,0.08);">
                <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                    Reported Symptoms & Issue Description
                </h3>
                <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--border-color); border-radius: 8px; padding: 18px; line-height: 1.6; white-space: pre-wrap; font-size: 0.9rem; color: #e2e8f0;">{{ $ticket->issue_description }}</div>
            </div>

            <!-- Technician Appointment / Dispatch Status -->
            <div class="card" style="padding: 24px; margin-bottom: 0; background: var(--bg-card); border-color: rgba(255,255,255,0.08);">
                <h3 style="font-size: 1.05rem; font-weight: 700; color: #fff; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                    Workshop Diagnosis & Technician Dispatch
                </h3>
                
                @if($ticket->schedules->isEmpty())
                    <div style="background: rgba(255,255,255,0.02); border: 1px dashed var(--border-color); border-radius: 8px; padding: 32px 20px; text-align: center;">
                        <div style="width: 44px; height: 44px; margin: 0 auto 12px; border-radius: 10px; background: rgba(255,255,255,0.04); display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <h4 style="font-weight: 700; font-size: 0.95rem; color: #fff; margin-bottom: 6px;">Triage in Progress</h4>
                        <p style="color: var(--text-muted); font-size: 0.85rem; max-width: 420px; margin: 0 auto; line-height: 1.5;">
                            A support technician is evaluating your case. Once assigned to a bench slot, your scheduled appointment date and workshop notes will appear here.
                        </p>
                    </div>
                @else
                    @foreach($ticket->schedules as $schedule)
                        <div style="background: rgba(34, 197, 94, 0.05); border: 1px solid rgba(34, 197, 94, 0.2); border-radius: 8px; padding: 18px; margin-bottom: 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <div style="font-weight: 700; color: var(--primary); font-family: monospace; font-size: 0.9rem;">
                                    Scheduled: {{ $schedule->scheduled_datetime->format('Y-m-d H:i') }}
                                </div>
                                <span class="badge badge-secondary" style="font-size: 0.75rem;">{{ $schedule->estimated_duration_hours }} hr inspection</span>
                            </div>
                            <div style="font-size: 0.88rem; color: var(--text-light); margin-bottom: 8px;">
                                <strong>Assigned Technician:</strong> {{ $schedule->technician->name ?? 'Workshop Engineer' }}
                            </div>
                            @if($schedule->notes)
                                <div style="background: rgba(0,0,0,0.3); border-radius: 6px; padding: 12px; font-size: 0.85rem; color: #cbd5e1; line-height: 1.5; border: 1px solid rgba(255,255,255,0.06);">
                                    <strong style="color: #fff;">Workbench Notes:</strong><br>
                                    {{ $schedule->notes }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Right Sidebar: Hardware Info & Warranty Status -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card" style="padding: 22px; margin-bottom: 0; background: var(--bg-card); border-color: rgba(255,255,255,0.08);">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: #fff; margin-bottom: 14px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                    Hardware Coverage
                </h4>

                @if($ticket->warranty)
                    @php
                        $warranty = $ticket->warranty;
                        $prod = $warranty->serialNumber->product ?? null;
                    @endphp
                    <div style="margin-bottom: 14px;">
                        <small style="color: var(--text-muted); font-size: 0.78rem; text-transform: uppercase; font-family: monospace;">Component</small>
                        <div style="font-weight: 700; color: #fff; font-size: 0.92rem; margin-top: 2px;">{{ $prod->name ?? 'Hardware' }}</div>
                    </div>
                    <div style="margin-bottom: 14px;">
                        <small style="color: var(--text-muted); font-size: 0.78rem; text-transform: uppercase; font-family: monospace;">Serial Number</small>
                        <div style="font-family: monospace; font-size: 0.85rem; color: var(--primary); margin-top: 2px;">
                            {{ $warranty->serialNumber->sn_code ?? 'N/A' }}
                        </div>
                    </div>
                    <div style="margin-bottom: 14px;">
                        <small style="color: var(--text-muted); font-size: 0.78rem; text-transform: uppercase; font-family: monospace;">Warranty Status</small>
                        <div style="margin-top: 4px;">
                            @if($warranty->status === 'Active' && !$warranty->isExpired())
                                <span class="badge badge-success" style="font-size: 0.75rem; font-family: monospace;">● ACTIVE</span>
                                <small style="display: block; color: var(--text-muted); margin-top: 4px; font-family: monospace; font-size: 0.75rem;">
                                    {{ $warranty->daysRemaining() }} days left (until {{ $warranty->end_date->format('Y-m-d') }})
                                </small>
                            @else
                                <span class="badge badge-secondary" style="font-size: 0.75rem; font-family: monospace;">EXPIRED</span>
                            @endif
                        </div>
                    </div>
                    @if($warranty->order)
                        <div style="border-top: 1px solid var(--border-color); padding-top: 12px; margin-top: 12px;">
                            <small style="color: var(--text-muted); font-size: 0.78rem; text-transform: uppercase; font-family: monospace;">Origin Order</small>
                            <div style="font-family: monospace; margin-top: 2px;">
                                <a href="{{ route('orders.track', ['query' => $warranty->order->order_number]) }}" style="color: var(--primary); text-decoration: none; font-size: 0.88rem; font-weight: 600;">
                                    {{ $warranty->order->order_number }} &rarr;
                                </a>
                            </div>
                        </div>
                    @endif
                @else
                    <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.5; margin: 0;">
                        This ticket is logged as a general technical consultation or full-system custom PC inquiry.
                    </p>
                @endif
            </div>

            <!-- Customer assistance info -->
            <div class="card" style="padding: 20px; font-size: 0.85rem; color: var(--text-muted); background: var(--bg-card); border-color: rgba(255,255,255,0.08); margin-bottom: 0;">
                <h4 style="font-size: 0.9rem; font-weight: 700; color: #fff; margin-bottom: 8px;">Need urgent walk-in help?</h4>
                <p style="margin-bottom: 12px; line-height: 1.5;">
                    Our hardware service desk is open Monday through Saturday from 10:00 AM to 7:00 PM.
                </p>
                <div style="color: var(--primary); font-weight: 600; font-family: monospace; font-size: 0.88rem;">
                    Support Hotline: +60 3-8888 1234
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
