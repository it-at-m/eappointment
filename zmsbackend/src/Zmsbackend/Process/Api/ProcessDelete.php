<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Process\Service\Process;
use BO\Zmsbackend\Mail\Service\Mail;
use BO\Zmsbackend\Config\Service\Config;
use BO\Mellon\Validator;

/**
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class ProcessDelete extends \BO\Zmsbackend\Api\BaseController
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
        $workstation = (new \BO\Zmsbackend\Helper\User($request))->readWorkstation();
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $process = (new \BO\Zmsbackend\Process\Service\Process())->readEntity($args['id'], new \BO\Zmsbackend\Helper\NoAuth(), 2);
        $this->testProcessData($process, $args['authKey']);
        if ('reserved' == $process->status) {
            if (!(new \BO\Zmsbackend\Process\Service\Process())->writeBlockedEntity($process, false, $workstation->getUseraccount())) {
                throw new \BO\Zmsbackend\Process\Exception\ProcessDeleteFailed(); // @codeCoverageIgnore
            }
            $processDeleted = $process;
        } else {
            (new \BO\Zmsbackend\OverviewCalendar\Service\OverviewCalendar())->perform(
                \BO\Zmsbackend\Calendar\Repository\OverviewCalendar::CANCEL_BY_PROCESS,
                ['process_id' => (int)$process->id]
            );

            $processDeleted = (new \BO\Zmsbackend\Process\Service\Process())->writeCanceledEntity(
                $args['id'],
                $args['authKey'],
                null,
                $workstation->getUseraccount()
            );
            if (!$processDeleted || !$processDeleted->hasId()) {
                throw new \BO\Zmsbackend\Process\Exception\ProcessDeleteFailed(); // @codeCoverageIgnore
            }
        }
        $this->writeMails($request, $process);
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $processDeleted;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function writeMails($request, $process)
    {
        if ($process->hasScopeAdmin() && $process->sendAdminMailOnDeleted() && $process->getStatus() !== 'blocked') {
            $authority = $request->getUri()->getAuthority();
            $validator = $request->getAttribute('validator');
            $initiator = $validator->getParameter('initiator')
                ->isString()
                ->setDefault("$authority API-User")
                ->getValue();
            $config = (new \BO\Zmsbackend\Config\Service\Config())->readEntity();
            $mail = (new \BO\Zmsentities\Mail())
                ->setTemplateProvider(new \BO\Zmsbackend\Helper\MailTemplateProvider($process))
                ->toResolvedEntity($process, $config, 'deleted', $initiator);
            (new \BO\Zmsbackend\Mail\Service\Mail())->writeInQueueWithAdmin($mail, \App::$now);
        }
    }

    protected function testProcessData($process, $authKey)
    {
        if (!$process) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessNotFound();
        }
        if ($process['authKey'] !== $authKey) {
            throw new \BO\Zmsbackend\Process\Exception\AuthKeyMatchFailed();
        }
    }
}
