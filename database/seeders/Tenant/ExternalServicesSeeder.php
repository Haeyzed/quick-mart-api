<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * External Services Seeder
 *
 * Seeds payment gateway and SMS provider configurations.
 * Updates existing services with new module statuses and adds missing gateways.
 *
 * @package Database\Seeders\Tenant
 */
class ExternalServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->updateModuleStatuses();
        $this->seedPaymentGateways();
    }

    /**
     * Update module statuses for existing external services.
     *
     * @return void
     */
    private function updateModuleStatuses(): void
    {
        $newAddons = ['ecommerce']; // Future add-ons

        $gateways = DB::table('external_services')->get();

        foreach ($gateways as $gateway) {
            $moduleStatus = json_decode($gateway->module_status ?? '{}', true) ?: [];

            // Ensure all add-ons are present in module_status with a default of false
            foreach ($newAddons as $addon) {
                if (!isset($moduleStatus[$addon])) {
                    $moduleStatus[$addon] = false;
                }
            }

            // Update the row
            DB::table('external_services')
                ->where('id', $gateway->id)
                ->update(['module_status' => json_encode($moduleStatus)]);
        }
    }

    /**
     * Seed payment gateways if they don't exist.
     *
     * @return void
     */
    private function seedPaymentGateways(): void
    {
        $gateways = [
            [
                'name' => 'PayPal',
                'type' => 'payment',
                'details' => 'Client ID,Client Secret;abcd1234,wxyz5678',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
            ],
            [
                'name' => 'Stripe',
                'type' => 'payment',
                'details' => 'Public Key,Private Key;efgh1234,stuv5678',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
            ],
            [
                'name' => 'Razorpay',
                'type' => 'payment',
                'details' => 'Key,Secret;rzp_test_Y4MCcpHfZNU6rR,3Hr7SDqaZ0G5waN0jsLgsiLx',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
            ],
            [
                'name' => 'Paystack',
                'type' => 'payment',
                'details' => 'public_Key,Secret_Key;pk_test_e8d220b7463d64569f0053e78534f38e6b10cf4a,sk_test_6d62cb976e1e0ab43f1e48b2934b0dfc7f32a1fe',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
            ],
            [
                'name' => 'Mollie',
                'type' => 'payment',
                'details' => 'api_key;test_dHar4XY7LxsDOtmnkVtjNVWXLSlXsM',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
            ],
            [
                'name' => 'Xendit',
                'type' => 'payment',
                'details' => 'secret_key,callback_token;xnd_development_aKJVKYbc4lHkEjcCLzWLrBsKs6jF6nbM6WaCMfnJerP3JW57CLis553XNRdDU,YPZxND92Mt8tdXntTYIEkRX802onZ5OcdKBUzycebuqYvN4n',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
            ],
            [
                'name' => 'bkash',
                'type' => 'payment',
                'details' => 'Mode,app_key,app_secret,username,password;sandbox,0vWQuCRGiUX7EPVjQDr0EUAYtc,jcUNPBgbcqEDedNKdvE4G1cAK7D3hCjmJccNPZZBq96QIxxwAMEx,01770618567,D7DaC<*E*eG',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
            ],
            [
                'name' => 'sslcommerz',
                'type' => 'payment',
                'details' => 'appkey,appsecret;12341234,asdfa23423',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
            ],
            [
                'name' => 'Mpesa',
                'type' => 'payment',
                'details' => 'consumer_Key,consumer_Secret;fhfgkj,dtrddhd',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
            ],
            [
                'name' => 'Pesapal',
                'type' => 'payment',
                'details' => 'Mode,Consumer Key,Consumer Secret;sandbox,qkio1BGGYAXTu2JOfm7XSXNruoZsrqEW,osGQ364R49cXKeOYSpaOnT++rHs=',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
            ],
            [
                'name' => 'Moneipoint',
                'type' => 'payment',
                'details' => 'Mode,client_id,client_secret,terminal_serial;sandbox,api-client-3956952-7e1279e2-95d2-45e1-825a-3a28e0a35168,ZtH02Q%jQ$Imcf%W^B%q,C42P008D01909830',
                'module_status' => json_encode(['ecommerce' => true, 'pos' => true]),
                'active' => 1,
            ],
        ];

        foreach ($gateways as $gateway) {
            $exists = DB::table('external_services')
                ->where('name', $gateway['name'])
                ->exists();

            if (!$exists) {
                DB::table('external_services')->insert(array_merge($gateway, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}




