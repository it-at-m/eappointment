<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope as Query;
use \BO\Zmsdb\Process;
use \BO\Zmsdb\Workstation;
use \BO\Zmsentities\Helper\DateTime;

class ProcessNextByScope extends BaseController
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


        (new Helper\User($request))->checkRights();
        $query = new Query();
        $selectedDate = Validator::param('date')->isString()->getValue();
        $exclude = Validator::param('exclude')->isString()->getValue();



        $dateTime = ($selectedDate) ? new DateTime($selectedDate) : \App::$now;
        $scope = $query->readEntity($args['id']);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $queueList = $query->readQueueList($scope->id, $dateTime, 1);

        $message = Response\Message::create($request);
        $message->data = static::getProcess($queueList, $dateTime, $exclude);

        if($message->data->id >= 1000 || $message->data->id !== 0){

            $workstation = (new Helper\User($request))->checkRights();
            $process = new \BO\Zmsentities\Process($message->data);
            
            $process->testValid();
    
            $this->testProcessData($process);
            $this->testProcessInWorkstation($process, $workstation);
    
            \BO\Zmsdb\Connection\Select::getWriteConnection();
            $query = new \BO\Zmsdb\ProcessStatusArchived;

            //error_log(json_encode($process));

            //error_log($process->queuedTime);
    
            if ('queued' == $process['status']) {
                error_log($process->queuedTime);

                $process = $query->updateEntity($process, \App::$now);
                (new Workstation)->writeRemovedProcess($workstation);
            }

        }

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }

    public static function getProcess($queueList, $dateTime, $exclude = null)
    {
        $process = $queueList->getNextProcess($dateTime, $exclude);
        return ($process) ? $process : new \BO\Zmsentities\Process();
    }

    protected function testProcessInWorkstation($process, $workstation)
    {
        $department = (new \BO\Zmsdb\Department)->readByScopeId($workstation->scope['id'], 1);
        $workstation->process = $process;
        $workstation->testMatchingProcessScope($department->getScopeList());
    }

    protected function testProcessData($process)
    {
        $hasValidId = (
            $process->hasId() &&
            ('queued' == $process['status'] || 'called' == $process['status'])
        );
        if (! $hasValidId) {
            throw new Exception\Process\ProcessInvalid();
        }

        $processCheck = (new Process())->readEntity($process->id, new \BO\Zmsdb\Helper\NoAuth());
        
        if (null === $processCheck || false === $processCheck->hasId()) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($processCheck->authKey != $process->authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        }
    }
}
