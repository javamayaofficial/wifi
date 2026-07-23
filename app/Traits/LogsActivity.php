<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Mencatat siapa mengubah apa. Dipasang di model yang datanya sensitif
 * (pelanggan, transaksi, paket, router, pengaturan).
 *
 * Nilai sensitif (password, token, secret) TIDAK PERNAH ikut tercatat.
 */
trait LogsActivity
{
    protected static array $auditRedacted = [
        'password', 'value', 'secret', 'token', 'api_key',
    ];

    public static function bootLogsActivity(): void
    {
        static::created(fn (Model $m) => static::writeAudit($m, 'created', $m->getAttributes()));

        static::updated(function (Model $m) {
            $changes = [];

            foreach ($m->getChanges() as $key => $new) {
                if ($key === 'updated_at') {
                    continue;
                }

                $changes[$key] = [
                    'dari' => static::redact($key, $m->getOriginal($key)),
                    'ke'   => static::redact($key, $new),
                ];
            }

            if ($changes) {
                static::writeAudit($m, 'updated', $changes);
            }
        });

        static::deleted(fn (Model $m) => static::writeAudit($m, 'deleted', []));
    }

    protected static function writeAudit(Model $model, string $event, array $changes): void
    {
        $clean = [];

        foreach ($changes as $key => $val) {
            $clean[$key] = is_array($val) ? $val : static::redact($key, $val);
        }

        AuditLog::create([
            'user_id'    => Auth::id(),
            'user_name'  => Auth::user()?->name ?? 'sistem',
            'event'      => $event,
            'model_type' => class_basename($model),
            'model_id'   => $model->getKey(),
            'label'      => method_exists($model, 'auditLabel')
                ? $model->auditLabel()
                : (string) ($model->name ?? $model->getKey()),
            'changes'    => $clean,
            'ip'         => request()->ip(),
        ]);
    }

    protected static function redact(string $key, $value)
    {
        foreach (static::$auditRedacted as $needle) {
            if (str_contains(strtolower($key), $needle)) {
                return '••••';
            }
        }

        return is_scalar($value) || $value === null ? $value : '[data]';
    }
}
