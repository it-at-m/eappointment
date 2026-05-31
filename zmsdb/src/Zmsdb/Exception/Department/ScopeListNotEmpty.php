<?php

namespace BO\Zmsdb\Exception\Department;

/**
 * class to generate an exception if children exists
 */
class ScopeListNotEmpty extends \Exception
{
    protected int $code = 428;

    protected string $message = 'There are still some children (scopes or clusters) in department entity. ' .
         'Please mind to delete all children before delete parent item.';
}
