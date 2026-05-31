<?php

namespace BO\Zmsdb\Exception\Organisation;

/**
 * class to generate an exception if children exists
 */
class DepartmentListNotEmpty extends \Exception
{
    protected int $code = 428;

    protected string $message = 'There are still some children (departments) in organisation entity. ' .
         'Please mind to delete all children before delete parent item.';
}
