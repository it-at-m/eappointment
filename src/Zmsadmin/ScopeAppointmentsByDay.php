<?php
/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

use BO\Zmsentities\Scope as Entity;
use BO\Mellon\Validator;

use \BO\Zmsentities\Collection\ProcessList;

/**
 * Handle requests concerning services
 */
class ScopeAppointmentsByDay extends BaseController
{

    /**
     *
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();

        $scopeId = $args['id'];
        $selectedDate = $args['date'];
        $scope = \App::$http->readGetResult('/scope/' . $scopeId . '/')->getEntity();
        $cluster = \App::$http->readGetResult('/scope/'. $scope->id .'/cluster/')->getEntity();
        $processList = new ProcessList();

        if (1 == $workstation->queue['clusterEnabled']) {
            $resultList = \App::$http
                        ->readGetResult(
                            '/cluster/'. $cluster->id .'/process/'. $selectedDate .'/',
                            ['resolveReferences' => 1]
                        )->getCollection();
        } else {
            $resultList = \App::$http
                        ->readGetResult(
                            '/scope/'. $scope->id .'/process/'. $selectedDate .'/',
                            ['resolveReferences' => 1]
                        )->getCollection();
        }
        $processList = ($resultList) ? $resultList : $processList;

        $selectedDateTime = new \DateTimeImmutable($selectedDate);
        $queueList = $processList
                   ->toQueueList($selectedDateTime)
                   ->withStatus(array('confirmed', 'queued', 'reserved'))
                   ->withSortedArrival();















        return \BO\Slim\Render::withHtml(
            $response,
            'page/scopeAppointmentsByDay.twig',
            array(
                'title' => 'Termine am Standort',
                'menuActive' => 'owner',
                'workstation' => $workstation,
                'date' => $selectedDate,
                'processList' => $queueList->toProcessList(),
            )
        );
    }
}
