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
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
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
 * @method static Builder|User active()
 * @method static Builder|User notDeleted()
 * @method static Builder|User filter(array $filters)
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $holidays_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read int|null $payments_count
 * @property-read Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read int|null $purchases_count
 * @property-read Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read int|null $sales_count
 * @property-read Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User permission($permissions, $without = false)
 * @method static Builder<static>|User query()
 * @method static Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static Builder<static>|User whereAvatar($value)
 * @method static Builder<static>|User whereAvatarUrl($value)
 * @method static Builder<static>|User whereBillerId($value)
 * @method static Builder<static>|User whereCompanyName($value)
 * @method static Builder<static>|User whereCreatedAt($value)
 * @method static Builder<static>|User whereEmail($value)
 * @method static Builder<static>|User whereEmailVerifiedAt($value)
 * @method static Builder<static>|User whereId($value)
 * @method static Builder<static>|User whereIsActive($value)
 * @method static Builder<static>|User whereIsDeleted($value)
 * @method static Builder<static>|User whereKitchenId($value)
 * @method static Builder<static>|User whereName($value)
 * @method static Builder<static>|User wherePassword($value)
 * @method static Builder<static>|User wherePhone($value)
 * @method static Builder<static>|User whereRememberToken($value)
 * @method static Builder<static>|User whereServiceStaff($value)
 * @method static Builder<static>|User whereUpdatedAt($value)
 * @method static Builder<static>|User whereUsername($value)
 * @method static Builder<static>|User whereWarehouseId($value)
 * @method static Builder<static>|User withoutPermission($permissions)
 * @method static Builder<static>|User withoutRole($roles, $guard = null)
 * @mixin \Eloquent
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
        'image',
        'image_url',
        'password',
        'phone_number',
        'company_name',
        'biller_id',
        'warehouse_id',
        'kitchen_id',
        'service_staff',
        'is_active',
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
     * Check if the user is "staff" (limited access: not having a full-access role).
     * Used for restricting audits, incomes, etc. to own records.
     *
     * @return bool True when user does not have any of the full-access roles
     */
    public function isStaff(): bool
    {
        $fullAccessRoles = config('permission.full_access_roles', ['Admin', 'Owner']);
        if (empty($fullAccessRoles)) {
            return false;
        }

        return ! $this->hasAnyRole($fullAccessRoles);
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
     * Scope a query to apply filters (search, is_active). Same pattern as Customer::scopeFilter.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['is_active']),
                fn (Builder $q) => $q->where('is_active', (bool) $filters['is_active'])
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = '%'.$filters['search'].'%';
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('username', 'like', $term)
                    );
                }
            );
    }

    /**
     * Check if the user has a specific permission.
     * This is a convenience wrapper around Spatie's hasPermissionTo method.
     *
     * @param string $permission Permission name
     */
    public function canPerform(string $permission): bool
    {
        return $this->hasPermissionTo($permission);
    }

    /**
     * Check if the user denies (lacks) a specific permission.
     * Treats non-existent permissions as denied (avoids 500 when permission name is invalid).
     *
     * @param string $permission Permission name
     */
    public function denies(string $permission): bool
    {
        try {
            return !$this->hasPermissionTo($permission);
        } catch (PermissionDoesNotExist) {
            return true;
        }
    }

    /**
     * Check if the user has any of the given permissions.
     *
     * @param array<string> $permissions Array of permission names
     */
    public function canPerformAny(array $permissions): bool
    {
        return $this->hasAnyPermission($permissions);
    }

    /**
     * Check if the user has all of the given permissions.
     *
     * @param array<string> $permissions Array of permission names
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
            'biller_id' => 'integer',
            'warehouse_id' => 'integer',
            'kitchen_id' => 'integer',
        ];
    }
}
