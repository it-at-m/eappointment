<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Config;
use \BO\Zmsdb\Mail;
use BO\Mellon\Validator;
use \BO\Zmsdb\Process;

/**
 * @SuppressWarnings(Coupling)
 * @return String
 */
class ProcessUpdate extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @SuppressWarnings(Complexity)
     * @return String
     */
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
        $this->testProcessData($entity);

        \BO\Zmsdb\Connection\Select::setCriticalReadSession();

        if ($slotType || $slotsRequired) {
            $workstation = (new Helper\User($request))->checkRights();
            $process = Process::init()->updateEntityWithSlots(
                $entity,
                \App::$now,
                $slotType,
                $slotsRequired,
                $resolveReferences,
                $workstation->getUseraccount()
            );
            Helper\Matching::testCurrentScopeHasRequest($process);
        } elseif ($clientKey) {
            $apiClient = (new \BO\Zmsdb\Apiclient)->readEntity($clientKey);
            if (!$apiClient || !isset($apiClient->accesslevel) || $apiClient->accesslevel == 'blocked') {
                throw new Exception\Process\ApiclientInvalid();
            }
            $entity->apiclient = $apiClient;
            $process = (new Process)->updateEntity($entity, \App::$now, $resolveReferences);
        } else {
            $process = (new Process)->updateEntity($entity, \App::$now, $resolveReferences);
        }
       
        if ($initiator && $process->hasScopeAdmin() && $process->sendAdminMailOnUpdated()) {
            $config = (new Config())->readEntity();
            $mail = (new \BO\Zmsentities\Mail())->toResolvedEntity($process, $config, 'updated', $initiator);
            (new Mail())->writeInQueueWithAdmin($mail);
        }
        $message = Response\Message::create($request);
        $message->data = $process;
        
        $response = Render::withLastModified($response, time(), '0');

        return Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
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

        var_dump($entity->getClients()->getFirst());
        $maxAppointmentsPerMail = $entity->scope->getAppointmentsPerMail();
        var_dump($maxAppointmentsPerMail);
        $processes = (new Process())->readProcessListByMailAddress(
            $entity->getClients()->getFirst()->email,
            $entity->scope->id
        );
        $activeAppointments = 0;

        var_dump(count($processes));
        foreach ($processes as $process) {
            var_dump('====');
            var_dump($process->getStatus());
            if ($process->getStatus() === 'confirmed') {
                $activeAppointments++;
            }
        }

        var_dump($activeAppointments);
        if ($maxAppointmentsPerMail > 0 && $activeAppointments > $maxAppointmentsPerMail) {
            throw new Exception\Process\MoreThanAllowedAppointmentsPerMail();
        }
    }
}
