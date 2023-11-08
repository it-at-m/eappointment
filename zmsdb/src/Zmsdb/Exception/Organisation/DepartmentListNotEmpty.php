<?php

namespace BO\Zmsdb\Exception\Organisation;

/**
 * class to generate an exception if children exists
 */
class DepartmentListNotEmpty extends \Exception
{
    protected $code = 428;

    protected $message = 'There are still some children (departments) in organisation entity. ' .
         'Please mind to delete all children before delete parent item.';
}
