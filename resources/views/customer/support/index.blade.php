@extends('layouts.app')

@section('title', 'My Support & RMA Tickets — GOPC')

@section('content')
<div class="container" style="padding-top: 36px; padding-bottom: 60px;">
    <!-- Breadcrumb & Nav -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--primary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--primary);"></span>
                Technical Service & Workshop
            </div>
            <h1 style="font-size: 2rem; font-weight: 700; color: #fff; margin: 0; letter-spacing: -0.5px;">Support & RMA Claims</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin: 6px 0 0;">
                Track hardware diagnostics, RMA replacements, and technician workshop appointments.
            </p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('customer.warranties.index') }}" class="btn btn-outline btn-sm" style="border-color: rgba(255,255,255,0.15); padding: 8px 16px;">Registered Warranties</a>
            <a href="{{ route('customer.support.create') }}" class="btn btn-primary btn-sm" style="padding: 8px 18px; font-weight: 600;">+ Open Support Ticket</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 24px; border-radius: 8px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card" style="padding: 0; overflow: hidden; border-color: rgba(255,255,255,0.08);">
        <div class="card-header" style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0; color: #fff;">Submitted Support Tickets</h3>
            <span class="badge badge-secondary" style="font-family: monospace; font-size: 0.75rem;">{{ $tickets->total() }} Records</span>
        </div>

        @if($tickets->isEmpty())
            <div style="text-align: center; padding: 72px 24px;">
                <div style="width: 56px; height: 56px; margin: 0 auto 16px; border-radius: 12px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                </div>
                <h4 style="font-weight: 700; color: #fff; margin-bottom: 8px; font-size: 1.1rem;">No Support Tickets Submitted</h4>
                <p style="color: var(--text-muted); max-width: 440px; margin: 0 auto 24px; font-size: 0.9rem; line-height: 1.5;">
                    Experiencing hardware faults, booting issues, or need replacement parts under warranty? Our certified workshop technicians are ready to assist.
                </p>
                <a href="{{ route('customer.support.create') }}" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">Open a New Ticket</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="padding-left: 24px;">Ticket ID</th>
                            <th>Inquiry Subject</th>
                            <th>Hardware Context</th>
                            <th>Date Opened</th>
                            <th>Technician Appointment</th>
                            <th>Status</th>
                            <th style="text-align: right; padding-right: 24px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            @php
                                $schedule = $ticket->latestSchedule;
                                $product = $ticket->warranty->serialNumber->product ?? null;
                            @endphp
                            <tr>
                                <td style="padding-left: 24px;">
                                    <span style="font-family: monospace; font-weight: 600; color: var(--primary); font-size: 0.9rem;">#TK-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('customer.support.show', $ticket) }}" style="color: #fff; font-weight: 600; text-decoration: none;">
                                        {{ $ticket->subject }}
                                    </a>
                                </td>
                                <td>
                                    @if($ticket->warranty && $product)
                                        <div style="font-size: 0.88rem; color: #e2e8f0;">{{ $product->name }}</div>
                                        <small style="font-family: monospace; color: var(--text-muted); font-size: 0.75rem;">SN: {{ $ticket->warranty->serialNumber->sn_code }}</small>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">General Inquiries / Custom PC</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-family: monospace; font-size: 0.85rem; color: #fff;">{{ $ticket->created_at->format('Y-m-d') }}</div>
                                    <small style="color: var(--text-muted); font-family: monospace; font-size: 0.75rem;">{{ $ticket->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    @if($schedule)
                                        <div style="color: var(--primary); font-weight: 600; font-size: 0.85rem; font-family: monospace;">
                                            {{ $schedule->scheduled_datetime->format('Y-m-d H:i') }}
                                        </div>
                                        <small style="color: var(--text-muted); font-size: 0.75rem;">
                                            Tech: {{ $schedule->technician->name ?? 'Assigned' }}
                                        </small>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">Under Review</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $ticket->status_badge_class }}" style="font-size: 0.75rem; font-family: monospace;">
                                        ● {{ $ticket->status }}
                                    </span>
                                </td>
                                <td style="text-align: right; padding-right: 24px;">
                                    <a href="{{ route('customer.support.show', $ticket) }}" class="btn btn-outline btn-sm" style="border-color: rgba(34,197,94,0.3); color: var(--primary); padding: 5px 14px;">
                                        View Case &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
                <div style="padding: 16px 24px; border-top: 1px solid var(--border-color);">
                    {{ $tickets->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
