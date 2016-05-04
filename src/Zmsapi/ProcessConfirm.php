<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\Mail;
use \BO\Zmsdb\Notification;

/**
  * Handle requests concerning services
  */
class ProcessConfirm extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $query = new Query();
        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();
        $entity = new \BO\Zmsentities\Process($input);
        $process = $query->updateProcessStatus($entity, 'confirmed');

        //write mail in queue
        $mail = Messaging\Mail::getEntityData($process);
        MailAdd::render($mail);

        //write notification in queue
        $notification = Messaging\Notification::getEntityData($process);
        NotificationAdd::render($notification);

        $message->data = $process;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
