<?php

namespace App\Policies;

use App\Models\CustomStandard;
use App\Models\User;

class CustomStandardPolicy
{
    /**
     * Determine whether the user can view any models.
     * Only users with institution can view custom standards.
     */
    public function viewAny(User $user): bool
    {
        return $user->institution_id !== null;
    }

    /**
     * Determine whether the user can view the model.
     * Only users from the same institution can view.
     */
    public function view(User $user, CustomStandard $customStandard): bool
    {
        return $user->institution_id === $customStandard->institution_id;
    }

    /**
     * Determine whether the user can create models.
     * Only users with institution can create custom standards.
     */
    public function create(User $user): bool
    {
        return $user->institution_id !== null;
    }

    /**
     * Determine whether the user can update the model.
     * Only users from the same institution can update.
     */
    public function update(User $user, CustomStandard $customStandard): bool
    {
        return $user->institution_id === $customStandard->institution_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Only users from the same institution can delete.
     */
    public function delete(User $user, CustomStandard $customStandard): bool
    {
        return $user->institution_id === $customStandard->institution_id;
    }
}
