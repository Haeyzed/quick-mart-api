<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * InstallmentPlan Model
 *
 * Represents an installment payment plan for a sale or purchase.
 *
 * @property int $id
 * @property string $reference_type
 * @property int $reference_id
 * @property string $name
 * @property float $price
 * @property float $additional_amount
 * @property float $total_amount
 * @property float $down_payment
 * @property int $months
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model $reference
 * @property-read Collection<int, Installment> $installments
 */
class InstallmentPlan extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_type',
        'reference_id',
        'name',
        'price',
        'additional_amount',
        'total_amount',
        'down_payment',
        'months',
    ];

    /**
     * Get the parent reference model (Sale or Purchase).
     *
     * @return MorphTo<Model, self>
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the installments for this plan.
     *
     * @return HasMany<Installment>
     */
    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }

    /**
     * Calculate the monthly installment amount.
     */
    public function getMonthlyAmount(): float
    {
        if ($this->months <= 0) {
            return 0;
        }

        return ($this->total_amount - $this->down_payment) / $this->months;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'reference_id' => 'integer',
            'price' => 'float',
            'additional_amount' => 'float',
            'total_amount' => 'float',
            'down_payment' => 'float',
            'months' => 'integer',
        ];
    }
}
