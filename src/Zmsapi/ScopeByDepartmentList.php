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
class ScopeByDepartmentList extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $department = (new \BO\Zmsdb\Department())->readEntity($itemId);
        if (! $department) {
            throw new Exception\Department\DepartmentNotFound();
        }

        $scopeList = (new Query())->readByDepartmentId($itemId, $resolveReferences);
        $message = Response\Message::create(Render::$request);
        $message->data = $scopeList;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
