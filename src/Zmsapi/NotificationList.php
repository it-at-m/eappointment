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
class NotificationList extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $notificationList = (new Query())->readList(2);
        $message = Response\Message::create(Render::$request);
        $message->data = $notificationList;
        Render::json($message);
    }
}
