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
        if (!array_key_exists('id', $mail)) {
            $status = 404;
            $message->meta->error = 'Not found';
        } else {
            $status = 200;
            $message->data = $mail;
            $query->deleteEntity($itemId);
        }
        Render::lastModified(time(), '0');
        Render::json($message, $status);
    }
}
