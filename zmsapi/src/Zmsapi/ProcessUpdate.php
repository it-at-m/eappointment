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
        $this->testProcessData($entity, ! $initiator);

        \BO\Zmsdb\Connection\Select::setCriticalReadSession();
        $workstation = (new Helper\User($request));

        if ($slotType || $slotsRequired) {
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
            $process = (new Process)->updateEntity(
                $entity,
                \App::$now,
                $resolveReferences,
                null,
                $workstation->getUseraccount() ?? null
            );
        } else {
            $process = (new Process)->updateEntity(
                $entity,
                \App::$now,
                $resolveReferences,
                null,
                $workstation->getUseraccount() ?? null
            );
        }
       
        if ($initiator && $process->hasScopeAdmin() && $process->sendAdminMailOnUpdated()) {
            $config = (new Config())->readEntity();

            $mail = (new \BO\Zmsentities\Mail())
                    ->setTemplateProvider(new \BO\Zmsdb\Helper\MailTemplateProvider($process))
                    ->toResolvedEntity($process, $config, 'updated', $initiator);
            (new Mail())->writeInQueueWithAdmin($mail);
        }
        $message = Response\Message::create($request);
        $message->data = $process;
        
        $response = Render::withLastModified($response, time(), '0');

        return Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
    }

    protected function testProcessData($entity, bool $checkMailLimit = true)
    {
        $authCheck = (new Process())->readAuthKeyByProcessId($entity->id);

        if ($checkMailLimit && ! (new Process())->isAppointmentAllowedWithSameMail($entity)) {
            throw new Exception\Process\MoreThanAllowedAppointmentsPerMail();
        }

        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authCheck['authKey'] != $entity->authKey && $authCheck['authName'] != $entity->authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        }
    }
}
