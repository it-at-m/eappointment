<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Slim\Render;

/**
 * Delete a process
 */
class ProcessReserve extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();

        $selectedDate = Validator::value($args['date'])->isString()->getValue();
        $selectedTime = Validator::value($args['time'])->isString()->getValue();
        $input = $request->getParsedBody();

        if ($selectedDate && $selectedTime && is_array($input)) {
            $process = new \BO\Zmsentities\Process($input);
            $process->scope = $workstation->scope;
            $process->addRequests('dldb', implode(',', $input['process_requests']));
            $process->addAppointment(
                (new \BO\Zmsentities\Appointment())
                    ->setDateByString($selectedDate .' '. str_replace('-', ':', $selectedTime), 'Y-m-d H:i')
                    ->addScope($workstation->scope['id'])
            );
            $process->reminderTimestamp = $input['headsUpTime'];
            $reservedProcess = \App::$http->readPostResult('/process/status/reserved/', $process)->getEntity();
            error_log(var_export($reservedProcess->id, 1));
            if ($reservedProcess) {
                return $reservedProcess;
            }
        }
        return false;
    }
}
