<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process;

/**
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class ProcessPreconfirm extends BaseController
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

    protected function testProcessData($entity)
    {
        $authCheck = (new Process())->readAuthKeyByProcessId($entity->id);

        $this->checkIfAppointmentIsAllowedWithSameMail($entity);

        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authCheck['authKey'] != $entity->authKey && $authCheck['authName'] != $entity->authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        }
    }

    protected function checkIfAppointmentIsAllowedWithSameMail($entity)
    {
        if (empty($entity->getClients()) || empty($entity->getClients()->getFirst())) {
            return;
        }

        $maxAppointmentsPerMail = $entity->scope->getAppointmentsPerMail();
        $processes = (new Process())->readProcessListByMailAddress(
            $entity->getClients()->getFirst()->email,
            $entity->scope->id
        );
        $activeAppointments = 0;

        foreach ($processes as $process) {
            if (in_array($process->getStatus(), ['preconfirmed', 'confirmed'])) {
                $activeAppointments++;
            }
        }

        if ($maxAppointmentsPerMail > 0 && $activeAppointments > $maxAppointmentsPerMail) {
            throw new Exception\Process\MoreThanAllowedAppointmentsPerMail();
        }
    }
}
