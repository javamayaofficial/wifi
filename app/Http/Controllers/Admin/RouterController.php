<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Services\Mikrotik\MikrotikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RouterController extends Controller
{
    public function index(): View
    {
        return view('routers.index', ['routers' => Router::withCount('customers')->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('routers.form', ['router' => new Router(['api_port' => 8728])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Router::create($this->validated($request));

        return redirect('/routers')->with('success', 'Router ditambahkan.');
    }

    public function edit(Router $router): View
    {
        return view('routers.form', compact('router'));
    }

    public function update(Request $request, Router $router): RedirectResponse
    {
        $data = $this->validated($request);

        if (blank($data['password'] ?? null)) {
            unset($data['password']); // kosong = tidak diubah
        }

        $router->update($data);

        return redirect('/routers')->with('success', 'Router diperbarui.');
    }

    public function destroy(Router $router): RedirectResponse
    {
        if ($router->customers()->exists()) {
            return back()->with('error', 'Router tidak bisa dihapus karena masih dipakai pelanggan.');
        }

        $router->delete();

        return back()->with('success', 'Router dihapus.');
    }

    /** Tombol "Test Koneksi" di dashboard. */
    public function test(Router $router): RedirectResponse
    {
        $ok = (new MikrotikService($router))->testConnection();

        return back()->with(
            $ok ? 'success' : 'error',
            $ok ? "Koneksi ke {$router->name} berhasil." : "Koneksi ke {$router->name} GAGAL. Cek IP, port, dan kredensial."
        );
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'ip'       => ['required', 'string', 'max:45'],
            'api_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:100'],
            'password' => [$request->route('router') ? 'nullable' : 'required', 'string', 'max:100'],
            'use_tls'  => ['nullable', 'boolean'],
        ]);
    }
}
