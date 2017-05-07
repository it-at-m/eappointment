<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class CounterQueueInfo extends BaseController
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
        $validator = $request->getAttribute('validator');
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $ghostWorkstation= $validator->getParameter('ghostworkstationcount')->isNumber()->getValue();
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();

        if ($ghostWorkstation) {
            $scope = $workstation->getScope();
            $scope->setStatusQueue('ghostWorkstationCount', $ghostWorkstation);
            \App::$http->readPostResult("/scope/$scope->id/ghostworkstation/", $scope)->getEntity();
        }

        $workstationInfo = Helper\WorkstationInfo::getInfoBoxData($workstation, $selectedDate);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/queue/info.twig',
            array(
                'workstation' => $workstation,
                'workstationInfo' => $workstationInfo,
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
            )
        );
    }
}
