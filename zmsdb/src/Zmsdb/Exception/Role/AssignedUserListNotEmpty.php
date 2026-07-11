<?php

namespace BO\Zmsdb\Exception\Role;

/**
 * Thrown when attempting to delete a role that still has assigned users.
 */
class AssignedUserListNotEmpty extends \Exception
{
    protected $code = 428;

    protected $message = 'There are still users assigned to this role. ' .
        'Please remove all user assignments before deleting the role.';
}
