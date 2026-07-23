<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketUpdate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        ]);
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
