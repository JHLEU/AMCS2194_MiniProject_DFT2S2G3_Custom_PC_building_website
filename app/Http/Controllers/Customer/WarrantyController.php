<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WarrantyController extends Controller
{
    public function index(Request $request): View
    {
        $userId = Auth::id();

        $warranties = Warranty::with(['serialNumber.product', 'order'])
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('customer_id', $userId);
            })
            ->latest('start_date')
            ->paginate(15);

        return view('customer.warranties.index', compact('warranties'));
    }
}
