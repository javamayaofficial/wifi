<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * Import pelanggan dari Excel/CSV.
 *
 * Importer ini mencoba mendeteksi berbagai variasi header secara otomatis:
 * - nama / customer_name / full name
 * - username / user / pppoe / mikrotik id
 * - paket / plan / profile
 * - router / server / nas
 * - jatuh_tempo / expire / expired_date
 * dan beberapa alias umum lainnya.
 */
class CustomersImport implements ToCollection
{
    public int $successCount = 0;
    public array $errors = [];
    public array $detectedColumns = [];

    protected const POSITIONAL_FIELDS = [
        0 => 'name',
        1 => 'username',
        2 => 'password',
        3 => 'plan',
        4 => 'router',
        5 => 'expired_date',
        6 => 'status',
        7 => 'phone',
        8 => 'email',
        9 => 'address',
        10 => 'national_id_number',
        11 => 'latitude',
        12 => 'longitude',
    ];

    protected const HEADER_ALIASES = [
        'name' => ['name', 'nama', 'nama_pelanggan', 'namapelanggan', 'nama_customer', 'customer_name', 'customername', 'full_name', 'fullname'],
        'username' => ['username', 'user', 'userid', 'login', 'login_id', 'pppoe', 'pppoe_user', 'pppoe_username', 'pppoe_id', 'mikrotik_id', 'mikrotikid', 'secret', 'id_pelanggan_mikrotik', 'id_mikrotik'],
        'password' => ['password', 'pass', 'passwd', 'sandi', 'kata_sandi', 'pppoe_password', 'pppoepassword'],
        'plan' => ['plan', 'paket', 'package', 'plan_name', 'planname', 'nama_paket', 'paket_internet', 'paketinternet', 'profile', 'service_plan'],
        'router' => ['router', 'router_name', 'routername', 'server', 'nas', 'device', 'olt', 'router_ip', 'router_host', 'host_router'],
        'expired_date' => ['expired_date', 'expire_date', 'expire', 'expired', 'expiry_date', 'duedate', 'due_date', 'jatuh_tempo', 'jatuhtempo', 'tgl_jatuh_tempo', 'masa_aktif', 'masaaktif', 'berlaku_sampai'],
        'status' => ['status', 'state', 'service_status', 'status_layanan'],
        'phone' => ['phone', 'phone_number', 'no_hp', 'nomor_hp', 'hp', 'no_telp', 'telepon', 'mobile', 'whatsapp', 'wa', 'nomor_wa', 'nomor_whatsapp'],
        'email' => ['email', 'e_mail', 'mail'],
        'address' => ['address', 'alamat', 'alamat_pelanggan', 'alamat_pasang', 'alamat_lengkap', 'lokasi', 'installation_address'],
        'national_id_number' => ['national_id_number', 'ktp', 'nik', 'no_ktp', 'nomor_ktp', 'id_card', 'identity_number'],
        'latitude' => ['latitude', 'lat', 'koordinat_lat', 'coord_lat'],
        'longitude' => ['longitude', 'lng', 'lon', 'long', 'koordinat_lng', 'coord_lng'],
    ];

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $plans = Plan::query()->get()->mapWithKeys(function (Plan $plan) {
            return [
                $this->normalizeKey($plan->name) => $plan->id,
                $this->normalizeKey($plan->mikrotik_profile) => $plan->id,
                (string) $plan->id => $plan->id,
            ];
        });

        $routers = Router::query()->get()->mapWithKeys(function (Router $router) {
            return [
                $this->normalizeKey($router->name) => $router->id,
                $this->normalizeKey($router->ip) => $router->id,
                (string) $router->id => $router->id,
            ];
        });

        $headerRow = $rows->first();
        $hasHeaderRow = $this->looksLikeHeaderRow($headerRow);
        $mapping = $hasHeaderRow
            ? $this->detectHeaderMapping($headerRow)
            : self::POSITIONAL_FIELDS;

        $this->detectedColumns = $mapping;
        $startIndex = $hasHeaderRow ? 1 : 0;

        foreach ($rows->slice($startIndex)->values() as $index => $row) {
            $line = $index + $startIndex + 1;
            $rawRow = $this->extractRowData($row, $mapping);

            if ($this->rowIsEmpty($rawRow)) {
                continue;
            }

            $planId = $this->resolveLookup($plans, $rawRow['plan'] ?? null);
            $routerId = $this->resolveLookup($routers, $rawRow['router'] ?? null);

            $data = [
                'name' => $this->cleanString($rawRow['name'] ?? ''),
                'username' => $this->cleanString($rawRow['username'] ?? ''),
                'password' => (string) ($rawRow['password'] ?? ''),
                'plan_id' => $planId,
                'router_id' => $routerId,
                'expired_date' => $this->parseDate($rawRow['expired_date'] ?? null),
                'status' => $this->normalizeStatus($rawRow['status'] ?? null),
                'phone' => $this->cleanString($rawRow['phone'] ?? null),
                'email' => $this->cleanString($rawRow['email'] ?? null),
                'address' => $this->cleanString($rawRow['address'] ?? null),
                'national_id_number' => $this->normalizeDigits($rawRow['national_id_number'] ?? null),
                'latitude' => $this->parseCoordinate($rawRow['latitude'] ?? null),
                'longitude' => $this->parseCoordinate($rawRow['longitude'] ?? null),
            ];

            if (blank($data['password']) && filled($data['username'])) {
                $data['password'] = 'thre' . random_int(100000, 999999);
            }

            $validator = Validator::make($data, [
                'name'         => ['required', 'string', 'max:150'],
                'username'     => ['required', 'string', 'max:100', 'unique:thre_customers,username'],
                'password'     => ['required', 'string', 'max:100'],
                'plan_id'      => ['required', 'exists:thre_plans,id'],
                'router_id'    => ['required', 'exists:thre_routers,id'],
                'expired_date' => ['required', 'date'],
                'status'       => ['required', 'in:new,active,isolated,suspended'],
                'phone'        => ['nullable', 'string', 'max:20'],
                'email'        => ['nullable', 'email', 'max:150'],
                'address'      => ['nullable', 'string'],
                'national_id_number' => ['nullable', 'digits_between:12,20'],
                'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
                'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
            ]);

            if ($validator->fails()) {
                $this->errors[] = "Baris {$line}: " . implode(' ', $validator->errors()->all());
                continue;
            }

            Customer::create($data);
            $this->successCount++;
        }
    }

    protected function detectHeaderMapping($row): array
    {
        $mapping = [];

        foreach ($this->rowToArray($row) as $index => $value) {
            $normalized = $this->normalizeKey($value);

            if ($normalized === '') {
                continue;
            }

            foreach (self::HEADER_ALIASES as $field => $aliases) {
                if (in_array($normalized, $aliases, true) && ! in_array($field, $mapping, true)) {
                    $mapping[$index] = $field;
                    break;
                }
            }
        }

        return $mapping;
    }

    protected function looksLikeHeaderRow($row): bool
    {
        return count($this->detectHeaderMapping($row)) >= 2;
    }

    protected function extractRowData($row, array $mapping): array
    {
        $data = [];

        foreach ($this->rowToArray($row) as $index => $value) {
            $field = $mapping[$index] ?? null;

            if (! $field) {
                continue;
            }

            $data[$field] = $value;
        }

        return $data;
    }

    protected function rowToArray($row): array
    {
        if ($row instanceof Collection) {
            return array_values($row->toArray());
        }

        return array_values((array) $row);
    }

    protected function rowIsEmpty(array $row): bool
    {
        return collect($row)
            ->filter(fn ($value) => filled($this->cleanString($value)))
            ->isEmpty();
    }

    protected function resolveLookup(Collection $items, mixed $value): ?int
    {
        $normalized = $this->normalizeKey($value);

        if ($normalized === '') {
            return null;
        }

        return $items->get($normalized);
    }

    protected function cleanString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    protected function normalizeKey(mixed $value): string
    {
        $value = Str::of((string) $value)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->value();

        return $value;
    }

    protected function normalizeDigits(mixed $value): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits !== '' ? $digits : null;
    }

    protected function normalizeStatus(mixed $value): string
    {
        $normalized = $this->normalizeKey($value);

        return match ($normalized) {
            '', 'aktif', 'active', 'enabled', 'enable' => 'active',
            'isolated', 'isolir', 'disabled', 'disable', 'blocked' => 'isolated',
            'suspended', 'suspend', 'nonaktif', 'non_active' => 'suspended',
            'baru', 'new', 'pending' => 'new',
            default => 'new',
        };
    }

    protected function parseCoordinate(mixed $value): ?float
    {
        if (blank($value)) {
            return null;
        }

        $value = str_replace(',', '.', trim((string) $value));

        return is_numeric($value) ? (float) $value : null;
    }

    protected function parseDate($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        // Excel menyimpan tanggal sebagai serial number.
        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
