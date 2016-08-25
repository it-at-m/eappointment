<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Mail as Query;

/**
  * Handle requests concerning services
  */
class MailDelete extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $query = new Query();
        $message = Response\Message::create(Render::$request);
        $mail = $query->readEntity($itemId);
        if ($query->deleteEntity($itemId)) {
            $message->data = $mail;
        } else {
            $message->meta->statuscode = 500;
            $message->meta->error = true;
            $message->meta->message = "Could not delete mail";
        }
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
