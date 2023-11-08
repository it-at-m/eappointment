<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;

use BO\Zmsentities\Department as DepartmentEntity;
use BO\Zmsentities\Collection\DepartmentList;

class TicketprinterConfig extends BaseController
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
        $scopeId = Validator::value($workstation['scope']['id'])->isNumber()->getValue();
        $config = \App::$http->readGetResult('/config/')->getEntity();
        $organisation = \App::$http->readGetResult(
            '/scope/'. $scopeId .'/organisation/',
            ['resolveReferences' => 3]
        )->getEntity();

        $departments = new DepartmentList();

        foreach ($organisation->departments as $departmentData) {
            $department = (new DepartmentEntity($departmentData))->withCompleteScopeList();
            $departments->addEntity($department);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/ticketprinterConfig.twig',
            array(
                'title' => 'Anmeldung an Warteschlange',
                'config' => $config->getArrayCopy(),
                'organisation' => $organisation->getArrayCopy(),
                'departments' => $departments->getArrayCopy(),
                'workstation' => $workstation,
                'menuActive' => 'ticketprinter'
            )
        );
    }
}
