<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Department as Entity;
use BO\Mellon\Validator;

class OrganisationAddDepartment extends BaseController
{
    /**
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $input = $request->getParsedBody();
        $organisationId = Validator::value($args['id'])->isNumber()->getValue();
        $organisation = \App::$http->readGetResult('/organisation/' . $organisationId . '/')->getEntity();
        if ($request->getMethod() === 'POST') {
            $input = $this->withCleanupLinks($input);
            $input = $this->withCleanupDayoffs($input);
            $entity = (new Entity($input))->withCleanedUpFormData();
            $entity->dayoff = $entity->getDayoffList()->withTimestampFromDateformat();
            $department = \App::$http->readPostResult('/organisation/'. $organisationId .'/department/', $entity)
                ->getEntity();
            return \BO\Slim\Render::redirect(
                'department',
                array(
                    'id' => $department->id
                ),
                array(
                    'success' => 'department_created'
                )
            );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/department.twig',
            array(
                'title' => 'Standort',
                'action' => 'add',
                'menuActive' => 'owner',
                'workstation' => $workstation,
                'organisation' => $organisation
            )
        );
    }

    protected function withCleanupLinks(array $input)
    {
        $links = $input['links'];

        $input['links'] = array_filter($links, function ($link) {
            return !($link['name'] === '' && $link['url'] == '');
        });

        return $input;
    }

    protected function withCleanupDayoffs(array $input)
    {
        $dayoffs = $input['dayoff'];
        $input['dayoff'] = array_filter($dayoffs, function ($dayoff) {
            return !($dayoff['name'] === '');
        });

        return $input;
    }
}
