<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        // High level counters for the ERP executive & staff overview
        $totalProducts = DB::table('products')->count();
        $lowStockCount = DB::table('products')
            ->where('low_stock_threshold', '>', 0)
            ->count(); // Can be refined once stock aggregate is in place

        $pendingRestocks = DB::table('restock_orders')
            ->where('status', 'Pending')
            ->count();

        $activeOrders = DB::table('orders')
            ->whereIn('order_status', ['Paid', 'Assembling', 'Testing'])
            ->count();

        $totalRevenue = DB::table('orders')
            ->whereIn('order_status', ['Paid', 'Assembling', 'Testing', 'Shipped', 'Completed'])
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
