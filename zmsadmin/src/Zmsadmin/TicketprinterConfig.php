<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;

use \BO\Zmsdb\Request;
use BO\Zmsentities\Collection\RequestList;
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
            ['resolveReferences' => 5]
        )->getEntity();

        $source = \App::$http->readGetResult(
            '/source/',
            ['resolveReferences' => 2]
        )->getEntity();
        $requestList = (new RequestList())->addData($source->requests);

        $departments = new DepartmentList();

        foreach ($organisation->departments as $departmentData) {
            $department = (new DepartmentEntity($departmentData))->withCompleteScopeList();
            foreach ($department->scopes as $scope) {
                $scope->services = [];

                if (! isset($scope->provider->data)) {
                    continue;
                }

                foreach ($scope->provider->data['services'] as $serviceArray) {
                    $service = $requestList->getEntity($serviceArray['service']);
                    $scope->services[] = [
                        'id' => $scope->id . '-' . $service->id,
                        'name' => $service->name
                    ];
                }
            }
            $departments->addEntity($department);
        }

        error_log("hello");

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
