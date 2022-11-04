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
 * @SuppressWarnings(Coupling)
 */
class AppointmentUpdate extends BaseController
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

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $slotsRequired = Validator::param('slotsRequired')->isNumber()->getValue();
        $slotType = Validator::param('slotType')->isString()->getValue();
        $clientKey = Validator::param('clientkey')->isString()->getValue();
        $keepReserved = Validator::param('keepReserved')->isNumber()->setDefault(0)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $appointment = new \BO\Zmsentities\Appointment($input);
        $appointment->testValid();

        // get old process and check user rights if slottype or slotrequired is set
        $process = Process::init()->readEntity($args['id'], $args['authKey'], 1);
        if (! $process->hasId()) {
            throw new Exception\Process\ProcessNotFound();
        }
        if ($slotType || $slotsRequired) {
            (new Helper\User($request))->checkRights();
            Helper\Matching::testCurrentScopeHasRequest($process);
        } elseif ($clientKey) {
            $apiClient = (new \BO\Zmsdb\Apiclient)->readEntity($clientKey);
            if (!$apiClient || !isset($apiClient->accesslevel) || $apiClient->accesslevel == 'blocked') {
                throw new Exception\Process\ApiclientInvalid();
            }
            $slotType = $apiClient->accesslevel;
            if ($apiClient->accesslevel != 'intern') {
                $slotsRequired = 0;
                $slotType = $apiClient->accesslevel;
                $process = (new Process)->readSlotCount($process);
            }
            $process->apiclient = $apiClient;
        } else {
            $slotsRequired = 0;
            $slotType = 'public';
            $process = (new Process)->readSlotCount($process);
        }

        $process = Process::init()->writeEntityWithNewAppointment(
            $process,
            $appointment,
            \App::$now,
            $slotType,
            $slotsRequired,
            $resolveReferences,
            ($keepReserved == 1)
        );

        $message = Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
