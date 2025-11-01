<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\OutboundOperation;
use Illuminate\Auth\Access\HandlesAuthorization;

class OutboundOperationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OutboundOperation');
    }

    public function view(AuthUser $authUser, OutboundOperation $outboundOperation): bool
    {
        return $authUser->can('View:OutboundOperation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OutboundOperation');
    }

    public function update(AuthUser $authUser, OutboundOperation $outboundOperation): bool
    {
        return $authUser->can('Update:OutboundOperation');
    }

    public function delete(AuthUser $authUser, OutboundOperation $outboundOperation): bool
    {
        return $authUser->can('Delete:OutboundOperation');
    }

    public function restore(AuthUser $authUser, OutboundOperation $outboundOperation): bool
    {
        return $authUser->can('Restore:OutboundOperation');
    }

    public function forceDelete(AuthUser $authUser, OutboundOperation $outboundOperation): bool
    {
        return $authUser->can('ForceDelete:OutboundOperation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:OutboundOperation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:OutboundOperation');
    }

    public function replicate(AuthUser $authUser, OutboundOperation $outboundOperation): bool
    {
        return $authUser->can('Replicate:OutboundOperation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:OutboundOperation');
    }

}