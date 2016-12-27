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

        if ($mail && ! $mail->hasId()) {
            throw new Exception\Mail\MailNotFound();
        }

        if ($query->deleteEntity($itemId)) {
            $message->data = $mail;
        } else {
            throw new Exception\Mail\MailDeleteFailed();
        }
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
