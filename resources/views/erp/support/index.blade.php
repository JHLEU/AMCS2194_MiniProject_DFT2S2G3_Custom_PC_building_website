@extends('layouts.erp')

@section('title', 'Tech Support & RMA Workbench')

@section('content')
<div>
    <!-- Top KPI Counters -->
    <div class="metrics-grid" style="margin-bottom: 24px;">
        <div class="metric-card">
            <div class="metric-label">Total Tickets</div>
            <div class="metric-value">{{ $counters['total'] }}</div>
            <div class="metric-subtitle">All-time customer cases</div>
        </div>
        <div class="metric-card" style="border-top: 3px solid #f59e0b;">
            <div class="metric-label">Open / Unassigned</div>
            <div class="metric-value" style="color: #f59e0b;">{{ $counters['open'] }}</div>
            <div class="metric-subtitle">Needs triage & review</div>
        </div>
        <div class="metric-card" style="border-top: 3px solid #3b82f6;">
            <div class="metric-label">Diagnosing</div>
            <div class="metric-value" style="color: #60a5fa;">{{ $counters['diagnosing'] }}</div>
            <div class="metric-subtitle">Under technician testing</div>
        </div>
        <div class="metric-card" style="border-top: 3px solid #ef4444;">
            <div class="metric-label">Waiting For Parts (RMA)</div>
            <div class="metric-value" style="color: #ef4444;">{{ $counters['waiting_for_parts'] }}</div>
            <div class="metric-subtitle">Supplier replacement pending</div>
        </div>
        <div class="metric-card" style="border-top: 3px solid #10b981;">
            <div class="metric-label">Resolved</div>
            <div class="metric-value" style="color: #10b981;">{{ $counters['resolved'] }}</div>
            <div class="metric-subtitle">Repairs completed</div>
        </div>
    </div>

    <!-- Filter Bar & Search -->
    <div class="filter-bar">
        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <a href="{{ route('erp.support.index') }}" class="btn btn-sm {{ !$statusFilter ? 'btn-primary' : 'btn-secondary' }}">
                All ({{ $counters['total'] }})
            </a>
            <a href="{{ route('erp.support.index', ['status' => 'Open']) }}" class="btn btn-sm {{ $statusFilter === 'Open' ? 'btn-primary' : 'btn-secondary' }}">
                Open ({{ $counters['open'] }})
            </a>
            <a href="{{ route('erp.support.index', ['status' => 'Diagnosing']) }}" class="btn btn-sm {{ $statusFilter === 'Diagnosing' ? 'btn-primary' : 'btn-secondary' }}">
                Diagnosing ({{ $counters['diagnosing'] }})
            </a>
            <a href="{{ route('erp.support.index', ['status' => 'WaitingForParts']) }}" class="btn btn-sm {{ $statusFilter === 'WaitingForParts' ? 'btn-primary' : 'btn-secondary' }}">
                Waiting Parts ({{ $counters['waiting_for_parts'] }})
            </a>
            <a href="{{ route('erp.support.index', ['status' => 'Resolved']) }}" class="btn btn-sm {{ $statusFilter === 'Resolved' ? 'btn-primary' : 'btn-secondary' }}">
                Resolved ({{ $counters['resolved'] }})
            </a>
            <a href="{{ route('erp.support.index', ['status' => 'Closed']) }}" class="btn btn-sm {{ $statusFilter === 'Closed' ? 'btn-primary' : 'btn-secondary' }}">
                Closed ({{ $counters['closed'] }})
            </a>
        </div>

        <div style="display: flex; gap: 12px; align-items: center;">
            <form action="{{ route('erp.support.index') }}" method="GET" style="display: flex; gap: 8px;">
                @if($statusFilter)
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                @endif
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ticket, customer, SN..." value="{{ $search ?? '' }}" style="width: 220px;">
                <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            </form>
            <a href="{{ route('erp.support.schedules') }}" class="btn btn-outline btn-sm">📅 Tech Schedules</a>
            <a href="{{ route('erp.support.warranties') }}" class="btn btn-outline btn-sm">🛡️ Warranties</a>
        </div>
    </div>

    <!-- Ticket Queue Table -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 1.1rem; font-weight: 600;">Support & RMA Queue</h3>
            <span class="badge badge-info">{{ $tickets->total() }} Matching Tickets</span>
        </div>

        @if($tickets->isEmpty())
            <div style="text-align: center; padding: 50px 20px; color: var(--text-muted);">
                <div style="font-size: 2.5rem; margin-bottom: 8px;">🎉</div>
                <h4 style="font-weight: 600; color: var(--text-light);">No tickets found in this queue</h4>
                <p style="font-size: 0.875rem;">All technical support tickets are currently triaged or no tickets match the filters.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Customer</th>
                            <th>Subject / Hardware</th>
                            <th>Status</th>
                            <th>Assigned Tech</th>
                            <th>Workshop Slot</th>
                            <th>Opened</th>
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
                                    <div style="font-weight: 600;">{{ $ticket->customer->name ?? 'Guest/User' }}</div>
                                    <small style="color: var(--text-muted);">{{ $ticket->customer->email ?? '' }}</small>
                                </td>
                                <td>
                                    <div style="font-weight: 600; max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $ticket->subject }}
                                    </div>
                                    @if($ticket->warranty && $product)
                                        <small style="color: #93c5fd;">
                                            {{ $product->name }} (SN: {{ $ticket->warranty->serialNumber->sn_code }})
                                        </small>
                                    @else
                                        <small style="color: var(--text-muted);">General Consultation</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $ticket->status_badge_class }}">
                                        {{ $ticket->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($schedule && $schedule->technician)
                                        <span style="color: #10b981; font-weight: 600;">👨‍🔧 {{ $schedule->technician->name }}</span>
                                    @else
                                        <span style="color: var(--text-muted);">Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    @if($schedule)
                                        <div style="font-size: 0.85rem;">{{ $schedule->scheduled_datetime->format('M d, H:i') }}</div>
                                        <small style="color: var(--text-muted);">{{ $schedule->estimated_duration_hours }} hr(s)</small>
                                    @else
                                        <span style="color: var(--text-muted);">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small style="color: var(--text-muted);">{{ $ticket->created_at->diffForHumans() }}</small>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('erp.support.show', $ticket) }}" class="btn btn-secondary btn-sm">
                                        Workbench →
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
