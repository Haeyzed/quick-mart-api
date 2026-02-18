<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

/**
 * Excel/CSV import for Supplier entities.
 * Uses country_id, state_id, city_id like Customer. Accepts IDs in CSV or resolves from country/state/city names.
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

        $countryId = isset($data['country_id']) && (string) $data['country_id'] !== ''
            ? (int) $data['country_id']
            : null;
        $stateId = isset($data['state_id']) && (string) $data['state_id'] !== ''
            ? (int) $data['state_id']
            : null;
        $cityId = isset($data['city_id']) && (string) $data['city_id'] !== ''
            ? (int) $data['city_id']
            : null;

        if ($countryId === null || $stateId === null || $cityId === null) {
            $countryName = trim((string) ($data['country'] ?? ''));
            $stateName = trim((string) ($data['state'] ?? ''));
            $cityName = trim((string) ($data['city'] ?? ''));
            if ($countryId === null && $countryName !== '') {
                $countryId = Country::query()->where('name', $countryName)->value('id');
            }
            if ($stateId === null && $stateName !== '' && $countryId !== null) {
                $stateId = State::query()
                    ->where('country_id', $countryId)
                    ->where('name', $stateName)
                    ->value('id');
            }
            if ($cityId === null && $cityName !== '' && $stateId !== null) {
                $cityId = City::query()
                    ->where('state_id', $stateId)
                    ->where('name', $cityName)
                    ->value('id');
            }
        }

        $attributes = [
            'company_name' => $companyName,
            'name' => trim((string) ($data['name'] ?? '')) ?: $companyName,
            'vat_number' => trim((string) ($data['vat_number'] ?? $data['vatnumber'] ?? '')) ?: null,
            'email' => trim((string) ($data['email'] ?? '')) ?: null,
            'phone_number' => trim((string) ($data['phone_number'] ?? $data['phonenumber'] ?? '')) ?: null,
            'address' => trim((string) ($data['address'] ?? '')) ?: null,
            'country_id' => $countryId,
            'state_id' => $stateId,
            'city_id' => $cityId,
            'postal_code' => trim((string) ($data['postal_code'] ?? $data['postalcode'] ?? '')) ?: null,
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
