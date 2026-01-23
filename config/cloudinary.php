<?php

// Build Cloudinary URL from environment variables if not set
// Format: cloudinary://API_KEY:API_SECRET@CLOUD_NAME
$cloudinaryUrl = env('CLOUDINARY_URL');
if (empty($cloudinaryUrl)) {
    $cloudName = env('CLOUDINARY_CLOUD_NAME');
    $apiKey = env('CLOUDINARY_API_KEY') ?? env('CLOUDINARY_KEY');
    $apiSecret = env('CLOUDINARY_API_SECRET') ?? env('CLOUDINARY_SECRET');
    
    if (!empty($cloudName) && !empty($apiKey) && !empty($apiSecret)) {
        $cloudinaryUrl = "cloudinary://{$apiKey}:{$apiSecret}@{$cloudName}";
    }
}

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | An HTTP or HTTPS URL to notify your application (a webhook) when the process of uploads, deletes, and any API
    | that accepts notification_url has completed.
    |
    |
    */
    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Cloudinary settings. Cloudinary is a cloud hosted
    | media management service for all file uploads, storage, delivery and transformation needs.
    |
    | Note: For dynamic configuration from database, use the CloudinaryInfo trait in your services.
    | The cloud_url is built in format: cloudinary://API_KEY:API_SECRET@CLOUD_NAME
    |
    */
    'cloud_url' => $cloudinaryUrl,

    /**
     * Upload Preset From Cloudinary Dashboard
     */
    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),

    /**
     * Route to get cloud_image_url from Blade Upload Widget
     */
    'upload_route' => env('CLOUDINARY_UPLOAD_ROUTE'),

    /**
     * Controller action to get cloud_image_url from Blade Upload Widget
     */
    'upload_action' => env('CLOUDINARY_UPLOAD_ACTION'),
];