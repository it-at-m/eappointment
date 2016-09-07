<?php
/**
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Notification as Query;
use \BO\Zmsdb\Config;
use \BO\Zmsdb\Process;

/**
  * Handle requests concerning services
  */
class ProcessConfirmationNotification extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();
        $process = new \BO\Zmsentities\Process($input);
        $authKeyByProcessId = (new Process())->readAuthKeyByProcessId($process->id);
        
        if (null === $input) {
            throw new Exception\InvalidInput();
        } elseif (null === $authKeyByProcessId) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authKeyByProcessId != $process->authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        } else {
            $config = (new Config())->readEntity();
            $notification = (new \BO\Zmsentities\Notification())->toResolvedEntity($process, $config);
            $queueId = (new Query())->writeInQueue($notification);
            $notification->id = $queueId;
            $message->data = $notification;
        }

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
