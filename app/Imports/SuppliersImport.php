<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

/**
 * Excel/CSV import for Supplier entities.
 *
 * Old app CSV: companyname, name, image, vatnumber, email, phonenumber, address, city, state, postalcode, country.
 */
class SuppliersImport implements OnEachRow, SkipsEmptyRows, WithHeadingRow, WithValidation
{
    public function onRow(Row $row): void
    {
        $data = $row->toArray();

        $companyName = trim((string) ($data['company_name'] ?? $data['companyname'] ?? ''));
        if ($companyName === '') {
            return;
        }

        $attributes = [
            'company_name' => $companyName,
            'name' => trim((string) ($data['name'] ?? '')) ?: $companyName,
            'vat_number' => trim((string) ($data['vat_number'] ?? $data['vatnumber'] ?? '')) ?: null,
            'email' => trim((string) ($data['email'] ?? '')) ?: null,
            'phone_number' => trim((string) ($data['phone_number'] ?? $data['phonenumber'] ?? '')) ?: null,
            'address' => trim((string) ($data['address'] ?? '')) ?: null,
            'city' => trim((string) ($data['city'] ?? '')) ?: null,
            'state' => trim((string) ($data['state'] ?? '')) ?: null,
            'postal_code' => trim((string) ($data['postal_code'] ?? $data['postalcode'] ?? '')) ?: null,
            'country' => trim((string) ($data['country'] ?? '')) ?: null,
            'is_active' => true,
        ];

        Supplier::updateOrCreate(
            ['company_name' => $companyName],
            $attributes
        );
    }

    public function rules(): array
    {
        return [
            'company_name' => ['nullable', 'string', 'max:255'],
            'companyname' => ['nullable', 'string', 'max:255'],
        ];
    }
}
