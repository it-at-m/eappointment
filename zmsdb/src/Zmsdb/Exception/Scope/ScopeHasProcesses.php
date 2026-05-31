<?php

namespace BO\Zmsdb\Exception\Scope;

/**
 * class to generate an exception if children exists
 */
class ScopeHasProcesses extends \Exception
{
    protected int $code = 428;

    protected string $message = 'There are still some processes assigned to this scope. ' .
         'Please mind to delete all children before delete parent item.';
}
