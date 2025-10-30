<?php

namespace App\Policies;

use App\Models\OutboundOperation;
use App\Models\User;

class OutboundOperationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_outbound_operations');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OutboundOperation $outboundOperation): bool
    {
        return $user->hasPermissionTo('view_outbound_operations');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_outbound_operations');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OutboundOperation $outboundOperation): bool
    {
        return $user->hasPermissionTo('edit_outbound_operations');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OutboundOperation $outboundOperation): bool
    {
        return $user->hasPermissionTo('delete_outbound_operations');
    }

    /**
     * Determine whether the user can confirm the outbound operation.
     */
    public function confirm(User $user, OutboundOperation $outboundOperation): bool
    {
        return $user->hasPermissionTo('confirm_outbound_operations');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OutboundOperation $outboundOperation): bool
    {
        return $user->hasPermissionTo('delete_outbound_operations');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OutboundOperation $outboundOperation): bool
    {
        return $user->hasPermissionTo('delete_outbound_operations');
    }
}
