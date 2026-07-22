<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Import pelanggan dari Excel/CSV.
 *
 * Header kolom yang diharapkan (baris pertama):
 * name | username | password | plan | router | expired_date | status | phone | email
 *
 * Kolom "plan" & "router" diisi NAMA paket/router (bukan id) agar mudah diisi admin.
 * Baris yang gagal divalidasi dilewati dan dilaporkan, baris valid tetap tersimpan.
 */
class CustomersImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;
    public array $errors = [];

    public function collection(Collection $rows): void
    {
        $plans   = Plan::pluck('id', 'name');
        $routers = Router::pluck('id', 'name');

        foreach ($rows as $index => $row) {
            $line = $index + 2; // +1 header, +1 basis 1

            $planId   = $plans[$row['plan'] ?? ''] ?? null;
            $routerId = $routers[$row['router'] ?? ''] ?? null;

            $data = [
                'name'         => trim((string) ($row['name'] ?? '')),
                'username'     => trim((string) ($row['username'] ?? '')),
                'password'     => (string) ($row['password'] ?? ''),
                'plan_id'      => $planId,
                'router_id'    => $routerId,
                'expired_date' => $this->parseDate($row['expired_date'] ?? null),
                'status'       => (string) ($row['status'] ?? 'new'),
                'phone'        => $row['phone'] ?? null,
                'email'        => $row['email'] ?? null,
            ];

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
            ]);

            if ($validator->fails()) {
                $this->errors[] = "Baris {$line}: " . implode(' ', $validator->errors()->all());
                continue;
            }

            Customer::create($data);
            $this->successCount++;
        }
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
