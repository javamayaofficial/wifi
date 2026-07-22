<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\CustomersImport;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use App\Services\Mikrotik\MikrotikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->with(['plan', 'router'])
            ->when($request->q, fn ($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")->orWhere('username', 'like', "%{$s}%");
            }))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.form', [
            'customer' => new Customer(['status' => 'new', 'expired_date' => now()->addDays(30)]),
            'plans'    => Plan::orderBy('name')->get(),
            'routers'  => Router::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Customer::create($this->validated($request));

        return redirect('/customers')->with('success', 'Pelanggan THRE.F.NET ditambahkan.');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.form', [
            'customer' => $customer,
            'plans'    => Plan::orderBy('name')->get(),
            'routers'  => Router::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $this->validated($request, $customer->id);

        // Password kosong = tidak diubah.
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $customer->update($data);

        return redirect('/customers')->with('success', 'Data pelanggan diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return back()->with('success', 'Pelanggan dihapus.');
    }

    /** Aktifkan / isolir manual dari dashboard (tanpa menunggu scheduler). */
    public function toggle(Customer $customer): RedirectResponse
    {
        try {
            $mikrotik = MikrotikService::forCustomer($customer);

            if ($customer->status === 'active') {
                $mikrotik->disableUser($customer);
                $customer->update(['status' => 'isolated']);
                $msg = "Pelanggan {$customer->username} diisolir.";
            } else {
                $mikrotik->enableUser($customer);
                $customer->update(['status' => 'active']);
                $msg = "Pelanggan {$customer->username} diaktifkan.";
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghubungi MikroTik: ' . $e->getMessage());
        }
    }

    public function importForm(): View
    {
        return view('customers.import');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new CustomersImport();
        Excel::import($import, $request->file('file'));

        $msg = "Import selesai: {$import->successCount} berhasil, " . count($import->errors) . ' gagal.';

        return redirect('/customers')
            ->with($import->errors ? 'error' : 'success', $msg)
            ->with('import_errors', $import->errors);
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'         => ['required', 'string', 'max:150'],
            'username'     => ['required', 'string', 'max:100', Rule::unique('thre_customers', 'username')->ignore($ignoreId)],
            'password'     => [$ignoreId ? 'nullable' : 'required', 'string', 'max:100'],
            'plan_id'      => ['required', 'exists:thre_plans,id'],
            'router_id'    => ['required', 'exists:thre_routers,id'],
            'expired_date' => ['required', 'date'],
            'status'       => ['required', 'in:new,active,isolated,suspended'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'email'        => ['nullable', 'email', 'max:150'],
        ]);
    }
}
