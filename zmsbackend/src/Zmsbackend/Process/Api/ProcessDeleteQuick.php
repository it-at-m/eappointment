<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Process\Service\Process;
use BO\Zmsbackend\Process\Service\ProcessStatusArchived;
use BO\Zmsbackend\Mail\Service\Mail;
use BO\Zmsbackend\Config\Service\Config;
use BO\Mellon\Validator;

class ProcessDeleteQuick extends ProcessDelete
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
        $workstation = (new \BO\Zmsbackend\Helper\User($request, 2))->checkPermissions('appointment');
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $process = (new \BO\Zmsbackend\Process\Service\Process())->readEntity($args['id'], new \BO\Zmsbackend\Helper\NoAuth(), 2);

        $this->testProcess($workstation, $process);

        if ($process->hasId() && $process->scope && $process->status == 'confirmed') {
            (new \BO\Zmsbackend\OverviewCalendar\Service\OverviewCalendar())->perform(
                \BO\Zmsbackend\Calendar\Repository\OverviewCalendar::CANCEL_BY_PROCESS,
                ['process_id' => (int)$process->id]
            );
        }
        $process->status = 'blocked';
        $this->writeMails($request, $process);
        $status = (new \BO\Zmsbackend\Process\Service\Process())->writeBlockedEntity($process, false, $workstation->getUseraccount());
        if (!$status) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessDeleteFailed(); // @codeCoverageIgnore
        }
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcess($workstation, $process)
    {
        if (!$process->hasId()) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessNotFound();
        }

        if (
            !in_array(
                $process->getCurrentScope()->getId(),
                $workstation->getScopeListFromAssignedDepartments()->getIds()
            )
        ) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessNoAccess();
        }

        if ('called' == $process->status || 'processing' == $process->status) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessAlreadyCalled();
        }
        $process->testValid();
    }
}
