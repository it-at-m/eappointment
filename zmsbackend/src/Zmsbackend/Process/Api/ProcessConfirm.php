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
class ProcessConfirm extends \BO\Zmsbackend\Api\BaseController
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
        \BO\Zmsbackend\Connection\Select::setCriticalReadSession();

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(3)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Process($input);
        $entity->testValid();
        $this->testProcessData($entity);

        $userAccount = (new \BO\Zmsbackend\Helper\User($request))->readWorkstation()->getUseraccount();
        $process = (new \BO\Zmsbackend\Process\Service\Process())->readEntity($entity->id, $entity->authKey, 2);

        //$this->validateProcessLimits($process); Should be moved to zmscitizenapi.
        if ('preconfirmed' != $process->status && 'reserved' != $process->status) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessNotPreconfirmedAnymore();
        }

        $this->updateOverviewCalendar($process);

        $process = (new \BO\Zmsbackend\Process\Service\Process())->updateProcessStatus(
            $process,
            'confirmed',
            \App::$now,
            $resolveReferences,
            $userAccount
        );
        $this->writeMails($request, $process);
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
    protected function writeMails($request, $process)
    {
        if ($process->hasScopeAdmin() && $process->sendAdminMailOnConfirmation()) {
            $authority = $request->getUri()->getAuthority();
            $validator = $request->getAttribute('validator');
            $initiator = $validator->getParameter('initiator')
                ->isString()
                ->setDefault("$authority API-User")
                ->getValue();
            $config = (new \BO\Zmsbackend\Config\Service\Config())->readEntity();
            $mail = (new \BO\Zmsentities\Mail())
            ->setTemplateProvider(new \BO\Zmsbackend\Helper\MailTemplateProvider($process))
            ->toResolvedEntity($process, $config, 'appointment', $initiator);
            (new \BO\Zmsbackend\Mail\Service\Mail())->writeInQueueWithAdmin($mail, \App::$now);
        }
    }

    private function updateOverviewCalendar(\BO\Zmsentities\Process $process): void
    {
        $appointment = $process->getFirstAppointment();
        $scopeId = (int) $appointment->scope->id;

        $timezone = new \DateTimeZone(\BO\Zmsbackend\Connection\Select::$connectionTimezone);
        $startsAt = (new \DateTimeImmutable('@' . $appointment->date))->setTimezone($timezone);

        $slotCount = (int)($appointment->slotCount ?? 0);
        $slotTimeInMinutes = (int)($appointment->availability->slotTimeInMinutes ?? 0);
        $durationMinutes   = $slotCount * $slotTimeInMinutes;

        $endsAt = $startsAt->modify('+' . $durationMinutes . ' minutes');

        (new \BO\Zmsbackend\OverviewCalendar\Service\OverviewCalendar())->insert(
            $scopeId,
            (int) $process->id,
            'confirmed',
            $startsAt,
            $endsAt
        );
    }

    protected function testProcessData($entity)
    {
        $authCheck = (new \BO\Zmsbackend\Process\Service\Process())->readAuthKeyByProcessId($entity->id);
        if (! $authCheck) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessNotFound();
        } elseif ($authCheck['authKey'] !== $entity->authKey) {
            throw new \BO\Zmsbackend\Process\Exception\AuthKeyMatchFailed();
        }
    }

    protected function validateProcessLimits(\BO\Zmsentities\Process $process)
    {
        if (! (new \BO\Zmsbackend\Process\Service\Process())->isAppointmentSlotCountAllowed($process)) {
            throw new \BO\Zmsbackend\Process\Exception\MoreThanAllowedSlotsPerAppointment();
        }

        if (! (new \BO\Zmsbackend\Process\Service\Process())->isServiceQuantityAllowed($process)) {
            throw new \BO\Zmsbackend\Process\Exception\MoreThanAllowedQuantityPerService();
        }
    }
}
