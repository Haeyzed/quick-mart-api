<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * InvoiceSchema Model
 *
 * Represents invoice numbering schema configuration.
 *
 * @property int $id
 * @property string $prefix
 * @property int $number_of_digit
 * @property int $start_number
 * @property int|null $last_invoice_number
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class InvoiceSchema extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_schemas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prefix',
        'number_of_digit',
        'start_number',
        'last_invoice_number',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'number_of_digit' => 'integer',
            'start_number' => 'integer',
            'last_invoice_number' => 'integer',
        ];
    }
}
