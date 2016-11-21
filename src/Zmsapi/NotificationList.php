<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
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
        $message = Response\Message::create(Render::$request);
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $notificationList = (new Query())->readList($resolveReferences);

        if (0 < count($notificationList)) {
            $message->data = $notificationList;
        } else {
            $message->data = new \BO\Zmsentities\Collection\NotificationList();
            $message->error = false;
            $message->message = '';
        }
        Render::json($message, 200);
    }
}
