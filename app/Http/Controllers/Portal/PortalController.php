<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PortalController extends Controller
{
    protected function customer(Request $request): Customer
    {
        return $request->attributes->get('portal_customer');
    }

    public function dashboard(Request $request): View
    {
        $customer = $this->customer($request);

        return view('portal.dashboard', [
            'customer'     => $customer,
            'transactions' => $customer->transactions()->limit(5)->get(),
            'tickets'      => $customer->tickets()->latest()->limit(5)->get(),
            'profileComplete' => $customer->profileIsComplete(),
        ]);
    }

    public function profile(Request $request): View
    {
        return view('portal.profile', [
            'customer' => $this->customer($request),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $customer = $this->customer($request);
        $request->merge([
            'national_id_number' => preg_replace('/\D+/', '', (string) $request->input('national_id_number')),
        ]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:2000'],
            'national_id_number' => ['required', 'digits_between:12,20'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'identity_card' => [$customer->hasIdentityCard() ? 'nullable' : 'required', 'image', 'max:4096'],
        ], [
            'identity_card.required' => 'Foto KTP wajib diunggah agar data pelanggan bisa dipakai sebagai acuan titik pemasangan.',
        ]);

        if ($request->hasFile('identity_card')) {
            if ($customer->identity_card_path) {
                Storage::disk('public')->delete($customer->identity_card_path);
            }

            $data['identity_card_path'] = $request->file('identity_card')->store('customer-identity-cards', 'public');
        }

        unset($data['identity_card']);

        $customer->update(array_merge($data, [
            'profile_completed_at' => now(),
        ]));

        return back()->with('success', 'Profil pelanggan berhasil diperbarui. Titik lokasi ini sekarang menjadi acuan pada peta pemasangan.');
    }

    public function invoices(Request $request): View
    {
        $customer = $this->customer($request);

        return view('portal.invoices', [
            'customer'     => $customer,
            'transactions' => $customer->transactions()->paginate(20),
        ]);
    }

    public function tickets(Request $request): View
    {
        $customer = $this->customer($request);

        return view('portal.tickets', [
            'customer' => $customer,
            'tickets'  => $customer->tickets()->latest()->paginate(20),
        ]);
    }

    /** Pelanggan melaporkan gangguan sendiri. */
    public function storeTicket(Request $request): RedirectResponse
    {
        $customer = $this->customer($request);

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category'    => ['required', 'in:' . implode(',', array_keys(Ticket::CATEGORIES))],
        ]);

        // Cegah spam: satu laporan terbuka per pelanggan pada satu waktu.
        $adaTerbuka = $customer->tickets()
            ->whereNotIn('status', ['selesai', 'batal'])
            ->exists();

        if ($adaTerbuka) {
            return back()->with('error', 'Anda masih memiliki laporan yang sedang kami tangani.');
        }

        $ticket = Ticket::create([
            'ticket_number' => Ticket::generateNumber(),
            'customer_id'   => $customer->id,
            'title'         => $data['title'],
            'description'   => $data['description'],
            'category'      => $data['category'],
            'priority'      => 'normal',
            'status'        => 'baru',
        ]);

        TicketUpdate::create([
            'ticket_id' => $ticket->id,
            'note'      => 'Laporan dibuat oleh pelanggan melalui portal.',
            'status_to' => 'baru',
        ]);

        return back()->with('success', "Laporan {$ticket->ticket_number} terkirim. Kami akan segera menindaklanjuti.");
    }
}
