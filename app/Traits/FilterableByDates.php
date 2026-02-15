<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Trait FilterableByDates
 * * Provides reusable Eloquent scopes for common date-based filtering.
 */
trait FilterableByDates
{
    /**
     * Scope a query to only include records from today.
     *
     * @param Builder $query
     * @param string $column
     * @return Builder
     */
    public function scopeToday(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->whereDate($column, Carbon::today());
    }

    /**
     * Scope a query to only include records from yesterday.
     *
     * @param Builder $query
     * @param string $column
     * @return Builder
     */
    public function scopeYesterday(Builder $query, string $column = 'current_at'): Builder
    {
        return $query->whereDate($column, Carbon::yesterday());
    }

    /**
     * Scope a query to records from the start of the current month until now.
     *
     * @param Builder $query
     * @param string $column
     * @return Builder
     */
    public function scopeMonthToDate(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->whereBetween($column, [Carbon::now()->startOfMonth(), Carbon::now()]);
    }

    /**
     * Scope a query to records from the start of the current quarter until now.
     *
     * @param Builder $query
     * @param string $column
     * @return Builder
     */
    public function scopeQuarterToDate(Builder $query, string $column = 'created_at'): Builder
    {
        $now = Carbon::now();
        return $query->whereBetween($column, [$now->startOfQuarter(), $now]);
    }

    /**
     * Scope a query to records from the start of the current year until now.
     *
     * @param Builder $query
     * @param string $column
     * @return Builder
     */
    public function scopeYearToDate(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->whereBetween($column, [Carbon::now()->startOfYear(), Carbon::now()]);
    }

    /**
     * Scope a query to records from the last 7 days.
     *
     * @param Builder $query
     * @param string $column
     * @return Builder
     */
    public function scopeLast7Days(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->whereBetween($column, [Carbon::today()->subDays(6), Carbon::now()]);
    }

    /**
     * Scope a query to records from the last 30 days.
     *
     * @param Builder $query
     * @param string $column
     * @return Builder
     */
    public function scopeLast30Days(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->whereBetween($column, [Carbon::today()->subDays(29), Carbon::now()]);
    }

    /**
     * Scope a query to records from the previous quarter.
     *
     * @param Builder $query
     * @param string $column
     * @return Builder
     */
    public function scopeLastQuarter(Builder $query, string $column = 'created_at'): Builder
    {
        $now = Carbon::now();
        return $query->whereBetween($column, [
            $now->copy()->startOfQuarter()->subMonths(3),
            $now->copy()->startOfQuarter()
        ]);
    }

    /**
     * Scope a query to records from the last 365 days.
     *
     * @param Builder $query
     * @param string $column
     * @return Builder
     */
    public function scopeLastYear(Builder $query, string $column = 'created_at'): Builder
    {
        return $query->whereBetween($column, [Carbon::now()->subYear(), Carbon::now()]);
    }

    /**
     * Scope a query to a custom date range.
     *
     * @param Builder $query
     * @param string|Carbon|null $startDate
     * @param string|Carbon|null $endDate
     * @param string $column
     * @return Builder
     */
    public function scopeCustomRange(Builder $query, $startDate = null, $endDate = null, string $column = 'created_at'): Builder
    {
        return $query
            ->when($startDate, function (Builder $q) use ($startDate, $column) {
                $q->where($column, '>=', Carbon::parse($startDate)->startOfDay());
            })
            ->when($endDate, function (Builder $q) use ($endDate, $column) {
                $q->where($column, '<=', Carbon::parse($endDate)->endOfDay());
            });
    }
}
