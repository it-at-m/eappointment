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
        $workstation = \App::$http->readGetResult('/workstation/', [
            'resolveReferences' => 1,
            'gql' => Helper\GraphDefaults::getWorkstation()
        ])->getEntity();
        $workstationRequest = new \BO\Zmsclient\WorkstationRequests(\App::$http, $workstation);
        $selectedDateTime = static::readSelectedDateTime($args['date']);
        $scope = static::readSelectedScope($workstation, $workstationRequest, $args['id']);
        $processList = static::readProcessList($workstationRequest, $selectedDateTime);

        // rendering
        return \BO\Slim\Render::withHtml(
            $response,
            'page/scopeAppointmentsByDay.twig',
            array(
                'title' =>
                    'Termine für '
                    . $scope->contact['name']
                    . ' am '
                    . $selectedDateTime->format('d.m.Y'),
                'menuActive' => 'counter',
                'workstation' => $workstation,
                'date' => $selectedDateTime->format('Y-m-d'),
                'scope' => $scope,
                'clusterEnabled' => $workstation->isClusterEnabled(),
                'processList' => $processList,
            )
        );
    }

    public static function readSelectedDateTime($selectedDate)
    {
         return $selectedDate ? new \DateTimeImmutable($selectedDate) : \App::$now;
    }

    public static function readSelectedScope($workstation, $workstationRequest, $scopeId)
    {
        if ($workstation->getScope()->id != $scopeId) {
            $scope = \App::$http->readGetResult('/scope/' . $scopeId . '/', [
                'gql' => Helper\GraphDefaults::getScope()
            ])->getEntity();
            $workstationRequest->setDifferentScope($scope);
        }
        return $workstationRequest->getScope();
    }

    public static function readProcessList($workstationRequest, $selectedDateTime)
    {
        $processList = $workstationRequest->readProcessListByDate(
            $selectedDateTime,
            Helper\GraphDefaults::getProcess()
        );
        // data refinement
        return $processList
            ->toQueueList(\App::$now)
            ->withStatus(['confirmed', 'queued'])
            ->withSortedArrival()
            ->toProcessList();
    }
}
