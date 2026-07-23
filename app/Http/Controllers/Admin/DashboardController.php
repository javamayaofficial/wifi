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

        $triage = [];

        if ($stats['routers_down'] > 0) {
            $triage[] = [
                'count' => $stats['routers_down'],
                'title' => 'Router tidak terjangkau',
                'sub' => 'Perlu pengecekan jaringan dan listrik di lokasi.',
                'url' => '/routers',
                'tone' => 'down',
            ];
        }

        $urgentTickets = Ticket::query()
            ->whereIn('status', ['baru', 'ditugaskan', 'proses'])
            ->whereIn('priority', ['darurat', 'tinggi'])
            ->count();

        if ($urgentTickets > 0) {
            $triage[] = [
                'count' => $urgentTickets,
                'title' => 'Tiket prioritas tinggi',
                'sub' => 'Ada gangguan yang perlu didahulukan hari ini.',
                'url' => '/tickets',
                'tone' => 'warn',
            ];
        }

        $unsyncedCustomers = Customer::query()
            ->whereNotNull('sync_error')
            ->count();

        if ($unsyncedCustomers > 0) {
            $triage[] = [
                'count' => $unsyncedCustomers,
                'title' => 'Sinkronisasi MikroTik gagal',
                'sub' => 'Sebagian pelanggan belum sinkron ke router.',
                'url' => '/customers',
                'tone' => 'warn',
            ];
        }

        if ($stats['arrears_count'] > 0) {
            $triage[] = [
                'count' => $stats['arrears_count'],
                'title' => 'Pelanggan menunggak',
                'sub' => 'Sudah melewati jatuh tempo dan perlu ditindaklanjuti.',
                'url' => '/reports/arrears',
                'tone' => 'down',
            ];
        }

        $jatuhTempo = Customer::query()
            ->whereIn('status', ['active', 'isolated', 'new'])
            ->whereBetween('expired_date', [today(), today()->copy()->addDays(3)])
            ->count();

        $lastPayments = Transaction::query()
            ->with('customer')
            ->where('status', 'paid')
            ->latest('paid_at')
            ->limit(5)
            ->get();

        $recentTickets = Ticket::query()
            ->with(['customer', 'assignee'])
            ->whereIn('status', ['baru', 'ditugaskan', 'proses'])
            ->orderByRaw("
                case priority
                    when 'darurat' then 1
                    when 'tinggi' then 2
                    when 'sedang' then 3
                    else 4
                end
            ")
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return view('dashboard', compact('stats', 'triage', 'jatuhTempo', 'lastPayments', 'recentTickets'));
    }
}
