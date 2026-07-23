<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailNotification;
use App\Jobs\SendWhatsAppNotification;
use App\Models\Customer;
use App\Models\NotificationLog;
use App\Models\Setting;
use App\Services\Notifications\MailketingService;
use Illuminate\Console\Command;

/**
 * Reminder HARIAN sebelum jatuh tempo.
 *
 * Mencegah lebih murah daripada menghukum: mengingatkan pelanggan sebelum
 * jatuh tempo biasanya menurunkan jumlah isolir dan keluhan jauh lebih efektif
 * daripada isolir itu sendiri.
 *
 * Jadwal default: setiap hari mulai H-7 s/d H-1, ditambah H+1 (opsional follow-up).
 */
class SendPaymentReminders extends Command
{
    protected $signature = 'threfnet:send-reminders {--dry-run : Tampilkan target reminder tanpa mengirim notifikasi}';
    protected $description = 'THRE.F.NET: kirim pengingat harian H-7 sampai H-1 ke pelanggan';

    public function handle(): int
    {
        $total = 0;
        $dryRun = (bool) $this->option('dry-run');
        $today = today();

        $customers = Customer::query()
            ->with('plan')
            ->whereIn('status', ['active', 'new'])
            ->whereBetween('expired_date', [$today->copy()->addDay(), $today->copy()->addDays(7)])
            ->orderBy('expired_date')
            ->get();

        foreach ($customers as $customer) {
            $daysLeft = (int) $today->diffInDays($customer->expired_date, false);
            $context = 'reminder_h7_daily';

            if ($daysLeft < 1 || $daysLeft > 7 || $this->alreadySent($customer->id, $context)) {
                continue;
            }

            if ($dryRun) {
                $this->line("H-{$daysLeft}: {$customer->name} ({$customer->username})");
            } else {
                $this->dispatchReminder($customer, $daysLeft, $context);
            }

            $total++;
        }

        // Tetap pertahankan follow-up H+1 yang sudah ada.
        $overdueCustomers = Customer::query()
            ->with('plan')
            ->whereIn('status', ['active', 'new'])
            ->whereDate('expired_date', $today->copy()->subDay())
            ->orderBy('expired_date')
            ->get();

        foreach ($overdueCustomers as $customer) {
            if ($this->alreadySent($customer->id, 'reminder_lewat')) {
                continue;
            }

            if ($dryRun) {
                $this->line("Lewat tempo: {$customer->name} ({$customer->username})");
            } else {
                $this->dispatchOverdueReminder($customer);
            }

            $total++;
        }

        $this->info($dryRun ? "Target reminder ditemukan: {$total}" : "Reminder diproses: {$total}");

        return self::SUCCESS;
    }

    /** Cegah kirim ganda bila command dijalankan berulang di hari yang sama. */
    protected function alreadySent(int $customerId, string $context): bool
    {
        return NotificationLog::where('customer_id', $customerId)
            ->where('context', $context)
            ->where('status', 'sent')
            ->whereDate('created_at', today())
            ->exists();
    }

    protected function dispatchReminder(Customer $customer, int $daysLeft, string $context): void
    {
        $judul = "Pengingat Masa Aktif ({$daysLeft} hari lagi)";
        $wa = $this->renderReminderTemplate($customer, $daysLeft);

        $html = MailketingService::template($judul,
            '<p style="white-space:pre-line">' . e($wa) . '</p>'
        );

        SendWhatsAppNotification::dispatch($customer, $wa, $context);
        SendEmailNotification::dispatch($customer, "THRE.F.NET - {$judul}", $html, $context);
    }

    protected function dispatchOverdueReminder(Customer $customer): void
    {
        $tanggal = $customer->expired_date->translatedFormat('d F Y');
        $harga   = 'Rp ' . number_format($customer->plan->price, 0, ',', '.');
        $link    = config('app.url') . '/bayar/' . $customer->username;
        $judul   = 'Tagihan Lewat Jatuh Tempo';
        $wa = "[THRE.F.NET - Tagihan Lewat Jatuh Tempo]\n"
            . "Halo {$customer->name}, tagihan Anda jatuh tempo pada {$tanggal} "
            . "dan belum kami terima.\n"
            . "Tagihan: {$harga}\n"
            . "Segera bayar agar layanan tidak terisolasi: {$link}";

        $html = MailketingService::template($judul,
            '<p style="white-space:pre-line">' . e($wa) . '</p>'
        );

        SendWhatsAppNotification::dispatch($customer, $wa, 'reminder_lewat');
        SendEmailNotification::dispatch($customer, "THRE.F.NET - {$judul}", $html, 'reminder_lewat');
    }

    protected function renderReminderTemplate(Customer $customer, int $daysLeft): string
    {
        $template = Setting::get('reminder_h7_template', config('threfnet.reminders.h7_daily_template'));

        return strtr($template, [
            '{customer_name}' => $customer->name,
            '{plan_name}'     => $customer->plan?->name ?? '-',
            '{expired_date}'  => $customer->expired_date->translatedFormat('d F Y'),
            '{days_left}'     => (string) $daysLeft,
            '{amount}'        => 'Rp ' . number_format((float) ($customer->plan?->price ?? 0), 0, ',', '.'),
            '{payment_link}'  => config('app.url') . '/bayar/' . $customer->username,
            '{username}'      => $customer->username,
            '{company_name}'  => config('app.name', 'THRE.F.NET'),
        ]);
    }
}
