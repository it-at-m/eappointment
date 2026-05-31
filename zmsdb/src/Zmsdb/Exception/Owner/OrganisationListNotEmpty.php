<?php

namespace BO\Zmsdb\Exception\Owner;

/**
 * class to generate an exception if children exists
 */
class OrganisationListNotEmpty extends \Exception
{
    protected int $code = 428;

    protected string $message = 'There are still some children (organisations) in owner entity. ' .
         'Please mind to delete all children before delete parent item.';
}
