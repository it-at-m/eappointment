<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Process\Service\Process;

/**
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class ProcessPreconfirm extends \BO\Zmsbackend\Api\BaseController
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
        if ('reserved' != $process->status) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessNotReservedAnymore();
        }

        $process = (new \BO\Zmsbackend\Process\Service\Process())->updateProcessStatus(
            $process,
            'preconfirmed',
            \App::$now,
            $resolveReferences,
            $userAccount
        );
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcessData($entity)
    {
        $authCheck = (new \BO\Zmsbackend\Process\Service\Process())->readAuthKeyByProcessId($entity->id);

        if (! (new \BO\Zmsbackend\Process\Service\Process())->isAppointmentAllowedWithSameMail($entity)) {
            throw new \BO\Zmsbackend\Process\Exception\MoreThanAllowedAppointmentsPerMail();
        }

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
