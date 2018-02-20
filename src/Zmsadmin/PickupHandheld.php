<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class PickupHandheld extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $input = $request->getParsedBody();
        if ($workstation->process && $workstation->process->hasId()) {
            $selectedProcess = $workstation->process;
        } else {
            $selectedProcess = (is_array($input) && array_key_exists('selectedprocess', $input)) ?
                $this->readPickupProcess($input['selectedprocess']) :
                null;
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/pickupHandheld.twig',
            array(
              'title' => 'Abholer verwalten',
              'workstation' => $workstation->getArrayCopy(),
              'menuActive' => 'pickup',
              'selectedProcess' => ($selectedProcess) ? $selectedProcess->getId() : null
            )
        );
    }

    protected function readPickupProcess($selectedProcess)
    {
        try {
            $process = \App::$http->readGetResult('/process/'. $selectedProcess .'/')->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template == 'BO\Zmsapi\Exception\Process\ProcessNotFound') {
                $process = new \BO\Zmsentities\Process([
                    'queue' => [
                        'number' => $selectedProcess
                    ]
                ]);
                $process = \App::$http->readPostResult('/process/status/pickup/', $process)->getEntity();
            }
        }
        return $process;
    }
}
