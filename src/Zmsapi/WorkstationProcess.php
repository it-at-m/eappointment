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
        $workstation = (new Helper\User($request))->checkRights();
        $process = $workstation->process;
        if (! $process->hasId()) {
            $input = Validator::input()->isJson()->assertValid()->getValue();
            $entity = new \BO\Zmsentities\Process($input);
            $authCheck = (new Process)->readAuthKeyByProcessId($entity['id']);
            $process = (new Process)->readEntity($entity['id'], $authCheck['authKey']);
        }
        if (! $process) {
            throw new Exception\Process\ProcessNotFound();
        }
        if ('called' == $process->status || 'processing' == $process->status) {
            throw new Exception\Process\ProcessAlreadyCalled();
        }
        $process->testValid();
        $process->setCallTime(\App::$now);
        $process->queue['callCount']++;
        $workstation->process = (new Workstation)->writeAssignedProcess($workstation->id, $process);

        $message = Response\Message::create($request);
        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
