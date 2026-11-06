<?php

namespace App\Services;

use App\Models\SerialNumber;
use App\Models\SupportTicket;
use App\Models\TechSchedule;
use App\Models\User;
use App\Models\Warranty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class SupportTicketService
{
    /**
     * Submit a new customer support or warranty claim ticket.
     *
     * @throws ValidationException
     */
    public function createTicket(User $customer, array $data): SupportTicket
    {
        $warrantyId = $data['warranty_id'] ?? null;

        if ($warrantyId) {
            $warranty = Warranty::with('order')->findOrFail($warrantyId);
            // Verify ownership: warranty order must belong to this customer
            if ($warranty->order && (int) $warranty->order->customer_id !== (int) $customer->id) {
                throw ValidationException::withMessages([
                    'warranty_id' => ['You do not own this warranty coverage.'],
                ]);
            }
        }

        return SupportTicket::create([
            'customer_id'       => $customer->id,
            'warranty_id'       => $warrantyId ?: null,
            'subject'           => $data['subject'] ?? 'Technical Support Inquiry',
            'issue_description' => $data['issue_description'],
            'status'            => SupportTicket::STATUS_OPEN,
        ]);
    }

    /**
     * Transition ticket status and update hardware serial state if appropriate.
     */
    public function updateStatus(SupportTicket $ticket, string $status): SupportTicket
    {
        if (!in_array($status, SupportTicket::STATUSES, true)) {
            throw new \InvalidArgumentException("Invalid ticket status [{$status}].");
        }

        $ticket->status = $status;
        $ticket->save();

        // If ticket is RMA / WaitingForParts, mark the serial number as RMA
        if ($status === SupportTicket::STATUS_WAITING_FOR_PARTS && $ticket->warranty && $ticket->warranty->serialNumber) {
            $ticket->warranty->serialNumber->update(['status' => SerialNumber::STATUS_RMA]);
        }

        return $ticket;
    }

    /**
     * Assign a ticket to a technician and schedule a workshop appointment slot.
     */
    public function assignTechnician(SupportTicket $ticket, int $technicianId, array $scheduleData): TechSchedule
    {
        $tech = User::findOrFail($technicianId);

        $schedule = TechSchedule::create([
            'support_ticket_id'        => $ticket->id,
            'technician_id'            => $tech->id,
            'scheduled_datetime'       => $scheduleData['scheduled_datetime'],
            'estimated_duration_hours' => (int) ($scheduleData['estimated_duration_hours'] ?? 1),
            'notes'                    => $scheduleData['notes'] ?? null,
        ]);

        // Auto-advance ticket to Diagnosing if currently Open
        if ($ticket->status === SupportTicket::STATUS_OPEN) {
            $ticket->update(['status' => SupportTicket::STATUS_DIAGNOSING]);
        }

        return $schedule;
    }

    /**
     * Aggregated counter breakdown for the ERP ticket triage workbench.
     */
    public function getDashboardCounters(): array
    {
        return [
            'total'             => SupportTicket::count(),
            'open'              => SupportTicket::where('status', SupportTicket::STATUS_OPEN)->count(),
            'diagnosing'        => SupportTicket::where('status', SupportTicket::STATUS_DIAGNOSING)->count(),
            'waiting_for_parts' => SupportTicket::where('status', SupportTicket::STATUS_WAITING_FOR_PARTS)->count(),
            'resolved'          => SupportTicket::where('status', SupportTicket::STATUS_RESOLVED)->count(),
            'closed'            => SupportTicket::where('status', SupportTicket::STATUS_CLOSED)->count(),
        ];
    }
}
