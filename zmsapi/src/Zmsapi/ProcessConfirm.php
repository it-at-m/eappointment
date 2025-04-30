<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Process;
use BO\Zmsdb\Mail;
use BO\Zmsdb\Config;
use BO\Mellon\Validator;

/**
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class ProcessConfirm extends BaseController
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
        \BO\Zmsdb\Connection\Select::setCriticalReadSession();

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(3)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Process($input);
        $entity->testValid();
        $this->testProcessData($entity);

        $userAccount = (new Helper\User($request))->readWorkstation()->getUseraccount();
        $process = (new Process())->readEntity($entity->id, $entity->authKey);
        if ('preconfirmed' != $process->status && 'reserved' != $process->status) {
            throw new Exception\Process\ProcessNotPreconfirmedAnymore();
        }
        $this->updateOverallCalendar($process);
        $process = (new Process())->updateProcessStatus(
            $process,
            'confirmed',
            \App::$now,
            $resolveReferences,
            $userAccount
        );
        $this->writeMails($request, $process);
        $message = Response\Message::create($request);
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
            $config = (new Config())->readEntity();
            $mail = (new \BO\Zmsentities\Mail())
            ->setTemplateProvider(new \BO\Zmsdb\Helper\MailTemplateProvider($process))
            ->toResolvedEntity($process, $config, 'appointment', $initiator);
            (new Mail())->writeInQueueWithAdmin($mail, \App::$now);
        }
    }

    private function updateOverallCalendar(\BO\Zmsentities\Process $process): void
    {
        $appointment = null;
        foreach ($process->appointments as $appointment) {
            break;
        }
        if (!$appointment) {
            return;
        }

        $scopeId = (int) $appointment->scope->id;

        $time = (new \DateTimeImmutable('@' . $appointment->date))
            ->setTimezone(new \DateTimeZone(\BO\Zmsdb\Connection\Select::$connectionTimezone))
            ->format('Y-m-d H:i:00');

        $duration = 0;
        foreach ($process->requests as $req) {
            if (!empty($req['data']['duration'])) {
                $duration += (int) $req['data']['duration'];
            }
        }
        $duration = $duration ?: 5;
        $slotUnits = (int) ceil($duration / 5);

        (new \BO\Zmsdb\OverallCalendar())->book(
            $scopeId,
            $time,
            $process->id,
            $slotUnits
        );
    }

    protected function testProcessData($entity)
    {
        $authCheck = (new Process())->readAuthKeyByProcessId($entity->id);
        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authCheck['authKey'] != $entity->authKey && $authCheck['authName'] != $entity->authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        }
    }
}
