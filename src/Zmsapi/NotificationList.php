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
        Helper\User::checkRights('department');

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $notificationList = (new Query())->readList($resolveReferences);
        $message = Response\Message::create(Render::$request);
        $message->data = $notificationList;
        Render::json($message);
    }
}
