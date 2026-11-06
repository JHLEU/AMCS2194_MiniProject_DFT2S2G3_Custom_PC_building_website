<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        // High level counters for the ERP executive & staff overview
        $totalProducts = Product::count();
        $lowStockCount = Product::lowStock()->count();

        $pendingRestocks = DB::table('restock_orders')
            ->where('status', 'Pending')
            ->count();

        $activeOrders = Order::active()->count();

        $totalRevenue = (float) Order::where('order_status', '!=', Order::STATUS_CANCELLED)
            ->sum('total_selling_amount');

        $openTickets = DB::table('support_tickets')
            ->whereIn('status', ['Open', 'Diagnosing', 'WaitingForParts'])
            ->count();

        return view('erp.dashboard', compact(
            'user',
            'totalProducts',
            'lowStockCount',
            'pendingRestocks',
            'activeOrders',
            'totalRevenue',
            'openTickets'
        ));
    }
}
