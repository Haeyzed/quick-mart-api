<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\IdCardTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class IdCardTemplateService
 *
 * Handles all core business logic and database interactions for ID Card Templates.
 */
class IdCardTemplateService
{
    /**
     * Retrieve all ID Card templates.
     *
     * @return Collection
     */
    public function getAllTemplates(): Collection
    {
        return IdCardTemplate::latest()->get();
    }

    /**
     * Retrieve the currently active ID Card template.
     *
     * @return IdCardTemplate
     */
    public function getActiveTemplate(): IdCardTemplate
    {
        $template = IdCardTemplate::where('is_active', true)->first();

        if (!$template) {
            // Provide a default fallback if none exists
            $template = IdCardTemplate::create([
                'name' => 'System Default',
                'is_active' => true,
                'design_config' => [
                    'primary_color' => '#171f27',
                    'text_color' => '#ffffff',
                    'logo_url' => null,
                    'show_phone' => true,
                    'show_address' => true,
                    'show_qr_code' => true,
                ]
            ]);
        }

        return $template;
    }

    /**
     * Create a new ID Card Template.
     *
     * @param array $data
     * @return IdCardTemplate
     */
    public function createTemplate(array $data): IdCardTemplate
    {
        return DB::transaction(function () use ($data) {
            if (!empty($data['is_active'])) {
                $this->deactivateAllTemplates();
            }

            return IdCardTemplate::create($data);
        });
    }

    /**
     * Update an existing ID Card Template.
     *
     * @param IdCardTemplate $template
     * @param array $data
     * @return IdCardTemplate
     */
    public function updateTemplate(IdCardTemplate $template, array $data): IdCardTemplate
    {
        return DB::transaction(function () use ($template, $data) {
            if (!empty($data['is_active']) && !$template->is_active) {
                $this->deactivateAllTemplates();
            }

            $template->update($data);
            return $template;
        });
    }

    /**
     * Set a specific template as active and deactivate all others.
     *
     * @param IdCardTemplate $template
     * @return IdCardTemplate
     */
    public function markAsActive(IdCardTemplate $template): IdCardTemplate
    {
        return DB::transaction(function () use ($template) {
            $this->deactivateAllTemplates();
            $template->update(['is_active' => true]);
            return $template;
        });
    }

    /**
     * Deactivate all templates in the system.
     *
     * @return void
     */
    private function deactivateAllTemplates(): void
    {
        IdCardTemplate::query()->update(['is_active' => false]);
    }

    /**
     * Delete an ID card template.
     *
     * @param IdCardTemplate $template
     * @return void
     */
    public function deleteTemplate(IdCardTemplate $template): void
    {
        $template->delete();
    }

    /**
     * Bulk delete templates.
     *
     * @param array $ids
     * @return int Number of templates deleted
     */
    public function bulkDeleteTemplates(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            // Ensure we don't delete the active template during bulk delete
            $activeTemplateId = IdCardTemplate::where('is_active', true)->value('id');
            $idsToDelete = array_filter($ids, fn($id) => $id !== $activeTemplateId);

            return IdCardTemplate::whereIn('id', $idsToDelete)->delete();
        });
    }
}
