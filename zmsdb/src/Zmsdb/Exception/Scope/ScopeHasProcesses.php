<?php

namespace BO\Zmsdb\Exception\Scope;

/**
 * class to generate an exception if children exists
 */
class ScopeHasProcesses extends \Exception
{
    protected $code = 428;

    protected $message = 'There are still some processes assigned to this scope. ' .
         'Please mind to delete all children before delete parent item.';
}
