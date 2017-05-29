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

use Helper\AppointmentsByDayHelper;

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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();

        $scopeId = $args['id'];
        $scope = \App::$http->readGetResult('/scope/' . $scopeId . '/')->getEntity();
        $selectedDate = $args['date'];

        $selectedDateTime = new \DateTimeImmutable($selectedDate);
        $queueList = Helper\AppointmentsByDayHelper::getAppointmentsByDayForScope(
            $workstation,
            $scope,
            $selectedDate
        );
        return \BO\Slim\Render::withHtml(
            $response,
            'page/scopeAppointmentsByDay.twig',
            array(
                'title' => 'Termine für ' . $scope->contact['name'] . ' am ' . $selectedDateTime->format('d.m.Y'),
                'menuActive' => 'owner',
                'workstation' => $workstation,
                'date' => $selectedDate,
                'scope' => $scope,
                'processList' => $queueList->toProcessList(),
            )
        );
    }
}
