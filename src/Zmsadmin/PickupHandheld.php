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
        $selectedProcess = (is_array($input) && array_key_exists('selectedprocess', $input)) ?
            $this->readPickupProcess($input['selectedprocess']) :
            null;
        $processList = \App::$http->readGetResult('/workstation/process/pickup/', ['resolveReferences' => 1])
            ->getCollection();
        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/pickupHandheld.twig',
            array(
              'title' => 'Abholer verwalten',
              'menuActive' => 'pickup',
              'workstation' => $workstation->getArrayCopy(),
              'department' => $department,
              'cluster' => (new Helper\ClusterHelper($workstation))->getEntity(),
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
    }
}
