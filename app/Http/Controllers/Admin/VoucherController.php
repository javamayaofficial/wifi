<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Models\Voucher;
use App\Models\VoucherProfile;
use App\Models\Reseller;
use App\Services\Mikrotik\HotspotService;
use App\Services\VoucherSalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function __construct(protected VoucherSalesService $sales) {}

    public function index(Request $request): View
    {
        $vouchers = Voucher::query()
            ->with(['profile', 'router', 'reseller'])
            ->when($request->batch, fn ($q, $s) => $q->where('batch', $s))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $summary = Voucher::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $batches = Voucher::distinct()->orderByDesc('batch')->pluck('batch')->take(20);

        return view('vouchers.index', [
            'vouchers'   => $vouchers,
            'summary'    => $summary,
            'batches'    => $batches,
            'profiles'   => VoucherProfile::orderBy('name')->get(),
            'routers'    => Router::orderBy('name')->get(),
            'resellers'  => Reseller::where('is_active', true)->orderBy('name')->get(),
            'omzetBulan' => $this->sales->revenueForMonth(now()->year, now()->month),
        ]);
    }

    public function storeProfile(Request $request): RedirectResponse
    {
        VoucherProfile::create($request->validate([
            'name'            => ['required', 'string', 'max:100'],
            'hotspot_profile' => ['required', 'string', 'max:100'],
            'price'           => ['required', 'numeric', 'min:0'],
            'agent_price'     => ['nullable', 'numeric', 'min:0'],
            'validity'        => ['required', 'string', 'max:20'],
            'shelf_life_days' => ['required', 'integer', 'min:0'],
            'code_length'     => ['required', 'integer', 'min:4', 'max:12'],
        ]));

        return back()->with('success', 'Profil voucher dibuat.');
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'voucher_profile_id' => ['required', 'exists:thre_voucher_profiles,id'],
            'router_id'          => ['required', 'exists:thre_routers,id'],
            'count'              => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        $profile = VoucherProfile::findOrFail($data['voucher_profile_id']);
        $router  = Router::findOrFail($data['router_id']);

        try {
            $result = (new HotspotService($router))->generate($profile, (int) $data['count']);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghubungi router: ' . $e->getMessage());
        }

        $msg = "Batch {$result['batch']}: {$result['created']} voucher dibuat.";

        if ($result['errors']) {
            $msg .= ' ' . count($result['errors']) . ' gagal.';
        }

        return redirect("/vouchers?batch={$result['batch']}")
            ->with($result['errors'] ? 'error' : 'success', $msg);
    }

    /** Halaman cetak voucher (siap gunting). */
    public function print(Request $request): View
    {
        $vouchers = Voucher::with('profile')
            ->where('batch', $request->batch)
            ->orderBy('code')
            ->get();

        abort_if($vouchers->isEmpty(), 404);

        return view('vouchers.print', compact('vouchers'));
    }

    /** Jual langsung di tempat (harga jual profil). */
    public function markSold(Voucher $voucher): RedirectResponse
    {
        $ok = $this->sales->sellDirect($voucher->load('profile'));

        return back()->with(
            $ok ? 'success' : 'error',
            $ok ? "Voucher {$voucher->code} terjual." : 'Voucher sudah tidak tersedia.'
        );
    }

    /** Titipkan sejumlah voucher ke agen/warung. */
    public function handOver(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reseller_id'        => ['required', 'exists:thre_resellers,id'],
            'voucher_profile_id' => ['required', 'exists:thre_voucher_profiles,id'],
            'count'              => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $reseller = Reseller::findOrFail($data['reseller_id']);

        $jumlah = $this->sales->handOver($reseller, (int) $data['voucher_profile_id'], (int) $data['count']);

        if ($jumlah === 0) {
            return back()->with('error', 'Tidak ada stok voucher tersedia untuk profil tersebut.');
        }

        return back()->with('success', "{$jumlah} voucher dititipkan ke {$reseller->name}.");
    }

    /** Agen menyetorkan hasil penjualan. */
    public function settle(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reseller_id'        => ['required', 'exists:thre_resellers,id'],
            'voucher_profile_id' => ['required', 'exists:thre_voucher_profiles,id'],
            'count'              => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $reseller = Reseller::findOrFail($data['reseller_id']);

        $hasil = $this->sales->settleFromAgent($reseller, (int) $data['voucher_profile_id'], (int) $data['count']);

        if ($hasil['count'] === 0) {
            return back()->with('error', "Tidak ada stok titipan {$reseller->name} untuk profil tersebut.");
        }

        return back()->with('success', sprintf(
            '%d voucher disetor dari %s. Omzet Rp %s, margin agen Rp %s.',
            $hasil['count'],
            $reseller->name,
            number_format($hasil['omzet'], 0, ',', '.'),
            number_format($hasil['margin'], 0, ',', '.')
        ));
    }

    public function syncUsage(Request $request): RedirectResponse
    {
        $router = Router::findOrFail($request->router_id);

        try {
            $count = (new HotspotService($router))->syncUsage();
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal sinkron: ' . $e->getMessage());
        }

        return back()->with('success', "{$count} voucher ditandai terpakai.");
    }
}
