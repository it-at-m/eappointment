<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Schema\Loader;
use BO\Zmsentities\Useraccount as Entity;

class UseraccountAdd extends BaseController
{
    /**
     * @SuppressWarnings(unused)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $confirmSuccess = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $selectedDepartment = $request->getAttribute('validator')->getParameter('department')->isNumber()->getValue();
        $ownerList = \App::$http->readGetResult('/owner/', ['resolveReferences' => 2])->getCollection();

        $input = $request->getParsedBody();
        if ($request->getMethod() === 'POST') {
            $input['password'] = $input['changePassword'][0];
            $result = $this->writeNewEntity($input);
            if ($result instanceof Entity) {
                return \BO\Slim\Render::redirect(
                    'useraccountEdit',
                    array(
                        'loginname' => $result->id
                    ),
                    array(
                        'success' => 'useraccount_added'
                    )
                );
            }
        }

        $config = \App::$http->readGetResult('/config/', [], \App::CONFIG_SECURE_TOKEN)->getEntity();
        $allowedProviderList = explode(',', $config->getPreference('oidc', 'provider'));

        return \BO\Slim\Render::withHtml(
            $response,
            'page/useraccountEdit.twig',
            [
                'ownerList' => $ownerList->toDepartmentListByOrganisationName(),
                'workstation' => $workstation,
                'success' => $confirmSuccess,
                'action' => 'add',
                'title' => 'Nutzer: Einrichtung und Administration',
                'menuActive' => 'useraccount',
                'exception' => (isset($result)) ? $result : null,
                'userAccount' => (isset($result)) ? $input : null,
                'selectedDepartment' => $selectedDepartment,
                'oidcProviderList' => array_filter($allowedProviderList),
                'metadata' => $this->getSchemaConstraintList(Loader::asArray(Entity::$schema))
            ]
        );
    }

    protected function writeNewEntity($input)
    {
        $entity = new Entity($input);
        if (isset($input['oidcProvider']) && '' != $input['oidcProvider']) {
            $entity->id = $entity->id . '@' . $input['oidcProvider'];
        }
        $entity = $entity->withCleanedUpFormData(true);
        try {
            $entity = \App::$http->readPostResult('/useraccount/', $entity)->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            $template = Helper\TwigExceptionHandler::getExceptionTemplate($exception);
            if (
                '' != $exception->template
                && \App::$slim->getContainer()->get('view')->getLoader()->exists($template)
            ) {
                return [
                    'template' => $template,
                    'include' => true,
                    'data' => $exception->data
                ];
            }
            throw $exception;
        }
        return $entity;
    }
}
