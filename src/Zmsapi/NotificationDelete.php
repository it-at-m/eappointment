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
class NotificationDelete extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $message = Response\Message::create();
        $message->data = \BO\Zmsentities\Notification::createExample();
        $message->data->id = $itemId;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
