<?php

namespace BO\Zmsdb\Exception\Owner;

/**
 * class to generate an exception if children exists
 */
class OrganisationListNotEmpty extends \Exception
{
    protected $code = 428;

    protected $message = 'There are still some children (organisations) in owner entity. ' .
         'Please mind to delete all children before delete parent item.';
}
