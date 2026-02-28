<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'requires_expiry',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'requires_expiry' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function employeeDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class, 'document_type_id');
    }
}
