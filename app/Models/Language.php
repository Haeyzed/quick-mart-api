<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Language Model
 *
 * Represents a language configuration for the application.
 *
 * @property int $id
 * @property string $language
 * @property string $name
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Translation> $translations
 *
 * @method static Builder|Language default()
 */
class Language extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'language',
        'name',
        'is_default',
    ];

    /**
     * Get the default language (cached).
     */
    public static function getDefaultLanguage(): ?self
    {
        return Cache::rememberForever('default_language', function () {
            return self::where('is_default', true)->first();
        });
    }

    /**
     * Forget cached language data.
     */
    public static function forgetCachedLanguage(): void
    {
        Cache::forget('default_language');
        Cache::forget('languages_list');
    }

    /**
     * Set the default language and update cache.
     */
    public static function setDefaultLanguage(int $id): self
    {
        self::query()->update(['is_default' => false]);

        $language = self::findOrFail($id);
        $language->is_default = true;
        $language->save();

        setcookie('language', $language->language, time() + (86400 * 365), '/');
        Cache::forever('default_language', $language);

        session(['locale' => $language->language]);
        app()->setLocale($language->language);
        config(['app.locale' => $language->language]);

        Artisan::call('cache:clear');
        Artisan::call('config:clear');

        return $language;
    }

    /**
     * Get the translations for this language.
     *
     * @return HasMany<Translation>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class, 'locale', 'language');
    }

    /**
     * Scope a query to only include default language.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
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
        ];
    }
}
