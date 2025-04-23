<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Zmsentities\Department;
use BO\Zmsentities\Collection\DepartmentList;

class Calldisplay extends BaseController
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
        $scopeId = $workstation['scope']['id'];
        $entityId = Validator::value($scopeId)->isNumber()->getValue();

        $config = \App::$http->readGetResult('/config/')->getEntity();

        $entity = \App::$http->readGetResult(
            '/scope/' . $entityId . '/organisation/',
            ['resolveReferences' => 3]
        )->getEntity();

        $departments = new DepartmentList();

        foreach ($entity->departments as $departmentData) {
            $department = (new Department($departmentData))->withCompleteScopeList();
            $departments->addEntity($department);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/calldisplay.twig',
            array(
                'title' => 'Aufrufanzeige',
                'config' => $config->getArrayCopy(),
                'organisation' => $entity->getArrayCopy(),
                'departments' => $departments->getArrayCopy(),
                'workstation' => $workstation,
                'menuActive' => 'calldisplay'
            )
        );
    }
}
