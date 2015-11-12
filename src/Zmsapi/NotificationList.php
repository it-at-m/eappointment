<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;

/**
  * Handle requests concerning services
  *
  */
class NotificationList extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create();
        $message->data = array(\BO\Zmsentities\Notification::createExample());
        Render::json($message);
    }
}
