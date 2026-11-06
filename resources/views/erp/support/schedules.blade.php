@extends('layouts.erp')

@section('title', 'Technician Dispatch & Workshop Schedules')

@section('content')
<div>
    <!-- Top Action / Nav -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 1.6rem; font-weight: 700; margin-bottom: 4px;">📅 Technician Dispatch & Workshop Schedules</h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">
                Upcoming physical hardware inspection slots and repair timeline across all workshop benches.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('erp.support.index') }}" class="btn btn-secondary btn-sm">🛠️ Ticket Queue</a>
            <a href="{{ route('erp.support.warranties') }}" class="btn btn-secondary btn-sm">🛡️ Warranty Registry</a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form action="{{ route('erp.support.schedules') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; width: 100%;">
            <div style="display: flex; gap: 8px; align-items: center;">
                <label style="font-size: 0.85rem; color: var(--text-muted);">Technician:</label>
                <select name="technician_id" class="form-control form-control-sm" style="width: 200px;">
                    <option value="">-- All Technicians --</option>
                    @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}" {{ $techId == $tech->id ? 'selected' : '' }}>
                            {{ $tech->name ?? $tech->username }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 8px; align-items: center;">
                <label style="font-size: 0.85rem; color: var(--text-muted);">Date:</label>
                <input type="date" name="date" class="form-control form-control-sm" value="{{ $date ?? '' }}" style="width: 160px;">
            </div>

            <button type="submit" class="btn btn-primary btn-sm">Apply Filters</button>
            @if($techId || $date)
                <a href="{{ route('erp.support.schedules') }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
        </form>
    </div>

    <!-- Schedules Table -->
    <div class="card">
        <div class="card-header">
            <h3 style="font-size: 1.1rem; font-weight: 600;">Workshop Schedule Timeline</h3>
            <span class="badge badge-info">{{ $schedules->total() }} Bookings</span>
        </div>

        @if($schedules->isEmpty())
            <div style="text-align: center; padding: 50px 20px; color: var(--text-muted);">
                <div style="font-size: 2.5rem; margin-bottom: 8px;">🗓️</div>
                <h4 style="font-weight: 600; color: var(--text-light);">No scheduled appointments found</h4>
                <p style="font-size: 0.875rem;">Assign tickets to technicians from the ticket workbench to populate the dispatch calendar.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Scheduled Date & Time</th>
                            <th>Technician</th>
                            <th>Ticket Case</th>
                            <th>Customer</th>
                            <th>Hardware Component</th>
                            <th>Ticket Status</th>
                            <th>Diagnostic / Repair Notes</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $sched)
                            @php
                                $ticket = $sched->ticket;
                                $prod = $ticket->warranty->serialNumber->product ?? null;
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: #10b981;">
                                        📅 {{ $sched->scheduled_datetime->format('M d, Y') }}
                                    </div>
                                    <small style="color: var(--text-muted);">
                                        {{ $sched->scheduled_datetime->format('h:i A') }} ({{ $sched->estimated_duration_hours }} hr slot)
                                    </small>
                                </td>
                                <td>
                                    <span style="font-weight: 600; color: var(--text-light);">
                                        👨‍🔧 {{ $sched->technician->name ?? 'Staff' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('erp.support.show', $ticket) }}" class="font-mono" style="font-weight: 600; color: #60a5fa; text-decoration: none;">
                                        #TK-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                                    </a>
                                    <div style="font-size: 0.85rem; max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $ticket->subject }}
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $ticket->customer->name ?? 'User' }}</div>
                                    <small style="color: var(--text-muted);">{{ $ticket->customer->email ?? '' }}</small>
                                </td>
                                <td>
                                    @if($prod)
                                        <div style="font-size: 0.85rem;">{{ $prod->name }}</div>
                                        <small class="font-mono" style="color: var(--text-muted);">SN: {{ $ticket->warranty->serialNumber->serial_number }}</small>
                                    @else
                                        <small style="color: var(--text-muted);">General PC Inspection</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $ticket->status_badge_class }}">
                                        {{ $ticket->status }}
                                    </span>
                                </td>
                                <td>
                                    <small style="max-width: 250px; display: block;">
                                        {{ $sched->notes ?? 'Standard bench inspection' }}
                                    </small>
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

            @if($schedules->hasPages())
                <div style="padding: 16px 24px; border-top: 1px solid var(--border-color);">
                    {{ $schedules->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
