<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Exception\UserAccountMissingRights;
use BO\Zmsentities\Department as Entity;
use BO\Mellon\Validator;

class OrganisationAddDepartment extends BaseController
{
    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        if (!$workstation->getUseraccount()->hasPermissions(['department'])) {
            throw new UserAccountMissingRights();
        }
        $input = $request->getParsedBody();
        $organisationId = Validator::value($args['id'])->isNumber()->getValue();
        $organisation = \App::$http->readGetResult('/organisation/' . $organisationId . '/')->getEntity();
        if ($request->getMethod() === 'POST') {
            $input = $this->withCleanupLinks($input);
            $entity = (new Entity($input))->withCleanedUpFormData();
            $department = \App::$http->readPostResult('/organisation/' . $organisationId . '/department/', $entity)
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
                'title' => 'Behörde einrichten',
                'action' => 'add',
                'menuActive' => 'owner',
                'workstation' => $workstation,
                'organisation' => $organisation
            )
        );
    }

    /**
     * @return (array|mixed)[]
     *
     */
    protected function withCleanupLinks(array $input): array
    {
        $links = $input['links'];

        $input['links'] = array_filter($links, function ($link) {
            return !($link['name'] === '' && $link['url'] == '');
        });

        return $input;
    }
}
