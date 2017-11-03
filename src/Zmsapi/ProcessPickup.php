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
        $cluster = (new \BO\Zmsdb\Cluster)->readByScopeId($workstation->scope['id'], 1);

        if ($entity->hasProcessCredentials()) {
            $this->testProcessData($entity);
            $process = (new Query())->readEntity($entity['id'], $entity['authKey'], 0);
            $process->addData($input);
            $process = (new Query())->updateEntity($process, \App::$now);
        } elseif ($entity->hasQueueNumber()) {
            $process = ProcessStatusQueued::init()
                ->readByQueueNumberAndScope($entity['queue']['number'], $workstation->scope['id']);
            if (! $process->id) {
                $workstation = (new \BO\Zmsdb\Workstation)->readResolvedReferences($workstation, 1);
                $process = (new Query())->writeNewPickup($workstation->scope, \App::$now, $entity['queue']['number']);
            }
            $process->testValid();
        } else {
            $entity->testValid();
            throw new Exception\Process\ProcessInvalid();
        }
        $workstation->testMatchingProcessScope($workstation->getScopeList($cluster), $process);
        (new \BO\Zmsdb\Workstation)->writeAssignedProcess($workstation->id, $process, \App::$now);

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
}
