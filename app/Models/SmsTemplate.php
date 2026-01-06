<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * SmsTemplate Model
 *
 * Represents an SMS message template.
 *
 * @property int $id
 * @property string $name
 * @property string $content
 * @property bool $is_default
 * @property bool $is_default_ecommerce
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|SmsTemplate default()
 * @method static Builder|SmsTemplate defaultEcommerce()
 */
class SmsTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'content',
        'is_default',
        'is_default_ecommerce',
    ];

    /**
     * Scope a query to only include default SMS templates.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to only include default ecommerce SMS templates.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDefaultEcommerce(Builder $query): Builder
    {
        return $query->where('is_default_ecommerce', true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_default_ecommerce' => 'boolean',
        ];
    }
}

