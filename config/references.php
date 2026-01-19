<?php

declare(strict_types=1);

/**
 * Reference Number Configuration
 *
 * Centralized configuration for all reference number prefixes used across the application.
 * Reference numbers are generated in the format: {prefix}-{YYYYMMDD}-{HHMMSS}
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Purchase Reference Prefixes
    |--------------------------------------------------------------------------
    */
    'purchase' => [
        'prefix' => 'pr', // Purchase reference: pr-YYYYMMDD-HHMMSS
    ],

    /*
    |--------------------------------------------------------------------------
    | Sale Reference Prefixes
    |--------------------------------------------------------------------------
    */
    'sale' => [
        'prefix' => 'sr', // Sale reference: sr-YYYYMMDD-HHMMSS
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Reference Prefixes
    |--------------------------------------------------------------------------
    */
    'payment' => [
        'purchase' => 'ppr', // Purchase payment reference: ppr-YYYYMMDD-HHMMSS
        'sale' => 'psr',     // Sale payment reference: psr-YYYYMMDD-HHMMSS
    ],

    /*
    |--------------------------------------------------------------------------
    | Return Reference Prefixes
    |--------------------------------------------------------------------------
    */
    'return' => [
        'purchase' => 'rrp', // Purchase return reference: rrp-YYYYMMDD-HHMMSS
        'sale' => 'rrs',     // Sale return reference: rrs-YYYYMMDD-HHMMSS
    ],

    /*
    |--------------------------------------------------------------------------
    | Adjustment Reference Prefixes
    |--------------------------------------------------------------------------
    */
    'adjustment' => [
        'prefix' => 'adj', // Adjustment reference: adj-YYYYMMDD-HHMMSS
    ],

    /*
    |--------------------------------------------------------------------------
    | Transfer Reference Prefixes
    |--------------------------------------------------------------------------
    */
    'transfer' => [
        'prefix' => 'tr', // Transfer reference: tr-YYYYMMDD-HHMMSS
    ],
];
