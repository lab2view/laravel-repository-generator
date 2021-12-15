<?php

namespace Lab2view\RepositoryGenerator;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class BasePolicy
{
    use HandlesAuthorization;

    /**
     * @param  User|null $user
     * @param  string    $ability
     * @return bool|null
     */
    public function before(?User $user, string $ability): ?bool
    {
        if ($user) {
            return $user->isSuperAdmin() ?: null;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  User $user
     * @return mixed
     */
    public function viewAny(User $user): mixed
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  User $user
     * @return mixed
     */
    public function create(User $user): mixed
    {
        return false;
    }
}
