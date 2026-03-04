<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

/**
 * Translation Model
 * 
 * Represents a translation entry for a language.
 *
 * @property int $id
 * @property string $locale
 * @property string $group
 * @property string $key
 * @property string $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Language $language
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|Translation newModelQuery()
 * @method static Builder<static>|Translation newQuery()
 * @method static Builder<static>|Translation query()
 * @method static Builder<static>|Translation whereCreatedAt($value)
 * @method static Builder<static>|Translation whereGroup($value)
 * @method static Builder<static>|Translation whereId($value)
 * @method static Builder<static>|Translation whereKey($value)
 * @method static Builder<static>|Translation whereLocale($value)
 * @method static Builder<static>|Translation whereUpdatedAt($value)
 * @method static Builder<static>|Translation whereValue($value)
 * @mixin Eloquent
 */
class Translation extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'locale',
        'group',
        'key',
        'value',
    ];

    /**
     * Get translations by locale (cached).
     *
     * @return array<string, string>
     */
    public static function getTranslationsByLocale(string $locale): array
    {
        return Cache::rememberForever("translations_by_locale_{$locale}", function () use ($locale) {
            return self::where('locale', $locale)
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->group . '.' . $item->key => $item->value];
                })
                ->toArray();
        });
    }

    /**
     * Forget cached translations.
     */
    public static function forgetCachedTranslations(): void
    {
        Cache::forget('translations_by_locale');
        Cache::forget('languages_list');
    }

    /**
     * Get the language for this translation.
     *
     * @return BelongsTo<Language, self>
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'locale', 'language');
    }
}
