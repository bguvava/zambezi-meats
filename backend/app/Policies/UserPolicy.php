<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * User policy for authorization.
 *
 * @requirement USER-013 Implement role assignment
 */
class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can change the model's status.
     *
     * @requirement USER-010 Prevent admin self-deletion
     */
    public function changeStatus(User $user, User $model): bool
    {
        // Admin can change status, but not their own
        return $user->isAdmin() && $model->canChangeStatus($user);
    }

    /**
     * Determine whether the user can reset password.
     */
    public function resetPassword(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view activity history.
     */
    public function viewActivity(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    /**
     * Note: Delete policy not implemented as users cannot be deleted.
     *
     * @requirement USER-006 Implement status change functionality
     */
}
