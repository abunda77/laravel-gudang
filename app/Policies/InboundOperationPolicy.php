<?php

namespace App\Policies;

use App\Models\InboundOperation;
use App\Models\User;

class InboundOperationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_inbound_operations');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InboundOperation $inboundOperation): bool
    {
        return $user->hasPermissionTo('view_inbound_operations');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_inbound_operations');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InboundOperation $inboundOperation): bool
    {
        return $user->hasPermissionTo('edit_inbound_operations');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InboundOperation $inboundOperation): bool
    {
        return $user->hasPermissionTo('delete_inbound_operations');
    }

    /**
     * Determine whether the user can confirm the inbound operation.
     */
    public function confirm(User $user, InboundOperation $inboundOperation): bool
    {
        return $user->hasPermissionTo('confirm_inbound_operations');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InboundOperation $inboundOperation): bool
    {
        return $user->hasPermissionTo('delete_inbound_operations');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InboundOperation $inboundOperation): bool
    {
        return $user->hasPermissionTo('delete_inbound_operations');
    }
}
