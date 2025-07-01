<?php

namespace KoalaPress\Model\Traits;

use Illuminate\Database\Eloquent\Builder;

trait DefaultOrderBy
{
    /**
     * The column to order by.
     *
     * @return void
     */
    protected static function bootDefaultOrderBy(): void
    {
        if (!property_exists(static::class, 'orderByColumn')) {
            return;
        }
        
        if (empty(static::$orderByColumn)) {
            return;
        }

        $column = static::$orderByColumn;

        $direction = static::$orderByColumnDirection;

        static::addGlobalScope('default_order_by', function (Builder $builder) use ($column, $direction) {
            $builder->orderBy($column, $direction);
        });
    }
}
