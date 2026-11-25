@extends('layouts.erp')

@section('title', 'Ticket Workbench #TK-' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT))

@section('content')
<div style="max-width: 1200px; margin: 0 auto;">
    <!-- Top Bar -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <a href="{{ route('erp.support.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 10px;">
                ← Back to Ticket Queue
            </a>
            <div style="display: flex; align-items: center; gap: 12px; margin-top: 6px;">
                <span class="font-mono" style="font-size: 1.2rem; color: #60a5fa; font-weight: 700;">
                    #TK-{{ str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                </span>
                <span class="badge {{ $ticket->status_badge_class }}" style="font-size: 0.95rem; padding: 6px 14px;">
                    {{ $ticket->status }}
                </span>
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 700; margin-top: 6px; margin-bottom: 4px;">
                {{ $ticket->subject }}
            </h1>
            <p style="color: var(--text-muted); font-size: 0.875rem;">
                Submitted {{ $ticket->created_at->format('M d, Y \a\t H:i') }} ({{ $ticket->created_at->diffForHumans() }})
            </p>
        </div>

        <!-- Quick Status Change Bar -->
        <div class="card" style="padding: 14px 18px;">
            <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 8px;">
                Update Status
            </div>
            <form action="{{ route('erp.support.status', $ticket) }}" method="POST" style="display: flex; gap: 8px; flex-wrap: wrap;">
                @csrf
                <button type="submit" name="status" value="Open" class="btn btn-sm {{ $ticket->status === 'Open' ? 'btn-primary' : 'btn-secondary' }}">
                    Open
                </button>
                <button type="submit" name="status" value="Diagnosing" class="btn btn-sm {{ $ticket->status === 'Diagnosing' ? 'btn-primary' : 'btn-secondary' }}">
                    Diagnosing
                </button>
                <button type="submit" name="status" value="WaitingForParts" class="btn btn-sm {{ $ticket->status === 'WaitingForParts' ? 'btn-primary' : 'btn-secondary' }}">
                    Waiting Parts
                </button>
                <button type="submit" name="status" value="Resolved" class="btn btn-sm {{ $ticket->status === 'Resolved' ? 'btn-primary' : 'btn-secondary' }}">
                    Resolved
                </button>
                <button type="submit" name="status" value="Closed" class="btn btn-sm {{ $ticket->status === 'Closed' ? 'btn-primary' : 'btn-secondary' }}">
                    Closed
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- Left Column: Problem & Workshop Dispatch -->
        <div>
            <!-- Problem Description -->
            <div class="card" style="padding: 24px; margin-bottom: 24px;">
                <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 14px;">Customer Issue Report</h3>
                <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; line-height: 1.6; white-space: pre-wrap; font-size: 0.95rem;">{{ $ticket->issue_description }}</div>
            </div>

            <!-- Technician Scheduling & Workshop Dispatch Form -->
            <div class="card" style="padding: 24px; margin-bottom: 24px;">
                <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 6px;">Schedule Workshop Appointment & Assign Tech</h3>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 18px;">
                    Book a physical workbench slot and assign a certified hardware technician for diagnostic testing or component replacement.
                </p>

                <form action="{{ route('erp.support.assign', $ticket) }}" method="POST">
                    @csrf
                    <div class="form-grid" style="margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">Assign Technician <span style="color: var(--danger);">*</span></label>
                            <select name="technician_id" class="form-control" required>
                                <option value="">-- Select Technician --</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}">
                                        {{ $tech->name ?? $tech->username }} ({{ $tech->role->name ?? 'Staff' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Appointment Date & Time <span style="color: var(--danger);">*</span></label>
                            <input type="datetime-local" name="scheduled_datetime" class="form-control" value="{{ now()->addDay()->format('Y-m-d\TH:00') }}" required>
                        </div>
                    </div>

                    <div class="form-grid" style="margin-bottom: 16px;">
                        <div class="form-group">
                            <label class="form-label">Estimated Duration (Hours) <span style="color: var(--danger);">*</span></label>
                            <input type="number" name="estimated_duration_hours" class="form-control" value="1" min="1" max="12" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Diagnostic / Workbench Notes (Optional)</label>
                            <input type="text" name="notes" class="form-control" placeholder="e.g. Test GPU with FurMark stress bench; swap PCIe riser cable">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Assign & Schedule Dispatch →
                    </button>
                </form>
            </div>

            <!-- Schedule History -->
            @if($ticket->schedules->isNotEmpty())
                <div class="card" style="padding: 24px;">
                    <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 14px;">Appointment History</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Technician</th>
                                    <th>Scheduled Date & Time</th>
                                    <th>Est. Duration</th>
                                    <th>Workbench Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ticket->schedules as $sched)
                                    <tr>
                                        <td>
                                            <span style="font-weight: 600; color: #10b981;">‍{{ $sched->technician->name ?? 'Staff' }}</span>
                                        </td>
                                        <td>
                                            <div>{{ $sched->scheduled_datetime->format('M d, Y') }}</div>
                                            <small style="color: var(--text-muted);">{{ $sched->scheduled_datetime->format('H:i A') }}</small>
                                        </td>
                                        <td>{{ $sched->estimated_duration_hours }} hr(s)</td>
                                        <td>
                                            <small>{{ $sched->notes ?? 'No special notes recorded' }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Customer Info & Hardware/Warranty Details -->
        <div>
            <!-- Customer Contact Card -->
            <div class="card" style="padding: 20px; margin-bottom: 24px;">
                <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 14px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                    Customer Details
                </h4>
                <div style="margin-bottom: 12px;">
                    <small style="color: var(--text-muted);">Name</small>
                    <div style="font-weight: 600;">{{ $ticket->customer->name ?? 'Guest User' }}</div>
                </div>
                <div style="margin-bottom: 12px;">
                    <small style="color: var(--text-muted);">Email Address</small>
                    <div>
                        <a href="mailto:{{ $ticket->customer->email ?? '' }}" style="color: #60a5fa; text-decoration: none;">
                            {{ $ticket->customer->email ?? 'N/A' }}
                        </a>
                    </div>
                </div>
                @if($ticket->customer->phone ?? null)
                    <div style="margin-bottom: 12px;">
                        <small style="color: var(--text-muted);">Phone Number</small>
                        <div style="color: var(--text-light); font-weight: 600;">{{ $ticket->customer->phone }}</div>
                    </div>
                @endif
            </div>

            <!-- Hardware Coverage & Serial Details -->
            <div class="card" style="padding: 20px;">
                <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 14px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                    Hardware & Warranty
                </h4>

                @if($ticket->warranty)
                    @php
                        $warranty = $ticket->warranty;
                        $prod = $warranty->serialNumber->product ?? null;
                    @endphp
                    <div style="margin-bottom: 12px;">
                        <small style="color: var(--text-muted);">Product Item</small>
                        <div style="font-weight: 600;">{{ $prod->name ?? 'Hardware Component' }}</div>
                        <small style="color: var(--text-muted);">Category: {{ $prod->category->name ?? 'Component' }}</small>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <small style="color: var(--text-muted);">Serial Number</small>
                        <div class="font-mono" style="font-size: 0.9rem; color: #60a5fa;">
                            {{ $warranty->serialNumber->sn_code ?? 'N/A' }}
                        </div>
                        <small style="color: var(--text-muted);">
                            Physical Unit State: <strong style="color: #f59e0b;">{{ $warranty->serialNumber->status }}</strong>
                        </small>
                    </div>
                    <div style="margin-bottom: 12px;">
                        <small style="color: var(--text-muted);">Coverage Validity</small>
                        <div>
                            @if($warranty->status === 'Active' && !$warranty->isExpired())
                                <span class="badge badge-success">Active Warranty</span>
                                <div style="color: #10b981; font-weight: 600; font-size: 0.85rem; margin-top: 4px;">
                                    {{ $warranty->daysRemaining() }} days left
                                </div>
                            @else
                                <span class="badge badge-secondary">Expired</span>
                            @endif
                        </div>
                        <small style="color: var(--text-muted); display: block; margin-top: 4px;">
                            {{ $warranty->start_date ? $warranty->start_date->format('M d, Y') : '-' }} to {{ $warranty->end_date ? $warranty->end_date->format('M d, Y') : '-' }}
                        </small>
                    </div>
                    @if($warranty->order)
                        <div style="border-top: 1px solid var(--border-color); padding-top: 12px; margin-top: 12px;">
                            <small style="color: var(--text-muted);">Purchased In Order</small>
                            <div>
                                <a href="{{ route('erp.orders.show', $warranty->order) }}" style="color: #93c5fd; text-decoration: none; font-weight: 600;" class="font-mono">
                                    {{ $warranty->order->order_number }}
                                </a>
                            </div>
                        </div>
                    @endif
                @else
                    <p style="color: var(--text-muted); font-size: 0.875rem;">
                        No specific hardware serial number attached. Case logged under general custom PC consultation.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
