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
    | Export Storage Paths
    |--------------------------------------------------------------------------
    */
    'exports' => [
        'base' => 'exports',
    ],
];
