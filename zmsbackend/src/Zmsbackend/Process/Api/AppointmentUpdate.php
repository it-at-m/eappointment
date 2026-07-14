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
 * @SuppressWarnings(Coupling)
 */
class AppointmentUpdate extends \BO\Zmsbackend\Api\BaseController
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

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $slotsRequired = Validator::param('slotsRequired')->isNumber()->getValue();
        $slotType = Validator::param('slotType')->isString()->getValue();
        $clientKey = Validator::param('clientkey')->isString()->getValue();
        $keepReserved = Validator::param('keepReserved')->isNumber()->setDefault(0)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $appointment = new \BO\Zmsentities\Appointment($input);
        $appointment->testValid();

        // get old process and check user rights if slottype or slotrequired is set
        $process = \BO\Zmsbackend\Process\Service\Process::init()->readEntity($args['id'], $args['authKey'], 1);
        if (! $process->hasId()) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessNotFound();
        }
        if ($slotType || $slotsRequired) {
            (new \BO\Zmsbackend\Helper\User($request))->checkPermissions('appointment');
            \BO\Zmsbackend\Helper\Matching::testCurrentScopeHasRequest($process);
        } elseif ($clientKey) {
            $apiClient = (new \BO\Zmsbackend\Apikey\Service\Apiclient())->readEntity($clientKey);
            if (!$apiClient || !isset($apiClient->accesslevel) || $apiClient->accesslevel == 'blocked') {
                throw new \BO\Zmsbackend\Process\Exception\ApiclientInvalid();
            }
            $slotType = $apiClient->accesslevel;
            if ($apiClient->accesslevel != 'intern') {
                $slotsRequired = 0;
                $slotType = $apiClient->accesslevel;
                $process = (new \BO\Zmsbackend\Process\Service\Process())->readSlotCount($process);
            }
            $process->apiclient = $apiClient;
        } else {
            $slotsRequired = 0;
            $slotType = 'public';
            $process = (new \BO\Zmsbackend\Process\Service\Process())->readSlotCount($process);
        }

        $process = \BO\Zmsbackend\Process\Service\Process::init()->writeEntityWithNewAppointment(
            $process,
            $appointment,
            \App::$now,
            $slotType,
            $slotsRequired,
            $resolveReferences,
            ($keepReserved == 1)
        );

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
