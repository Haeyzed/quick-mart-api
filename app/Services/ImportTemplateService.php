<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImportTemplateService
{
    private const TEMPLATE_PATH = 'imports/templates';

    /**
     * Download a CSV template by module name.
     *
     * Example:
     *  brand  -> brands-sample.csv
     *  product -> products-sample.csv
     */
    public function download(string $module): string
    {
        $fileName = "{$module}s-sample.csv";

        $path = app_path(self::TEMPLATE_PATH . '/' . $fileName);

        if (!File::exists($path)) {
            throw new \RuntimeException("Template {$fileName} not found.");
        }

        return $path;
    }
}
