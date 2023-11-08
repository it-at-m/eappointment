<?php

namespace BO\Zmsdb\Exception\Department;

/**
 * class to generate an exception if children exists
 */
class ScopeListNotEmpty extends \Exception
{
    protected $code = 428;

    protected $message = 'There are still some children (scopes or clusters) in department entity. ' .
         'Please mind to delete all children before delete parent item.';
}
