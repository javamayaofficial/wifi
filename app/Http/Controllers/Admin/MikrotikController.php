<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use App\Services\Mikrotik\MikrotikService;
use App\Services\Mikrotik\RouterImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class MikrotikController extends Controller
{
    /** Cache runtime 30 detik: jangan bebani router tiap load halaman. */
    protected const CACHE_TTL = 30;

    // =================== IMPORT DARI ROUTER ===================

    public function importForm(Request $request): View
    {
        $routers = Router::orderBy('name')->get();
        $plans   = Plan::orderBy('name')->get();

        $router  = $request->router ? Router::find($request->router) : null;
        $preview = null;
        $error   = null;

        if ($router) {
            try {
                $preview = app(RouterImportService::class)->preview($router);
            } catch (\Throwable $e) {
                $error = 'Gagal membaca router: ' . $e->getMessage();
            }
        }

        return view('mikrotik.import', compact('routers', 'plans', 'router', 'preview', 'error'));
    }

    public function import(Request $request, RouterImportService $importer): RedirectResponse
    {
        $data = $request->validate([
            'router_id' => ['required', 'exists:thre_routers,id'],
            'plan_id'   => ['required', 'exists:thre_plans,id'],
            'days'      => ['required', 'integer', 'min:1'],
        ]);

        $router = Router::findOrFail($data['router_id']);

        try {
            $result = $importer->import($router, (int) $data['plan_id'], (int) $data['days']);
        } catch (\Throwable $e) {
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }

        return redirect('/customers')
            ->with('success', "Import selesai: {$result['imported']} pelanggan baru, {$result['skipped']} dilewati.")
            ->with('import_errors', $result['errors']);
    }

    // =================== MONITORING LIVE ===================

    public function monitor(Request $request): View
    {
        $routers = Router::orderBy('name')->get();
        $router  = $request->router ? Router::find($request->router) : $routers->first();

        $resource = null;
        $active   = [];
        $error    = null;

        if ($router) {
            $key = "threfnet:monitor:{$router->id}";

            try {
                $data = Cache::remember($key, self::CACHE_TTL, function () use ($router) {
                    $mikrotik = new MikrotikService($router);

                    return [
                        'resource' => $mikrotik->systemResource(),
                        'active'   => $mikrotik->listActive(),
                    ];
                });

                $resource = $data['resource'];
                $active   = $data['active'];
            } catch (\Throwable $e) {
                $error = 'Gagal menghubungi router: ' . $e->getMessage();
            }
        }

        // Petakan sesi aktif ke data pelanggan di dashboard.
        $usernames = collect($active)->pluck('name')->filter()->all();
        $customers = Customer::whereIn('username', $usernames)->get()->keyBy('username');

        $totalCustomers = $router ? Customer::where('router_id', $router->id)->count() : 0;

        return view('mikrotik.monitor', compact(
            'routers', 'router', 'resource', 'active', 'customers', 'error', 'totalCustomers'
        ));
    }

    public function refresh(Request $request): RedirectResponse
    {
        if ($request->router) {
            Cache::forget("threfnet:monitor:{$request->router}");
        }

        return back()->with('success', 'Data monitoring diperbarui.');
    }

    /** Putus paksa sesi pelanggan dari halaman monitoring. */
    public function disconnect(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'router_id' => ['required', 'exists:thre_routers,id'],
            'username'  => ['required', 'string'],
        ]);

        $router = Router::findOrFail($data['router_id']);

        try {
            (new MikrotikService($router))->killActiveSession($data['username']);
            Cache::forget("threfnet:monitor:{$router->id}");

            return back()->with('success', "Sesi {$data['username']} diputus.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memutus sesi: ' . $e->getMessage());
        }
    }
}
