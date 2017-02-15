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
    const SECURE_TOKEN = 'a9b215f1-e460-490c-8a0b-6d42c274d5e4';
    /**
     * @return String
     */
    public function readResponse(
        \psr\http\message\requestinterface $request,
        \psr\http\message\responseinterface $response,
        array $args
    ) {

        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $scopeId = $workstation['scope']['id'];
        $entityId = Validator::value($scopeId)->isNumber()->getValue();

        $config = \App::$http->readGetResult('/config/', [], static::SECURE_TOKEN)->getEntity();

        $entity = \App::$http->readGetResult(
            '/scope/'. $entityId .'/organisation/',
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
                'title' => 'Anmeldung an Warteschlange',
                'config' => $config,
                'organisation' => $entity->getArrayCopy(),
                'departments' => $departments->getArrayCopy(),
                'menuActive' => 'calldisplay'
            )
        );
    }
}
