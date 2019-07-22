<?php
/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

class ScopeAppointmentsByDay extends BaseController
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
        //parameters
        $scopeId = $args['id'];
        $selectedDate = $args['date'];
        $selectedDateTime = $selectedDate ? new \DateTimeImmutable($selectedDate) : \App::$now;
        
        // HTTP requests
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $workstationRequest = new \BO\Zmsclient\WorkstationRequests(\App::$http, $workstation);
        if ($workstation->getScope()->id != $scopeId) {
            $scope = \App::$http->readGetResult('/scope/' . $scopeId . '/')->getEntity();
            $workstationRequest->setDifferentScope($scope);
        }
        $processList = $workstationRequest->readProcessListByDate($selectedDateTime);

        // data refinement
        $visibleProcessList = $processList
            ->toQueueList(\App::$now)
            ->withStatus(['confirmed', 'queued'])
            ->withSortedArrival()
            ->toProcessList();

        // rendering
        return \BO\Slim\Render::withHtml(
            $response,
            'page/scopeAppointmentsByDay.twig',
            array(
                'title' =>
                    'Termine für '
                    . $workstationRequest->getScope()->contact['name']
                    . ' am '
                    . $selectedDateTime->format('d.m.Y'),
                'menuActive' => 'counter',
                'workstation' => $workstation,
                'date' => $selectedDate,
                'scope' => $workstationRequest->getScope(),
                'clusterEnabled' => $workstation->isClusterEnabled(),
                'processList' => $visibleProcessList,
            )
        );
    }
}
