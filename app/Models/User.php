<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Model
 *
 * Represents a user in the system with authentication, roles, and permissions.
 *
 * @property int $id
 * @property string $name
 * @property string|null $username
 * @property string $email
 * @property string|null $avatar
 * @property string|null $avatar_url
 * @property string|null $phone
 * @property string|null $company_name
 * @property int $role_id
 * @property int|null $biller_id
 * @property int|null $warehouse_id
 * @property int|null $kitchen_id
 * @property bool|null $service_staff
 * @property bool $is_active
 * @property bool $is_deleted
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Biller|null $biller
 * @property-read Warehouse|null $warehouse
 * @property-read Collection<int, Holiday> $holidays
 * @property-read Collection<int, Sale> $sales
 * @property-read Collection<int, Purchase> $purchases
 * @property-read Collection<int, Payment> $payments
 *
 * @method static Builder|User active()
 * @method static Builder|User notDeleted()
 */
class User extends Authenticatable implements AuditableContract, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use Auditable;

    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'avatar',
        'avatar_url',
        'password',
        'phone',
        'company_name',
        'role_id',
        'biller_id',
        'warehouse_id',
        'kitchen_id',
        'service_staff',
        'is_active',
        'is_deleted',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the biller associated with the user.
     *
     * @return BelongsTo<Biller, self>
     */
    public function biller(): BelongsTo
    {
        return $this->belongsTo(Biller::class);
    }

    /**
     * Get the warehouse associated with the user.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the holidays for the user.
     *
     * @return HasMany<Holiday>
     */
    public function holidays(): HasMany
    {
        return $this->hasMany(Holiday::class);
    }

    /**
     * Get the sales created by the user.
     *
     * @return HasMany<Sale>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get the purchases created by the user.
     *
     * @return HasMany<Purchase>
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get the payments made by the user.
     *
     * @return HasMany<Payment>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if the user is active.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Check if the user is deleted.
     */
    public function isDeleted(): bool
    {
        return $this->is_deleted === true;
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include non-deleted users.
     */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where('is_deleted', false)->orWhereNull('is_deleted');
    }

    /**
     * Check if the user has a specific permission.
     * This is a convenience wrapper around Spatie's hasPermissionTo method.
     *
     * @param  string  $permission  Permission name
     */
    public function canPerform(string $permission): bool
    {
        return $this->hasPermissionTo($permission);
    }

    /**
     * Check if the user has any of the given permissions.
     *
     * @param  array<string>  $permissions  Array of permission names
     */
    public function canPerformAny(array $permissions): bool
    {
        return $this->hasAnyPermission($permissions);
    }

    /**
     * Check if the user has all of the given permissions.
     *
     * @param  array<string>  $permissions  Array of permission names
     */
    public function canPerformAll(array $permissions): bool
    {
        return $this->hasAllPermissions($permissions);
    }

    /**
     * Get all permissions for this user (from roles + direct permissions).
     *
     * @return SupportCollection<int, \Spatie\Permission\Models\Permission>
     */
    public function getAllUserPermissions(): SupportCollection
    {
        return collect($this->getAllPermissions());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_deleted' => 'boolean',
            'service_staff' => 'boolean',
            'role_id' => 'integer',
            'biller_id' => 'integer',
            'warehouse_id' => 'integer',
            'kitchen_id' => 'integer',
        ];
    }
}
