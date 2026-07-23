<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppNotification;
use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketUpdate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $tickets = Ticket::query()
            ->with(['customer', 'assignee'])
            // Teknisi hanya melihat tiket miliknya sendiri.
            ->when($user->hasRole('teknisi') && ! $user->isOwner(),
                fn ($q) => $q->where('assigned_to', $user->id))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->priority, fn ($q, $s) => $q->where('priority', $s))
            ->when($request->q, fn ($q, $s) => $q->where(function ($w) use ($s) {
                $w->where('ticket_number', 'like', "%{$s}%")->orWhere('title', 'like', "%{$s}%");
            }))
            ->orderByRaw("FIELD(status,'baru','ditugaskan','proses','selesai','batal')")
            ->orderByRaw("FIELD(priority,'darurat','tinggi','normal','rendah')")
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $counts = [
            'baru'   => Ticket::where('status', 'baru')->count(),
            'proses' => Ticket::whereIn('status', ['ditugaskan', 'proses'])->count(),
        ];

        return view('tickets.index', compact('tickets', 'counts'));
    }

    public function create(): View
    {
        return view('tickets.form', [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'username']),
            'teknisi'   => User::whereIn('role', ['teknisi', 'admin'])->where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:thre_customers,id'],
            'title'       => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'category'    => ['required', 'in:' . implode(',', array_keys(Ticket::CATEGORIES))],
            'priority'    => ['required', 'in:rendah,normal,tinggi,darurat'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $data['ticket_number'] = Ticket::generateNumber();
        $data['created_by']    = Auth::id();
        $data['status']        = $data['assigned_to'] ? 'ditugaskan' : 'baru';
        $data['assigned_at']   = $data['assigned_to'] ? now() : null;

        $ticket = Ticket::create($data);

        TicketUpdate::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'note'      => 'Tiket dibuat.',
            'status_to' => $ticket->status,
        ]);

        return redirect("/tickets/{$ticket->id}")->with('success', "Tiket {$ticket->ticket_number} dibuat.");
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load(['customer.plan', 'assignee', 'creator', 'updatesLog.user']);

        return view('tickets.show', [
            'ticket'  => $ticket,
            'teknisi' => User::whereIn('role', ['teknisi', 'admin'])->where('is_active', true)->get(),
        ]);
    }

    /** Tambah catatan / ubah status / unggah foto bukti pekerjaan. */
    public function addUpdate(Request $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'note'        => ['required', 'string'],
            'status_to'   => ['nullable', 'in:' . implode(',', Ticket::STATUSES)],
            'photo'       => ['nullable', 'image', 'max:4096'],
            'resolution'  => ['nullable', 'string'],
        ]);

        $statusFrom = $ticket->status;
        $statusTo   = $data['status_to'] ?? $statusFrom;

        $path = $request->hasFile('photo')
            ? $request->file('photo')->store('tickets', 'public')
            : null;

        TicketUpdate::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => Auth::id(),
            'note'        => $data['note'],
            'photo_path'  => $path,
            'status_from' => $statusFrom,
            'status_to'   => $statusTo,
        ]);

        if ($statusTo !== $statusFrom) {
            $ticket->status = $statusTo;

            if ($statusTo === 'selesai') {
                $ticket->resolved_at = now();
                $ticket->resolution  = $data['resolution'] ?? $data['note'];

                $this->notifyCustomerResolved($ticket);
            }

            $ticket->save();
        }

        return back()->with('success', 'Catatan tiket ditambahkan.');
    }

    public function assign(Request $request, Ticket $ticket): RedirectResponse
    {
        $data = $request->validate(['assigned_to' => ['required', 'exists:users,id']]);

        $ticket->update([
            'assigned_to' => $data['assigned_to'],
            'assigned_at' => now(),
            'status'      => $ticket->status === 'baru' ? 'ditugaskan' : $ticket->status,
        ]);

        $nama = User::find($data['assigned_to'])?->name;

        TicketUpdate::create([
            'ticket_id' => $ticket->id,
            'user_id'   => Auth::id(),
            'note'      => "Ditugaskan kepada {$nama}.",
            'status_to' => $ticket->status,
        ]);

        return back()->with('success', "Tiket ditugaskan ke {$nama}.");
    }

    protected function notifyCustomerResolved(Ticket $ticket): void
    {
        $customer = $ticket->customer;

        if (! $customer?->phone) {
            return;
        }

        SendWhatsAppNotification::dispatch(
            $customer,
            "[THRE.F.NET - Gangguan Selesai]\n"
            . "Halo {$customer->name}, laporan Anda ({$ticket->ticket_number}) telah kami tangani.\n"
            . "Catatan: {$ticket->resolution}\n"
            . 'Bila masih bermasalah, silakan hubungi kami kembali. Terima kasih.',
            'ticket_resolved'
        );
    }
}
