<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id',
        'document_type_id',
        'name',
        'file_path',
        'file_url',
        'issue_date',
        'expiry_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}
