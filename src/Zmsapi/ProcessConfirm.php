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
        $message = Response\Message::create();
        $input = Validator::input()->isJson()->getValue();
        $entity = new \BO\Zmsentities\Process($input);
        $process = $query->updateProcessStatus($entity, 'confirmed');

        $client = $process->getFirstClient();

        //write mail in queue
        $mail = Messaging\Mail::getEntityData($process);
        $mailQueued = (new Mail())->writeInQueue($mail);
        $client->emailSendCount += ($mailQueued) ? 1 : 0;

        //write notification in queue
        $notification = Messaging\Notification::getEntityData($process);
        $notificationQueued = (new Notification())->writeInQueue($notification);
        $client->notificationsSendCount += ($notificationQueued) ? 1 : 0;

        //update process
        $process->updateClients($client);
        $process = $query->updateEntity($process);

        $message->data = $process;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
