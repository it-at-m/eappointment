<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Config\Service\Config;
use BO\Zmsbackend\Log\Service\Log;
use BO\Zmsbackend\Mail\Service\Mail;
use BO\Mellon\Validator;
use BO\Zmsbackend\Process\Service\Process;

/**
 * @SuppressWarnings(Coupling)
 * @return \Psr\Http\Message\ResponseInterface
 */
class ProcessUpdate extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @SuppressWarnings(Complexity)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $slotsRequired = Validator::param('slotsRequired')->isNumber()->getValue();
        $slotType = Validator::param('slotType')->isString()->getValue();
        $clientKey = Validator::param('clientkey')->isString()->getValue();
        $initiator = Validator::param('initiator')->isString()->getValue();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Process($input);
        $entity->testValid();
        $this->testProcessData($entity, ! $initiator);

        \BO\Zmsbackend\Connection\Select::setCriticalReadSession();
        $workstation = (new \BO\Zmsbackend\Helper\User($request))->readWorkstation();

        if ($slotType || $slotsRequired) {
            $process = \BO\Zmsbackend\Process\Service\Process::init()->updateEntityWithSlots(
                $entity,
                \App::$now,
                $slotType,
                $slotsRequired,
                $resolveReferences,
                $workstation->getUseraccount()
            );
            \BO\Zmsbackend\Helper\Matching::testCurrentScopeHasRequest($process);
            $this->syncOverviewCalendarFromProcess($entity, $process);
        } elseif ($clientKey) {
            $apiClient = (new \BO\Zmsbackend\Apikey\Service\Apiclient())->readEntity($clientKey);
            if (!$apiClient || !isset($apiClient->accesslevel) || $apiClient->accesslevel == 'blocked') {
                throw new \BO\Zmsbackend\Process\Exception\ApiclientInvalid();
            }
            $entity->apiclient = $apiClient;
            $process = (new \BO\Zmsbackend\Process\Service\Process())->updateEntity(
                $entity,
                \App::$now,
                $resolveReferences,
                null,
                $workstation->getUseraccount()
            );
        } else {
            $process = (new \BO\Zmsbackend\Process\Service\Process())->updateEntity(
                $entity,
                \App::$now,
                $resolveReferences,
                null,
                $workstation->getUseraccount()
            );

            \BO\Zmsbackend\Log\Service\Log::writeProcessLog(
                "UPDATE (ProcessUpdate.php) $process ",
                \BO\Zmsbackend\Log\Service\Log::ACTION_CALLED,
                $process,
                $workstation->getUseraccount()
            );
            $this->syncOverviewCalendarFromProcess($entity, $process);
        }

        if ($initiator && $process->hasScopeAdmin() && $process->sendAdminMailOnUpdated()) {
            $config = (new \BO\Zmsbackend\Config\Service\Config())->readEntity();

            $mail = (new \BO\Zmsentities\Mail())
                    ->setTemplateProvider(new \BO\Zmsbackend\Helper\MailTemplateProvider($process))
                    ->toResolvedEntity($process, $config, 'updated', $initiator);
            (new \BO\Zmsbackend\Mail\Service\Mail())->writeInQueueWithAdmin($mail);
        }
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');

        return Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
    }

    protected function testProcessData($entity, bool $checkMailLimit = true)
    {
        $authCheck = (new \BO\Zmsbackend\Process\Service\Process())->readAuthKeyByProcessId($entity->id);

        if ($checkMailLimit && ! (new \BO\Zmsbackend\Process\Service\Process())->isAppointmentAllowedWithSameMail($entity)) {
            throw new \BO\Zmsbackend\Process\Exception\MoreThanAllowedAppointmentsPerMail();
        }

        /*if (! (new \BO\Zmsbackend\Process\Service\Process())->isAppointmentSlotCountAllowed($entity)) {
            throw new \BO\Zmsbackend\Process\Exception\MoreThanAllowedSlotsPerAppointment();
        } Should be moved to zmscitizenapi. */

        // Note: isServiceQuantityAllowed is only checked in ProcessPreconfirm/ProcessConfirm
        // to reduce DB queries on this frequently-called endpoint

        if (! $authCheck) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessNotFound();
        } elseif ($authCheck['authKey'] !== $entity->authKey) {
            throw new \BO\Zmsbackend\Process\Exception\AuthKeyMatchFailed();
        }
    }

    private function syncOverviewCalendarFromProcess(
        \BO\Zmsentities\Process $entity,
        \BO\Zmsentities\Process $process
    ): void {
        $appointment        = $entity->getFirstAppointment();
        $connectionTimezone = new \DateTimeZone(\BO\Zmsbackend\Connection\Select::$connectionTimezone);

        $startsAt = (new \DateTimeImmutable('@' . $appointment->date))
            ->setTimezone($connectionTimezone);

        $slotCount = (int) $appointment->slotCount;
        $scopeId   = (int) $appointment->scope->id;

        $slotTimeInMinutes = (int) $process->getFirstAppointment()->availability->slotTimeInMinutes;

        $endsAt = $startsAt->modify('+' . ($slotCount * $slotTimeInMinutes) . ' minutes');

        (new \BO\Zmsbackend\OverviewCalendar\Service\OverviewCalendar())->updateByProcess(
            (int) $entity->id,
            $scopeId,
            $startsAt,
            $endsAt
        );
    }
}
