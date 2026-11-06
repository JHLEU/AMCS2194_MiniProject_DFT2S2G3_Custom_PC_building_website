@extends('layouts.app')

@section('title', 'My Support & RMA Tickets')

@section('content')
<div class="container" style="padding-top: 40px; padding-bottom: 60px;">
    <!-- Breadcrumb & Nav -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 6px;">🛠️ Support & RMA Claims</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem;">
                Track diagnostic progress, technician repair status, and RMA hardware inquiries.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('customer.warranties.index') }}" class="btn btn-secondary">🛡️ My Warranties</a>
            <a href="{{ route('customer.support.create') }}" class="btn btn-primary">+ Open Support Ticket</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 1.1rem; font-weight: 600;">Submitted Tickets</h3>
            <span class="badge badge-info">{{ $tickets->total() }} Total</span>
        </div>

        @if($tickets->isEmpty())
            <div style="text-align: center; padding: 60px 20px;">
                <div style="font-size: 3rem; margin-bottom: 12px;">💬</div>
                <h4 style="font-weight: 600; margin-bottom: 8px;">No support tickets submitted</h4>
                <p style="color: var(--text-muted); max-width: 450px; margin: 0 auto 20px;">
                    Have an issue with your custom PC build or need hardware RMA replacement? Our certified technicians are ready to assist.
                </p>
                <a href="{{ route('customer.support.create') }}" class="btn btn-primary">Open a Ticket</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Subject</th>
                            <th>Linked Hardware</th>
                            <th>Date Opened</th>
                            <th>Technician Appointment</th>
                            <th>Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                            @php
                                $schedule = $ticket->latestSchedule;
                                $product = $ticket->warranty->serialNumber->product ?? null;
                            @endphp
                            <tr>
                                <td>
                                    <span class="font-mono" style="font-weight: 600; color: #60a5fa;">#TK-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('customer.support.show', $ticket) }}" style="color: var(--text-light); font-weight: 600; text-decoration: none;">
                                        {{ $ticket->subject }}
                                    </a>
                                </td>
                                <td>
                                    @if($ticket->warranty && $product)
                                        <div style="font-size: 0.85rem;">{{ $product->name }}</div>
                                        <small class="font-mono" style="color: var(--text-muted);">SN: {{ $ticket->warranty->serialNumber->sn_code }}</small>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">General Inquiries / Custom PC</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $ticket->created_at->format('M d, Y') }}</div>
                                    <small style="color: var(--text-muted);">{{ $ticket->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    @if($schedule)
                                        <div style="color: #10b981; font-weight: 600; font-size: 0.85rem;">
                                            📅 {{ $schedule->scheduled_datetime->format('M d, Y H:i') }}
                                        </div>
                                        <small style="color: var(--text-muted);">
                                            Tech: {{ $schedule->technician->name ?? 'Assigned' }}
                                        </small>
                                    @else
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">Under Review</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $ticket->status_badge_class }}">
                                        {{ $ticket->status }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('customer.support.show', $ticket) }}" class="btn btn-secondary btn-sm">
                                        View Case
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
