<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailNotification;
use App\Jobs\SendWhatsAppNotification;
use App\Models\Customer;
use App\Models\NotificationLog;
use App\Services\Notifications\MailketingService;
use Illuminate\Console\Command;

/**
 * Reminder BERJENJANG sebelum jatuh tempo.
 *
 * Mencegah lebih murah daripada menghukum: mengingatkan pelanggan sebelum
 * jatuh tempo biasanya menurunkan jumlah isolir dan keluhan jauh lebih efektif
 * daripada isolir itu sendiri.
 *
 * Jadwal default: H-7, H-3, H-1, dan H+1 (setelah lewat tempo).
 */
class SendPaymentReminders extends Command
{
    protected $signature = 'threfnet:send-reminders';
    protected $description = 'THRE.F.NET: kirim pengingat jatuh tempo berjenjang';

    /** offset hari => kode konteks */
    protected array $schedule = [
        7  => 'reminder_h7',
        3  => 'reminder_h3',
        1  => 'reminder_h1',
        -1 => 'reminder_lewat',
    ];

    public function handle(): int
    {
        $total = 0;

        foreach ($this->schedule as $offset => $context) {
            $targetDate = now()->addDays($offset)->toDateString();

            $customers = Customer::query()
                ->with('plan')
                ->whereIn('status', ['active', 'new'])
                ->whereDate('expired_date', $targetDate)
                ->get();

            foreach ($customers as $customer) {
                if ($this->alreadySent($customer->id, $context)) {
                    continue;
                }

                $this->dispatchReminder($customer, $offset, $context);
                $total++;
            }
        }

        $this->info("Reminder terkirim: {$total}");

        return self::SUCCESS;
    }

    /** Cegah kirim ganda bila command dijalankan berulang di hari yang sama. */
    protected function alreadySent(int $customerId, string $context): bool
    {
        return NotificationLog::where('customer_id', $customerId)
            ->where('context', $context)
            ->whereDate('created_at', today())
            ->exists();
    }

    protected function dispatchReminder(Customer $customer, int $offset, string $context): void
    {
        $tanggal = $customer->expired_date->translatedFormat('d F Y');
        $harga   = 'Rp ' . number_format($customer->plan->price, 0, ',', '.');
        $link    = config('app.url') . '/bayar/' . $customer->username;

        if ($offset > 0) {
            $judul = "Pengingat Jatuh Tempo ({$offset} hari lagi)";
            $wa = "[THRE.F.NET - Pengingat Jatuh Tempo]\n"
                . "Halo {$customer->name}, layanan internet Anda ({$customer->plan->name}) "
                . "akan jatuh tempo pada {$tanggal} ({$offset} hari lagi).\n"
                . "Tagihan: {$harga}\n"
                . "Bayar di sini: {$link}\n"
                . "Terima kasih.";
        } else {
            $judul = 'Tagihan Lewat Jatuh Tempo';
            $wa = "[THRE.F.NET - Tagihan Lewat Jatuh Tempo]\n"
                . "Halo {$customer->name}, tagihan Anda jatuh tempo pada {$tanggal} "
                . "dan belum kami terima.\n"
                . "Tagihan: {$harga}\n"
                . "Segera bayar agar layanan tidak terisolasi: {$link}";
        }

        $html = MailketingService::template($judul,
            "<p>Halo <b>{$customer->name}</b>,</p>"
            . "<p>Paket: <b>{$customer->plan->name}</b><br>"
            . "Jatuh tempo: <b>{$tanggal}</b><br>"
            . "Tagihan: <b>{$harga}</b></p>"
            . "<p><a href=\"{$link}\" style=\"background:#0d6efd;color:#fff;padding:10px 18px;"
            . "border-radius:6px;text-decoration:none;display:inline-block\">Bayar Sekarang</a></p>"
        );

        SendWhatsAppNotification::dispatch($customer, $wa, $context);
        SendEmailNotification::dispatch($customer, "THRE.F.NET - {$judul}", $html, $context);
    }
}
