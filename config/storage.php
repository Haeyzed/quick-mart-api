<?php

declare(strict_types=1);

/**
 * Storage Configuration
 *
 * Centralized configuration for all file upload paths and directories.
 * All services should use these paths instead of hard-coded values.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Product Storage Paths
    |--------------------------------------------------------------------------
    */
    'products' => [
        'images' => [
            'base' => 'images/product',
            'xlarge' => 'images/product/xlarge',
            'large' => 'images/product/large',
            'medium' => 'images/product/medium',
            'small' => 'images/product/small',
        ],
        'files' => 'product/files',
    ],

    /*
    |--------------------------------------------------------------------------
    | Category Storage Paths
    |--------------------------------------------------------------------------
    */
    'categories' => [
        'images' => 'images/category',
        'icons' => 'images/category/icon',
    ],

    /*
    |--------------------------------------------------------------------------
    | Brand Storage Paths
    |--------------------------------------------------------------------------
    */
    'brands' => [
        'images' => 'images/brand',
    ],

    /*
    |--------------------------------------------------------------------------
    | Biller Storage Paths
    |--------------------------------------------------------------------------
    */
    'billers' => [
        'images' => 'images/biller',
    ],

    'suppliers' => [
        'images' => 'images/supplier',
    ],

    'sale_agents' => [
        'images' => 'images/sale_agent',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Storage Paths
    |--------------------------------------------------------------------------
    */
    'users' => [
        'avatars' => 'images/user/avatar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Storage Paths
    |--------------------------------------------------------------------------
    */
    'exports' => [
        'base' => 'exports',
    ],
];
