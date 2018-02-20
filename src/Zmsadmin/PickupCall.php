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
        $processId = Validator::value($args['id'])->isNumber()->getValue();
        $process = new \BO\Zmsentities\Process(['id' => $processId]);

        try {
            $workstation = \App::$http->readPostResult('/workstation/process/called/', $process)->getEntity();
        } catch (\BO\Zmsapi\Exception\Workstation\WorkstationHasAssignedProcess $exception) {
            $exception->data = $workstation;
            throw $exception;
        }

        $process = \App::$http->readPostResult('/process/status/pickup/', $workstation->process)->getEntity();
        $workstation->testMatchingProcessScope((new Helper\ClusterHelper($workstation))->getScopeList());

        return \BO\Slim\Render::withHtml(
            $response,
            'block/pickup/called.twig',
            array(
                'workstation' => $workstation,
                'hasProcessCalled' => ($workstation->process['id'] != $processId)
            )
        );
    }
}
