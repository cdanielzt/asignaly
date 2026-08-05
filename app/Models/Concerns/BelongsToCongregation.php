<?php

namespace App\Models\Concerns;

use App\Models\Congregation;
use App\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCongregation
{
    protected static function bootBelongsToCongregation(): void
    {
        static::addGlobalScope('congregation', function (Builder $builder) {
            $congregationId = app(TenantContext::class)->get();
            if ($congregationId !== null) {
                $builder->where(
                    (new static)->getTable() . '.congregation_id',
                    $congregationId
                );
            }
        });

        static::creating(function ($model) {
            if (empty($model->congregation_id)) {
                $model->congregation_id = app(TenantContext::class)->get();
            }
        });
    }

    public function congregation(): BelongsTo
    {
        return $this->belongsTo(Congregation::class);
    }
}
