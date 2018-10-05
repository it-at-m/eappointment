<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Workstation;
use \BO\Zmsdb\Process;

/**
 * @SuppressWarnings(Coupling)
 */
class WorkstationProcess extends BaseController
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
        \BO\Zmsdb\Connection\Select::getWriteConnection();
        $workstation = (new Helper\User($request, 1))->checkRights();
        $process = $workstation->process;
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Process($input);
        if (!$process || !$process->hasId() || $process->id == $entity->id) {
            $process = (new Process)->readEntity($entity['id'], new \BO\Zmsdb\Helper\NoAuth());
            $this->testProcess($process);
            $workstation->testMatchingProcessScope($workstation->getScopeList(), $process);
        } else {
            $exception = new Exception\Workstation\WorkstationHasAssignedProcess();
            $exception->data = $process;
            throw $exception;
        }

        $process->setCallTime(\App::$now);
        $process->queue['callCount']++;
        $process->status = 'called';
        $workstation->process = (new Workstation)->writeAssignedProcess($workstation, $process, \App::$now);

        $message = Response\Message::create($request);
        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcess($process)
    {
        if (! $process->hasId()) {
            throw new Exception\Process\ProcessNotFound();
        }
        if ('called' == $process->status || 'processing' == $process->status) {
            throw new Exception\Process\ProcessAlreadyCalled();
        }
        $process->testValid();
    }
}
