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
        if (is_array($input) && array_key_exists('save', $input)) {
            $input = $this->cleanupLinks($input);
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
                'workstation' => $workstation
            )
        );
    }

    protected function cleanupLinks(array $input)
    {
        $links = $input['links'];

        $input['links'] = array_filter($links, function ($link) {
            return !($link['name'] === '' && $link['url'] == '');
        });

        return $input;
    }
}
