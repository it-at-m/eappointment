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
    public static function render($source, $itemIds = null)
    {
        $request = (new Query())->readList($source, $itemIds);
        $message = Response\Message::create();
        $message->data = $request;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
