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
class DepartmentGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        Helper\User::checkRights('department');

        $query = new Query();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $department = $query->readEntity($itemId, $resolveReferences);

        $message = Response\Message::create(Render::$request);
        $message->data = $department;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
