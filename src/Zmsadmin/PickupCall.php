<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Mellon\Validator;

/**
  *
  */
class PickupCall extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $inputNumber = Validator::value($args['id'])->isNumber()->getValue();
        $process = $this->readSelectedProcess($workstation, $inputNumber);
        $workstation->process = \App::$http->readPostResult('/process/status/pickup/', $process)->getEntity();

        return \BO\Slim\Render::withHtml(
            $response,
            'block/pickup/called.twig',
            array(
                'workstation' => $workstation            )
        );
    }

    protected function readSelectedProcess($workstation, $inputNumber)
    {
        $isWithAppointment = (5 < strlen((string)$inputNumber));
        try {
            if ($isWithAppointment) {
                $process = \App::$http
                    ->readGetResult('/process/'. $inputNumber .'/')
                    ->getEntity();
                $workstation->testMatchingProcessScope($workstation->getScopeList(), $process);
            } else {
                $process = \App::$http
                    ->readGetResult('/scope/'. $workstation->scope['id'] .'/queue/'. $inputNumber .'/')
                    ->getEntity();
            }
        } catch (\BO\Zmsclient\Exception $exception) {
            /*
            if ($exception->template == 'BO\Zmsapi\Exception\Process\ProcessNotFound') {
                $process = new \BO\Zmsentities\Process([
                    'queue' => [
                        'number' => $processId
                    ]
                ]);
                $process = \App::$http->readPostResult('/process/status/pickup/', $process)->getEntity();
            }
            */
            throw $exception;
        }
        return $process;
    }
}
