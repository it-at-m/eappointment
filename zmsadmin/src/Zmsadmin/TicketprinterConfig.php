<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Slim\Render;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Collection\DepartmentList;
use BO\Zmsentities\Department;
use BO\Zmsentities\Exception\UserAccountMissingRights;

class TicketprinterConfig extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        if (!$workstation->getUseraccount()->hasPermissions(['ticketprinter'])) {
            throw new UserAccountMissingRights();
        }
        $scopeId = Validator::value($workstation['scope']['id'])->isNumber()->getValue();
        $config = \App::$http->readGetResult('/config/')->getEntity();
        $organisation = \App::$http->readGetResult(
            '/scope/' . $scopeId . '/organisation/',
            ['resolveReferences' => 5]
        )->getEntity();

        $source = \App::$http->readGetResult(
            '/source/',
            ['resolveReferences' => 2]
        )->getEntity();
        $requestList = (new RequestList())->addData($source->requests);

        $departments = new DepartmentList();

        foreach ($organisation->departments as $departmentData) {
            $department = (new Department($departmentData))->withCompleteScopeList();
            foreach ($department->scopes as $scope) {
                $scope->services = [];

                if (! isset($scope->provider->data)) {
                    continue;
                }

                foreach ($scope->provider->data['services'] as $serviceArray) {
                    $service = $requestList->getEntity($serviceArray['service']);

                    if (! $service) {
                        continue;
                    }

                    $scope->services[] = [
                        'id' => $scope->id . '-' . $service->id,
                        'name' => $service->name
                    ];
                }
            }
            $departments->addEntity($department);
        }
        return Render::withHtml(
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
