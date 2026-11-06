<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\Warranty;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function __construct(
        protected SupportTicketService $ticketService
    ) {}

    public function index(): View
    {
        $tickets = SupportTicket::with(['warranty.serialNumber.product', 'latestSchedule.technician'])
            ->where('customer_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('customer.support.index', compact('tickets'));
    }

    public function create(Request $request): View
    {
        $userId = Auth::id();

        // Customer's available hardware warranties to link with RMA claim
        $warranties = Warranty::with(['serialNumber.product', 'order'])
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('customer_id', $userId);
            })
            ->where('status', Warranty::STATUS_ACTIVE)
            ->get();

        $selectedWarrantyId = $request->query('warranty_id');

        return view('customer.support.create', compact('warranties', 'selectedWarrantyId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'warranty_id'       => 'nullable|exists:warranties,id',
            'subject'           => 'required|string|max:200',
            'issue_description' => 'required|string|min:10',
        ]);

        $ticket = $this->ticketService->createTicket(Auth::user(), $validated);

        return redirect()->route('customer.support.show', $ticket)
            ->with('success', 'Your support ticket has been submitted. A certified technician will review your case.');
    }

    public function show(SupportTicket $ticket): View
    {
        // Enforce ownership
        if ((int) $ticket->customer_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized access to support ticket.');
        }

        $ticket->load(['warranty.serialNumber.product', 'warranty.order', 'schedules.technician']);

        return view('customer.support.show', compact('ticket'));
    }
}
