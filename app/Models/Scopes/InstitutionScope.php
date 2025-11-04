<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class InstitutionScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        // Skip if no authenticated user
        if (! $user) {
            return;
        }

        // Admin can see all institutions
        if ($user->isAdmin()) {
            return;
        }

        // Clients only see their institution's data
        if ($user->institution_id) {
            $builder->where('institution_id', $user->institution_id);
        }
    }
}
