<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Router;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\Voucher;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total'    => Customer::count(),
            'active'   => Customer::where('status', 'active')->count(),
            'inactive' => Customer::whereIn('status', ['isolated', 'suspended', 'new'])->count(),
            'revenue'  => Transaction::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('amount'),
            'tickets_open'  => Ticket::whereIn('status', ['baru', 'ditugaskan', 'proses'])->count(),
            'arrears_count' => Customer::whereIn('status', ['active', 'isolated', 'new'])
                ->whereDate('expired_date', '<', today())->count(),
            'routers_down'  => Router::where('is_up', false)->count(),
            'voucher_revenue' => (float) Voucher::sold()
                ->whereMonth('sold_at', now()->month)
                ->whereYear('sold_at', now()->year)
                ->sum('sale_price'),
        ];

        return view('dashboard', compact('stats'));
    }
}
