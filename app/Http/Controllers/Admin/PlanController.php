<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        return view('plans.index', ['plans' => Plan::withCount('customers')->orderBy('price')->get()]);
    }

    public function create(): View
    {
        return view('plans.form', ['plan' => new Plan(['duration_days' => 30])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Plan::create($this->validated($request));

        return redirect('/plans')->with('success', 'Paket THRE.F.NET ditambahkan.');
    }

    public function edit(Plan $plan): View
    {
        return view('plans.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $plan->update($this->validated($request));

        return redirect('/plans')->with('success', 'Paket diperbarui.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->customers()->exists()) {
            return back()->with('error', 'Paket tidak bisa dihapus karena masih dipakai pelanggan.');
        }

        $plan->delete();

        return back()->with('success', 'Paket dihapus.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'price'            => ['required', 'numeric', 'min:0'],
            'bandwidth'        => ['required', 'string', 'max:50'],
            'duration_days'    => ['required', 'integer', 'min:1'],
            'mikrotik_profile' => ['required', 'string', 'max:100'],
        ]);
    }
}
