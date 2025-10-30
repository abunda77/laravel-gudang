<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\Enums\PurchaseOrderStatus;

class PurchaseOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_purchase_orders');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasPermissionTo('view_purchase_orders');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_purchase_orders');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Only allow editing draft purchase orders
        if ($purchaseOrder->status !== PurchaseOrderStatus::DRAFT) {
            return false;
        }

        return $user->hasPermissionTo('edit_purchase_orders');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        // Only allow deleting draft purchase orders
        if ($purchaseOrder->status !== PurchaseOrderStatus::DRAFT) {
            return false;
        }

        return $user->hasPermissionTo('delete_purchase_orders');
    }

    /**
     * Determine whether the user can approve the purchase order.
     */
    public function approve(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasPermissionTo('approve_purchase_orders');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasPermissionTo('delete_purchase_orders');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasPermissionTo('delete_purchase_orders');
    }
}
