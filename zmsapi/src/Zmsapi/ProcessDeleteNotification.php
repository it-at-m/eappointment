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
class ProcessDeleteNotification extends BaseController
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

        \BO\Zmsdb\Connection\Select::getWriteConnection();

        $config = (new Config())->readEntity();
        $scopeId = $process->getScopeId();
        if (!$scopeId || $scopeId == 0) {
            throw new Exception\Process\ProcessInvalid(
                "Process scope ID is missing or invalid"
            );
        }
        $department = (new Department())->readByScopeId($scopeId);
        if (!$department) {
            throw new Exception\Department\DepartmentNotFound(
                "Department not found for scope " . $scopeId
            );
        }
        $notification = (new \BO\Zmsentities\Notification())
            ->toResolvedEntity($process, $config, $department, 'deleted');
        $notification = (new Query())->writeInQueue($notification, \App::$now, false);
        \App::$log->debug("Send notification", [$notification]);

        $message = Response\Message::create($request);
        $message->data = $notification;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }

    protected function testProcessData($process)
    {
        $authCheck = (new Process())->readAuthKeyByProcessId($process->id);
        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif (! $process->getFirstClient()->hasTelephone()) {
            throw new Exception\Process\TelephoneRequired();
        }
    }
}
