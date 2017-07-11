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
        $workstation = (new Helper\User($request, 1))->checkRights();
        $process = $workstation->process;
        if (!$process || !$process->hasId()) {
            $input = Validator::input()->isJson()->assertValid()->getValue();
            $entity = new \BO\Zmsentities\Process($input);
            $process = (new Process)->readEntity($entity['id'], new \BO\Zmsdb\Helper\NoAuth());
            $this->testProcess($process);
        }

        $process->setCallTime(\App::$now);
        $process->queue['callCount']++;
        $process->status = 'called';
        $workstation->process = (new Workstation)->writeAssignedProcess($workstation->id, $process);

        $message = Response\Message::create($request);
        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcess($process)
    {
        $selectedDate = Validator::param('date')->isString()->getValue();
        $dateTime = ($selectedDate) ? new DateTime($selectedDate) : \App::$now;
        if (! $process->hasId()) {
            throw new Exception\Process\ProcessNotFound();
        }
        if ('called' == $process->status || 'processing' == $process->status) {
            throw new Exception\Process\ProcessAlreadyCalled();
        }
        if ($process->getFirstAppointment()->date > $dateTime->getTimestamp()) {
            throw new Exception\Process\ProcessNotFoundInQueue();
        }
        $process->testValid();
    }
}
