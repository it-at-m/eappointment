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
        $result = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 3]);
        if (!$result) {
            throw new \Exception('Unable to retrieve workstation data');
        }
        $workstation = $result->getEntity();
        if (!$workstation->getUseraccount()->hasRights(['useraccount'])) {
            throw new \BO\Zmsentities\Exception\UserAccountMissingRights();
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/overallCalendar.twig',
            array(
                'title' => 'Wochenkalender',
                'workstation' => $workstation,
                'menuActive' => 'overallcalendar',
                'hideNavigation' => true,
            )
        );
    }
}
