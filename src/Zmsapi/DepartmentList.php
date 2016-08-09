<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Department as Query;

/**
  * Handle requests concerning services
  */
class DepartmentList extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        Helper\User::checkRights('department');

        $query = new Query();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $departmentList = $query->readList($resolveReferences);

        $message = Response\Message::create(Render::$request);
        $message->data = $departmentList;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
