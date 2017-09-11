<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Mellon\Validator;

class WorkstationProcessCalled extends BaseController
{
    /**
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');

        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $workstationInfo = Helper\WorkstationInfo::getInfoBoxData($workstation);
        if (! $workstation->process->getId() && ! $workstation->process->queue->callTime) {
            $processId = Validator::value($args['id'])->isNumber()->getValue();
            $process = new \BO\Zmsentities\Process(['id' => $processId]);
            $workstation = \App::$http->readPostResult('/workstation/process/called/', $process)->getEntity();
        }
        $workstation->testMatchingProcessScope((new Helper\ClusterHelper($workstation))->getScopeList());

        $excludedIds = $validator->getParameter('exclude')->isString()->getValue();
        if ($excludedIds) {
            $exclude = explode(',', $excludedIds);
        }
        $exclude[] = $workstation->process['id'];

        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/called.twig',
            array(
                'title' => 'Sachbearbeiter',
                'workstation' => $workstation,
                'workstationInfo' => $workstationInfo,
                'hasProcessCalled' => ($workstation->process['id'] != $processId),
                'menuActive' => 'workstation',
                'exclude' => join(',', $exclude)
            )
        );
    }
}
