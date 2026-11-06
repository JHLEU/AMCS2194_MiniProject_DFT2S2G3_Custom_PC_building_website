<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Warranty;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function __construct(
        protected SupportTicketService $ticketService
    ) {}

    public function index(Request $request): View
    {
        $counters = $this->ticketService->getDashboardCounters();
        $statusFilter = $request->query('status');
        $search = $request->query('search');

        $query = SupportTicket::with([
            'customer',
            'warranty.serialNumber.product',
            'latestSchedule.technician',
        ])->latest();

        if ($statusFilter && in_array($statusFilter, SupportTicket::STATUSES, true)) {
            $query->where('status', $statusFilter);
        }

        if ($search) {
            $term = '%' . trim($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('subject', 'like', $term)
                  ->orWhere('issue_description', 'like', $term)
                  ->orWhereHas('customer', function ($cq) use ($term) {
                      $cq->where('name', 'like', $term)
                         ->orWhere('email', 'like', $term);
                  })
                  ->orWhereHas('warranty.serialNumber', function ($sq) use ($term) {
                      $sq->where('sn_code', 'like', $term);
                  });
            });
        }

        $tickets = $query->paginate(20)->withQueryString();

        return view('erp.support.index', compact('tickets', 'counters', 'statusFilter', 'search'));
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load([
            'customer',
            'warranty.serialNumber.product',
            'warranty.order',
            'schedules.technician',
        ]);

        // Technicians available for dispatch assignment
        $technicians = User::whereHas('role', function ($query) {
            $query->whereIn('name', ['Staff_Tech', 'Admin']);
        })->get();

        return view('erp.support.show', compact('ticket', 'technicians'));
    }

    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', SupportTicket::STATUSES),
        ]);

        $this->ticketService->updateStatus($ticket, $validated['status']);

        return back()->with('success', "Ticket status updated to [{$validated['status']}].");
    }

    public function assignSchedule(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'technician_id'            => 'required|exists:users,id',
            'scheduled_datetime'       => 'required|date',
            'estimated_duration_hours' => 'required|integer|min:1|max:12',
            'notes'                    => 'nullable|string|max:1000',
        ]);

        $this->ticketService->assignTechnician($ticket, (int) $validated['technician_id'], $validated);

        return back()->with('success', 'Workshop appointment scheduled and assigned to technician.');
    }

    public function warranties(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');

        $query = Warranty::with(['serialNumber.product', 'order.customer'])->latest();

        if ($status && in_array($status, [Warranty::STATUS_ACTIVE, Warranty::STATUS_EXPIRED, Warranty::STATUS_VOID], true)) {
            $query->where('status', $status);
        }

        if ($search) {
            $term = '%' . trim($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('serialNumber', function ($sq) use ($term) {
                    $sq->where('sn_code', 'like', $term);
                })->orWhereHas('order', function ($oq) use ($term) {
                    $oq->where('order_number', 'like', $term)
                       ->orWhere('customer_name', 'like', $term);
                });
            });
        }

        $warranties = $query->paginate(25)->withQueryString();

        return view('erp.support.warranties', compact('warranties', 'search', 'status'));
    }
}
