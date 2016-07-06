<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Request as Query;

/**
  * Handle requests concerning services
  */
class RequestList extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $query = new Query();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $requestList = $query->readList($resolveReferences);

        $message = Response\Message::create(Render::$request);
        $message->data = $requestList;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
