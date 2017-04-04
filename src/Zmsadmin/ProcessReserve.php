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
use BO\Zmsentities\Helper\ProcessFormValidation as FormValidation;

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
            $validationList = FormValidation::fromAdminParameters($workstation->scope['preferences']);
            if ($validationList->hasFailed()) {
                return \BO\Slim\Render::withJson(
                    $response,
                    $validationList->getStatus(),
                    428
                );
            }
            $process->scope = $workstation->scope;
            $process->addRequests('dldb', implode(',', $input['process_requests']));
            $process->addAppointment(
                (new \BO\Zmsentities\Appointment())
                    ->setDateByString($selectedDate .' '. str_replace('-', ':', $selectedTime), 'Y-m-d H:i')
                    ->addScope($workstation->scope['id'])
            );
            $process->reminderTimestamp = $input['headsUpTime'];
            $reservedProcess = \App::$http->readPostResult('/process/status/reserved/', $process)->getEntity();
            if ($reservedProcess) {
                $process = \App::$http->readGetResult(
                    '/process/'. $reservedProcess->id .'/'. $reservedProcess->authKey .'/'
                )->getEntity();
                return $this->writeProcessConfirmation($process, $input);
            }
        }
        return false;
    }

    private function writeProcessConfirmation(\BO\Zmsentities\Process $process, $input)
    {
        $process = \App::$http->readPostResult('/process/status/confirmed/', $process)->getEntity();
        if (array_key_exists('sendMailConfirmation', $input)) {
            \App::$http->readPostResult(
                '/process/'. $process->id .'/'. $process->authKey .'/confirmation/mail/',
                $process
            );
        }
        if (array_key_exists('sendNotificationConfirmation', $input)) {
            \App::$http->readPostResult(
                '/process/'. $process->id .'/'. $process->authKey .'/confirmation/notification/',
                $process
            );
        }
        return $process;
    }
}
