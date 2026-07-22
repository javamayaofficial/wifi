<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Transaction;
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
        ];

        return view('dashboard', compact('stats'));
    }
}
