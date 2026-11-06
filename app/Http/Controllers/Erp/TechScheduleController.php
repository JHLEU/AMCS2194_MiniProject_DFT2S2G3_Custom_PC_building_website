<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\TechSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TechScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $techId = $request->query('technician_id');
        $date = $request->query('date');

        $query = TechSchedule::with([
            'ticket.customer',
            'ticket.warranty.serialNumber.product',
            'technician',
        ])->orderBy('scheduled_datetime', 'asc');

        if ($techId) {
            $query->where('technician_id', $techId);
        }

        if ($date) {
            $query->whereDate('scheduled_datetime', $date);
        }

        $schedules = $query->paginate(25)->withQueryString();

        $technicians = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['Staff_Tech', 'Admin']);
        })->get();

        return view('erp.support.schedules', compact('schedules', 'technicians', 'techId', 'date'));
    }
}
