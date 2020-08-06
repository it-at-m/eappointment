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
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $allowClusterWideCall = Validator::param('allowClusterWideCall')->isBool()->setDefault(false)->getValue();
        if ($workstation->process->hasId() && $workstation->process->getId() != $input['id']) {
            $exception = new Exception\Workstation\WorkstationHasAssignedProcess();
            $exception->data = ['process' => $workstation->process];
            throw $exception;
        }

        $process = $this->readTestedProcess($workstation, $input, $allowClusterWideCall);
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

    protected function readTestedProcess($workstation, $input, $allowClusterWideCall)
    {
        $process = (new Process)->readEntity($input['id'], new \BO\Zmsdb\Helper\NoAuth());
        if (! $process->hasId()) {
            throw new Exception\Process\ProcessNotFound();
        }
        //add data after check for process found, because id will be set too
        $process->addData($input);

        if ('called' == $process->status || 'processing' == $process->status) {
            throw new Exception\Process\ProcessAlreadyCalled();
        }
        if ('reserved' == $process->getStatus()) {
            throw new Exception\Process\ProcessReservedNotCallable();
        }
        if (! $allowClusterWideCall) {
            $workstation->testMatchingProcessScope($workstation->getScopeList(), $process);
        }
        $process->testValid();
        return $process;
    }
}
