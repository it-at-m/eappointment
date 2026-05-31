<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Process;

/**
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class ProcessPreconfirm extends BaseController
{
    /**
     * @SuppressWarnings (Param)
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        \BO\Zmsdb\Connection\Select::setCriticalReadSession();

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(3)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Process($input);
        $entity->testValid();
        $this->testProcessData($entity);

        $userAccount = (new Helper\User($request))->readWorkstation()->getUseraccount();
        $process = (new Process())->readEntity($entity->id, $entity->authKey, 2);

        //$this->validateProcessLimits($process); Should be moved to zmscitizenapi.
        if ('reserved' != $process->status) {
            throw new Exception\Process\ProcessNotReservedAnymore();
        }

        $process = (new Process())->updateProcessStatus(
            $process,
            'preconfirmed',
            \App::$now,
            $resolveReferences,
            $userAccount
        );
        $message = Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    /**
     * @return void
     */
    protected function testProcessData(\BO\Zmsentities\Process $entity)
    {
        $authCheck = (new Process())->readAuthKeyByProcessId($entity->id);

        if (! (new Process())->isAppointmentAllowedWithSameMail($entity)) {
            throw new Exception\Process\MoreThanAllowedAppointmentsPerMail();
        }

        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authCheck['authKey'] !== $entity->authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        }
    }

    /**
     * @return void
     */
    protected function validateProcessLimits(\BO\Zmsentities\Process $process)
    {
        if (! (new Process())->isAppointmentSlotCountAllowed($process)) {
            throw new Exception\Process\MoreThanAllowedSlotsPerAppointment();
        }

        if (! (new Process())->isServiceQuantityAllowed($process)) {
            throw new Exception\Process\MoreThanAllowedQuantityPerService();
        }
    }
}
