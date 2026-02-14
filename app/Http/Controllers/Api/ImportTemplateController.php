<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImportTemplateService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImportTemplateController extends Controller
{
    public function __construct(
        private readonly ImportTemplateService $service
    ) {}

    /**
     * Download module import sample template.
     */
    public function download(string $module): BinaryFileResponse|JsonResponse
    {
        if (auth()->user()->denies('import brands')) {
            return response()->forbidden('Permission denied for downloading import template.');
        }

        $path = $this->service->download($module);

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}
