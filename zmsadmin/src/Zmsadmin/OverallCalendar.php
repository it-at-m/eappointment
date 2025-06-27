<?php

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Zmsdb\Request;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Department as DepartmentEntity;
use BO\Zmsentities\Collection\DepartmentList;

class OverallCalendar extends BaseController
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

        if (!$workstation->hasSuperUseraccount()) {
            throw new Exception\NotAllowed();
        }
        $scopeId = $workstation->scope->id ?? null;

        $departmentScopes = [];

        if ($scopeId) {
            $departmentApiResult = \App::$http->readGetResult(
                '/scope/' . $scopeId . '/department/',
                ['resolveReferences' => 2]
            );

            $data = $departmentApiResult ? $departmentApiResult->getEntity() : null;
            if ($data) {
                $department = new DepartmentEntity($data);
                $departmentScopes = $department->getScopeList();
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/overallCalendar.twig',
            array(
                'title' => 'Wochenkalender',
                'workstation' => $workstation,
                'scopeList' => $departmentScopes,
                'menuActive' => 'overallcalendar',
                'hideNavigation' => true,
            )
        );
    }
}
