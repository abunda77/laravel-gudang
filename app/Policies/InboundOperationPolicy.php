<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\InboundOperation;
use Illuminate\Auth\Access\HandlesAuthorization;

class InboundOperationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InboundOperation');
    }

    public function view(AuthUser $authUser, InboundOperation $inboundOperation): bool
    {
        return $authUser->can('View:InboundOperation');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InboundOperation');
    }

    public function update(AuthUser $authUser, InboundOperation $inboundOperation): bool
    {
        return $authUser->can('Update:InboundOperation');
    }

    public function delete(AuthUser $authUser, InboundOperation $inboundOperation): bool
    {
        return $authUser->can('Delete:InboundOperation');
    }

    public function restore(AuthUser $authUser, InboundOperation $inboundOperation): bool
    {
        return $authUser->can('Restore:InboundOperation');
    }

    public function forceDelete(AuthUser $authUser, InboundOperation $inboundOperation): bool
    {
        return $authUser->can('ForceDelete:InboundOperation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InboundOperation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InboundOperation');
    }

    public function replicate(AuthUser $authUser, InboundOperation $inboundOperation): bool
    {
        return $authUser->can('Replicate:InboundOperation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InboundOperation');
    }

}