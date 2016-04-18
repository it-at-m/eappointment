<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Request as Query;

/**
  * Handle requests concerning services
  */
class RequestList extends BaseController
{
    /**
     * @return String
     */
    public static function render($source, $requestIds = null)
    {
        $requestList = (new Query())->readProviderList($source, $requestIds, 2);
        $message = Response\Message::create();
        $message->data = $requestList;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
