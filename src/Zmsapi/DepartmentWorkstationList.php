<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Workstation as Query;

/**
  * Handle requests concerning services
  */
class DepartmentWorkstationList extends BaseController
{
    /**
     * @return String
     */
    public static function render($departmentId)
    {
        Helper\User::checkRights('useraccount');
        Helper\User::checkDepartment($departmentId);

        $query = new Query();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $collection = $query->readCollectionByDepartmentId($departmentId, $resolveReferences);

        $message = Response\Message::create(Render::$request);
        $message->data = $collection;
        Render::lastModified(time(), '0');
        Render::json($message, 200);
    }
}
