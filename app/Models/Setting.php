<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $table = 'thre_settings';

    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    /** Key yang nilainya sensitif → dienkripsi saat disimpan. */
    protected static array $sensitiveKeys = [
        'doku_secret_key', 'moota_secret_token',
        'mailketing_api_token', 'whatsapp_api_key',
    ];

    public static function get(string $key, $default = null)
    {
        $row = static::query()->where('key', $key)->first();
        if (! $row) {
            return $default;
        }
        $value = $row->value;
        if (in_array($key, static::$sensitiveKeys, true) && $value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Throwable $e) {
                return $value; // fallback bila belum terenkripsi
            }
        }
        return $value;
    }

    public static function put(string $key, $value): void
    {
        if (in_array($key, static::$sensitiveKeys, true) && $value) {
            $value = Crypt::encryptString($value);
        }
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
