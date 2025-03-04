<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Notification as Query;
use BO\Zmsdb\Config;
use BO\Zmsdb\Process;
use BO\Zmsdb\Department;

/**
 * @SuppressWarnings(Coupling)
 */
class ProcessConfirmationNotification extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $process = new \BO\Zmsentities\Process($input);
        $process->testValid();
        $this->testProcessData($process);
        $process = (new Process())->readEntity($process->id, $process->authKey);

        \BO\Zmsdb\Connection\Select::getWriteConnection();

        $notification = $this->writeNotification($process);
        $message = Response\Message::create($request);
        $message->data = ($notification->hasId()) ? $notification : null;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }

    protected static function writeNotification(\BO\Zmsentities\Process $process)
    {
        $config = (new Config())->readEntity();
        $department = (new Department())->readByScopeId($process->scope['id']);
        $notification = (new \BO\Zmsentities\Notification())
            ->toResolvedEntity(
                $process,
                $config,
                $department,
                ($process->isWithAppointment()) ? 'appointment' : 'confirmed'
            );
        $notification->testValid();
        if ($process->scope->hasNotificationEnabled() && $process->getFirstClient()->hasTelephone()) {
            $notification = (new Query())->writeInQueue($notification, \App::$now, false);
            \App::$log->debug("Send notification", [$notification]);
        }
        return $notification;
    }

    protected function testProcessData(\BO\Zmsentities\Process $process)
    {
        $authCheck = (new Process())->readAuthKeyByProcessId($process->id);
        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authCheck['authKey'] != $process->authKey && $authCheck['authName'] != $process->authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        }
    }
}
