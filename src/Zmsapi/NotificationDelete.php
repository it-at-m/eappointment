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

        if ($notification && ! $notification->hasId()) {
            throw new Exception\Notificiation\NotificationNotFound();
        }

        if ($query->deleteEntity($itemId)) {
            $message->data = $notification;
        } else {
            throw new Exception\Mail\NotificationDeleteFailed();
        }
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
