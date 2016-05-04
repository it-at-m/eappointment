<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Request as Query;

/**
  * Handle requests concerning services
  */
class RequestGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($source, $itemId)
    {
        $request = (new Query())->readEntity($source, $itemId);
        $message = Response\Message::create(Render::$request);
        $message->data = $request;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
