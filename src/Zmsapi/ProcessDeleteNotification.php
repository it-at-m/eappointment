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
use \BO\Zmsdb\Department;

/**
  *
  * @SuppressWarnings(CouplingBetweenObjects)
  *
  * Handle requests concerning services
  * @SuppressWarnings(Coupling)
  */
class ProcessDeleteNotification extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create(Render::$request);
        $message->data = array();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $process = new \BO\Zmsentities\Process($input);
        $authCheck = (new Process())->readAuthKeyByProcessId($process->id);
        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authCheck['authKey'] != $process->authKey && $authCheck['authName'] != $process->authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        } elseif ($process->getFirstClient()->hasTelephone()) {
            $config = (new Config())->readEntity();
            $department = (new Department())->readByScopeId($process->scope['id']);
            $notification = (new \BO\Zmsentities\Notification())->toResolvedEntity($process, $config, $department);
            $queueId = (new Query())->writeInQueue($notification);
            $notification->id = $queueId;
            $message->data = $notification;
            \App::$log->debug("Send notification", [$notification]);
        }

        Render::lastModified(time(), '0');
        // Always return a 200, even if no notification is send
        Render::json($message, 200);
    }
}
