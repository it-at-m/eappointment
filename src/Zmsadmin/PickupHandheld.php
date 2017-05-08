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
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $input = $request->getParsedBody();
        if (is_array($input) && array_key_exists('selectedprocess', $input)) {
            $selectedProcess = $this->readPickupProcess($input['selectedprocess']);
        }
        $cluster = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/cluster/')->getEntity();
        $processList = \App::$http->readGetResult('/workstation/process/pickup/', ['resolveReferences' => 1])
            ->getCollection();
        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();

        \BO\Slim\Render::withHtml(
            $response,
            'page/pickupHandheld.twig',
            array(
              'title' => 'Abholer verwalten',
              'menuActive' => 'pickup',
              'workstation' => $workstation->getArrayCopy(),
              'department' => $department,
              'cluster' => ($cluster) ? $cluster : null,
              'processList' => $processList,
              'selectedProcess' => $selectedProcess
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
        /*
        if ($process->hasId()) {
            return \BO\Slim\Render::redirect(
                'pickup_handheld',
                [],
                ['selectedprocess' => $process->id]
            );
        }
        */
    }
}
