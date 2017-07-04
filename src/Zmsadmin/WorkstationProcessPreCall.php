<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Mellon\Validator;

class WorkstationProcessPreCall extends BaseController
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
        $workstation->testDepartmentList();
        $workstationInfo = Helper\WorkstationInfo::getInfoBoxData($workstation);
        $processId = Validator::value($args['id'])->isNumber()->getValue();
        $process = \App::$http->readGetResult('/process/'. $processId .'/')->getEntity();

        $excludedIds = $validator->getParameter('exclude')->isString()->getValue();
        if ($excludedIds) {
            $exclude = explode(',', $excludedIds);
        }
        $exclude[] = $process['id'];

        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/precall.twig',
            array(
                'title' => 'Sachbearbeiter',
                'workstation' => $workstation,
                'workstationInfo' => $workstationInfo,
                'menuActive' => 'workstation',
                'process' => $process,
                'exclude' => join(',', $exclude)
            )
        );
    }
}
