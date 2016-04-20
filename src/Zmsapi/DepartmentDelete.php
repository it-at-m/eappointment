<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Department as Query;

/**
  * Handle requests concerning services
  */
class DepartmentDelete extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $query = new Query();
        $message = Response\Message::create();
        $department = $query->readEntity($itemId);
        $query->deleteEntity($itemId);
        $message->data = $department;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
