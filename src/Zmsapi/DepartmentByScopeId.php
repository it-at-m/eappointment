<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope as Query;

/**
  * Handle requests concerning services
  */
class DepartmentByScopeId extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $department = (new \BO\Zmsdb\Department())->readByScopeId($itemId, $resolveReferences);
        if (! $department) {
            throw new Exception\Department\DepartmentNotFound();
        }
        $message = Response\Message::create(Render::$request);
        $message->data = $department;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
