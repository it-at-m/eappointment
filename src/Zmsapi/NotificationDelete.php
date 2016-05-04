<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Notification as Query;

/**
  * Handle requests concerning services
  */
class NotificationDelete extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $query = new Query();
        $message = Response\Message::create(Render::$request);
        $notification = $query->readEntity($itemId);
        $query->deleteEntity($itemId);
        $message->data = $notification;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
