<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Process\Service\ProcessStatusArchived as Query;
use BO\Zmsbackend\Process\Service\Process;
use BO\Zmsbackend\Workstation\Service\Workstation;

/**
 * @SuppressWarnings(Coupling)
 */
class ProcessFinished extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = (new \BO\Zmsbackend\Helper\User($request))->checkPermissions('appointment');
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $survey = Validator::param('survey')->isNumber()->setDefault(1)->getValue();
        $process = new \BO\Zmsentities\Process($input);
        $process->testValid();
        $this->testProcessData($process);
        $this->testProcessInWorkstation($process, $workstation);

        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $query = new Query();
        if ('pending' == $process['status']) {
            $process = $query->updateEntity(
                $process,
                \App::$now,
                0,
                $process['status'],
                $workstation->getUseraccount()
            );
            (new \BO\Zmsbackend\Workstation\Service\Workstation())->writeRemovedProcess($workstation);
        } else {
            $query->writeEntityFinished($process, \App::$now, false, $workstation->getUseraccount());
        }

        if ($survey) {
            $this->writeSurveyMail($process);
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcessInWorkstation($process, $workstation)
    {
        $department = (new \BO\Zmsbackend\Department\Service\Department())->readByScopeId($workstation->scope['id'], 1);
        $workstation->process = $process;
        $workstation->validateProcessScopeAccess($department->getScopeList());
    }

    protected function testProcessData($process)
    {
        $hasValidId = (
            $process->hasId() &&
            ('pending' == $process['status'] || 'finished' == $process['status'])
        );
        if (! $hasValidId) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessInvalid();
        }

        $processCheck = (new \BO\Zmsbackend\Process\Service\Process())->readEntity($process->id, new \BO\Zmsbackend\Helper\NoAuth());
        if (null === $processCheck || false === $processCheck->hasId()) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessNotFound();
        } elseif ($processCheck->authKey !== $process->authKey) {
            throw new \BO\Zmsbackend\Process\Exception\AuthKeyMatchFailed();
        }
    }

    protected function writeSurveyMail($process)
    {
        $process = clone $process;
        foreach ($process->getClients() as $client) {
            if ($client->hasSurveyAccepted()) {
                $config = (new \BO\Zmsbackend\Config\Service\Config())->readEntity();
                $process->scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($process['scope']['id'], 1);
                $mail = (new \BO\Zmsentities\Mail())->toResolvedEntity($process, $config, 'survey');
                (new \BO\Zmsbackend\Mail\Service\Mail())->writeInQueue($mail, \App::$now, false);
            }
        }
    }
}
