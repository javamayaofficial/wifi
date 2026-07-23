<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $items = InventoryItem::query()
            ->with('customer')
            ->when($request->type, fn ($q, $s) => $q->where('type', $s))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->q, fn ($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('name', 'like', "%{$s}%")->orWhere('serial', 'like', "%{$s}%");
            }))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $summary = InventoryItem::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $customers = Customer::orderBy('name')->get(['id', 'name', 'username']);

        return view('inventory.index', compact('items', 'summary', 'customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        InventoryItem::create($this->validated($request));

        return back()->with('success', 'Perangkat ditambahkan.');
    }

    public function update(Request $request, InventoryItem $inventory): RedirectResponse
    {
        $inventory->update($this->validated($request, $inventory->id));

        return back()->with('success', 'Perangkat diperbarui.');
    }

    public function destroy(InventoryItem $inventory): RedirectResponse
    {
        $inventory->delete();

        return back()->with('success', 'Perangkat dihapus.');
    }

    protected function validated(Request $request, ?int $ignore = null): array
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:120'],
            'type'           => ['required', 'in:' . implode(',', array_keys(InventoryItem::TYPES))],
            'serial'         => ['nullable', 'string', 'max:100', Rule::unique('thre_inventory', 'serial')->ignore($ignore)],
            'status'         => ['required', 'in:gudang,terpasang,rusak,hilang'],
            'customer_id'    => ['nullable', 'exists:thre_customers,id'],
            'purchase_date'  => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'note'           => ['nullable', 'string'],
        ]);

        // Perangkat di gudang tidak boleh terikat pelanggan.
        if ($data['status'] !== 'terpasang') {
            $data['customer_id'] = null;
        }

        return $data;
    }
}
