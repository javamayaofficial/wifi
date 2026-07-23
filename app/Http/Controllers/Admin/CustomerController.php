<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\CustomersImport;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use App\Jobs\SendWhatsAppNotification;
use App\Services\Mikrotik\MikrotikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        if (blank($data['identity_card_path'] ?? null)) {
            unset($data['identity_card_path']);
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

    /** Kirim ulang info akses portal pelanggan via WhatsApp. */
    public function resetPortalPassword(Customer $customer): RedirectResponse
    {
        if (! $customer->phone) {
            return back()->with('error', 'Nomor WhatsApp pelanggan belum diisi.');
        }

        SendWhatsAppNotification::dispatch(
            $customer,
            "[THRE.F.NET - Portal Pelanggan]\n"
            . "Halo {$customer->name}, akses portal pelanggan sekarang menggunakan OTP WhatsApp.\n"
            . "Buka: " . config('app.url') . "/portal/login\n"
            . "Masukkan nomor WhatsApp Anda, lalu verifikasi kode OTP yang dikirim.\n"
            . 'Mohon jangan bagikan kode OTP kepada siapa pun.',
            'portal_access'
        );

        return back()->with('success', 'Panduan akses portal via OTP berhasil dikirim ke WhatsApp pelanggan.');
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

        $mappedColumns = collect($import->detectedColumns)
            ->map(fn ($field, $index) => 'kolom ' . ($index + 1) . ' -> ' . $field)
            ->values()
            ->all();

        $msg = "Import selesai: {$import->successCount} berhasil, " . count($import->errors) . ' gagal.';

        return redirect('/customers')
            ->with($import->errors ? 'error' : 'success', $msg)
            ->with('import_errors', $import->errors)
            ->with('import_detected_columns', $mappedColumns);
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $request->merge([
            'national_id_number' => preg_replace('/\D+/', '', (string) $request->input('national_id_number')),
        ]);

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:150'],
            'username'     => ['required', 'string', 'max:100', Rule::unique('thre_customers', 'username')->ignore($ignoreId)],
            'password'     => [$ignoreId ? 'nullable' : 'required', 'string', 'max:100'],
            'plan_id'      => ['required', 'exists:thre_plans,id'],
            'router_id'    => ['required', 'exists:thre_routers,id'],
            'expired_date' => ['required', 'date'],
            'status'       => ['required', 'in:new,active,isolated,suspended'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'email'        => ['nullable', 'email', 'max:150'],
            'address'       => ['nullable', 'string'],
            'national_id_number' => ['nullable', 'digits_between:12,20'],
            'latitude'      => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'     => ['nullable', 'numeric', 'between:-180,180'],
            'odp_name'      => ['nullable', 'string', 'max:100'],
            'odp_port'      => ['nullable', 'string', 'max:50'],
            'device_type'   => ['nullable', 'string', 'max:100'],
            'device_serial' => ['nullable', 'string', 'max:100'],
            'identity_card' => ['nullable', 'image', 'max:4096'],
            'installed_at'  => ['nullable', 'date'],
            'reseller_id'   => ['nullable', 'exists:thre_resellers,id'],
        ]);

        $customer = $ignoreId ? Customer::find($ignoreId) : null;

        if ($request->hasFile('identity_card')) {
            if ($customer?->identity_card_path) {
                Storage::disk('public')->delete($customer->identity_card_path);
            }

            $data['identity_card_path'] = $request->file('identity_card')->store('customer-identity-cards', 'public');
        }

        unset($data['identity_card']);

        if (
            filled($data['name'] ?? null)
            && filled($data['address'] ?? null)
            && filled($data['national_id_number'] ?? null)
            && filled($data['latitude'] ?? null)
            && filled($data['longitude'] ?? null)
            && filled($data['identity_card_path'] ?? $customer?->identity_card_path)
        ) {
            $data['profile_completed_at'] = now();
        } else {
            $data['profile_completed_at'] = null;
        }

        return $data;
    }
}
