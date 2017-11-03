<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\ProcessStatusArchived as Query;
use \BO\Zmsdb\Process;
use \BO\Zmsdb\Workstation;

/**
 * @SuppressWarnings(Coupling)
 */
class ProcessFinished extends BaseController
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
        $process = new \BO\Zmsentities\Process($input);
        $process->testValid();
        $this->testProcessData($process);
        $this->testProcessInWorkstation($process, $workstation);

        $query = new Query();
        $clients = $query->readEntity($process->id, new \BO\Zmsdb\Helper\NoAuth())->getClients();
        if ('pending' == $process['status']) {
            $process = $query->updateEntity($process, \App::$now);
            (new Workstation)->writeRemovedProcess($workstation);
        } else {
            $query->writeEntityFinished($process, \App::$now);
            foreach ($clients as $client) {
                if ($client->hasSurveyAccepted()) {
                    $config = (new \BO\Zmsdb\Config())->readEntity();
                    $mail = (new \BO\Zmsentities\Mail())->toResolvedEntity($process, $config);
                    (new \BO\Zmsdb\Mail())->writeInQueue($mail, \App::$now);
                }
            }
        }

        $message = Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcessInWorkstation($process, $workstation)
    {
        $cluster = (new \BO\Zmsdb\Cluster)->readByScopeId($workstation->scope['id'], 1);
        $workstation->process = $process;
        $workstation->testMatchingProcessScope($workstation->getScopeList($cluster));
    }

    protected function testProcessData($process)
    {
        $hasValidId = (
            $process->hasId() &&
            ('pending' == $process['status'] || 'finished' == $process['status'])
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
