<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process;
use \BO\Zmsdb\ProcessStatusFree;

use \BO\Zmsentities\Collection\AppointmentList;

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
        \BO\Zmsdb\Connection\Select::setClusterWideCausalityChecks();
        \BO\Zmsdb\Connection\Select::getWriteConnection();

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();

        $processOld = Process::init()->readEntity($args['id'], $args['authKey'], 1);
 
        $this->testProcessData($processOld);
        $this->updateWithNewProcessId($processOld);
        $processNew = $this->updateWithNewAppointment($input, $processOld, $resolveReferences);

        $message = Response\Message::create($request);
        $message->data = $processNew;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcessData($entity)
    {
        $authCheck = Process::init()->readAuthKeyByProcessId($entity->id);
        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        }
    }

    protected function updateWithNewProcessId($processOld)
    {
        $processReserved = clone $processOld;
        $processReserved->status = 'reserved';
        return Process::init()->updateWithNewProcessId($processReserved, \App::$now);
    }

    protected function updateWithNewAppointment($input, $processOld, $resolveReferences)
    {
        $appointment = new \BO\Zmsentities\Appointment($input);
        $appointment->testValid();
        
        $processNew = clone $processOld;
        $processNew->appointments = (new AppointmentList())->addEntity($appointment);
        $freeProcessList = ProcessStatusFree::init()->readFreeProcesses($processNew->toCalendar(), \App::$now);
        $slotList = (new \BO\Zmsdb\Slot)->readByAppointment($appointment);
        if (! $freeProcessList->getAppointmentList()->hasAppointment($appointment) || ! $slotList) {
            throw new Exception\Process\ProcessReserveFailed();
        }
        return Process::init()->updateEntity($processNew, \App::$now, $resolveReferences);
    }
}
