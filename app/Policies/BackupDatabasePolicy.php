<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BackupDatabasePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('page_BackupDatabase');
    }

    public function view(User $user): bool
    {
        return $user->can('page_BackupDatabase');
    }

    public function create(User $user): bool
    {
        return $user->can('page_BackupDatabase');
    }

    public function update(User $user): bool
    {
        return $user->can('page_BackupDatabase');
    }

    public function delete(User $user): bool
    {
        return $user->can('page_BackupDatabase');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('page_BackupDatabase');
    }
}
