<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\ProcessStatusQueued;

/**
 * @SuppressWarnings(Coupling)
 */
class ProcessPickup extends BaseController
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
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Process($input);
        \BO\Zmsdb\Connection\Select::getWriteConnection();

        $process = $this->readValidProcess($workstation, $entity, $input);
        $this->testProcessAccess($workstation, $process);
        (new \BO\Zmsdb\Workstation)->writeAssignedProcess($workstation, $process, \App::$now);

        $message = Response\Message::create($request);
        $message->data = (new Query)->readEntity($process->id, $process->authKey);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcessData($entity)
    {
        $entity->testValid();
        $authCheck = (new Query())->readAuthKeyByProcessId($entity->id);
        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authCheck['authKey'] != $entity->authKey && $authCheck['authName'] != $entity->authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        }
    }

    protected function testProcessAccess($workstation, $process)
    {
        $cluster = (new \BO\Zmsdb\Cluster)->readByScopeId($workstation->scope['id'], 1);
        $workstation->testMatchingProcessScope($workstation->getScopeList($cluster), $process);
        if ($workstation->process && $workstation->process->hasId() && $workstation->process->id != $process->id) {
            $exception = new Exception\Workstation\WorkstationHasAssignedProcess();
            $exception->data = [
                'process' => $workstation->process
            ];
            throw $exception;
        }
    }

    protected function readValidProcess($workstation, $entity, $input)
    {
        if ($entity->hasProcessCredentials()) {
            $this->testProcessData($entity);
            $entity->addData($input);
            $process = (new Query())->updateEntity(
                $entity,
                \App::$now,
                0,
                null,
                $workstation->getUseraccount()
            );
        } elseif ($entity->hasQueueNumber()) {
            // Allow waitingnumbers over 1000 with the fourth parameter
            $process = ProcessStatusQueued::init()
                ->readByQueueNumberAndScope($entity['queue']['number'], $workstation->scope['id'], 0, 100000000);
            if (! $process->id) {
                $workstation = (new \BO\Zmsdb\Workstation)->readResolvedReferences($workstation, 1);
                $process = (new Query())->writeNewPickup(
                    $workstation->scope,
                    \App::$now,
                    $entity['queue']['number'],
                    $workstation->getUseraccount()
                );
            }
            $process->testValid();
        } else {
            $entity->testValid();
            throw new Exception\Process\ProcessInvalid();
        }
        return $process;
    }
}
