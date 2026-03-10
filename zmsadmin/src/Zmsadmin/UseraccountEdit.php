<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin;

use BO\Zmsentities\Schema\Loader;
use BO\Zmsentities\Useraccount as Entity;
use BO\Mellon\Validator;
use BO\Zmsclient\Auth;

class UseraccountEdit extends BaseController
{
    /**
     *
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $userAccountName = Validator::value($args['loginname'])->isString()->getValue();
        $confirmSuccess = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $userAccount = \App::$http->readGetResult('/useraccount/' . $userAccountName . '/')->getEntity();
        $ownerList = \App::$http->readGetResult('/owner/', ['resolveReferences' => 2])->getCollection();

        if ($request->getMethod() === 'POST') {
            $input = $request->getParsedBody();
            $result = $this->writeUpdatedEntity($input, $userAccountName);
            if ($result instanceof Entity) {
                return \BO\Slim\Render::redirect(
                    'useraccountEdit',
                    array('loginname' => $result->id),
                    array('success' => 'useraccount_saved')
                );
            }
        }

        $config = \App::$http->readGetResult('/config/', [], \App::CONFIG_SECURE_TOKEN)->getEntity();
        $allowedProviderList = explode(',', $config->getPreference('oidc', 'provider') ?? '');

        return \BO\Slim\Render::withHtml(
            $response,
            'page/useraccountEdit.twig',
            [
                'debug' => \App::DEBUG,
                'userAccount' => $userAccount,
                'success' => $confirmSuccess,
                'ownerList' => $ownerList ? $ownerList->toDepartmentListByOrganisationName() : [],
                'workstation' => $workstation,
                'title' => 'Nutzer: Einrichtung und Administration','menuActive' => 'useraccount',
                'exception' => (isset($result)) ? $result : null,
                'metadata' => $this->getSchemaConstraintList(Loader::asArray(Entity::$schema)),
                'oidcProviderList' => array_filter($allowedProviderList),
                'isFromOidc' => in_array($userAccount->getOidcProviderFromName(), $allowedProviderList)
            ]
        );
    }

    protected function writeUpdatedEntity($input, $userAccountName)
    {
        $roles = isset($input['roles']) && is_array($input['roles']) ? $input['roles'] : [];

        // Validate role selection (e.g. required, disallowed combinations)
        $validationResult = $this->validateRoles($roles);
        if (is_array($validationResult) && isset($validationResult['data'])) {
            // Return structured validation error for Twig rendering
            return $validationResult;
        }
        // Normalized roles from validator
        $roles = $validationResult;

        $input['roles'] = $roles;

        $entity = (new Entity($input))->withCleanedUpFormData();
        // TODO: Remove the password fields when password authentication is removed in the future
        $entity->setPassword($input);
        return $this->handleEntityWrite(function () use ($entity, $userAccountName) {
            return \App::$http
                ->readPostResult('/useraccount/' . $userAccountName . '/', $entity)
                ->getEntity();
        });
    }

    /**
     * Validate selected roles and normalize the list.
     *
     * @param array $roles
     * @return array Normalized role list on success, or an error-structure array on failure
     */
    protected function validateRoles(array $roles)
    {
        // Normalize roles (deduplicate, remove empty values)
        $normalized = [];
        foreach ($roles as $role) {
            if (!is_string($role)) {
                continue;
            }
            $role = trim($role);
            if ('' === $role) {
                continue;
            }
            $normalized[$role] = true;
        }
        $normalized = array_keys($normalized);

        $errors = [];

        // Require at least one role
        if (0 === count($normalized)) {
            $errors[] = 'Es muss mindestens eine Rolle ausgewählt werden.';
        }

        // Forbid combining system_admin with any other role to keep semantics clear
        if (in_array('system_admin', $normalized, true) && count($normalized) > 1) {
            $errors[] = 'Die Rolle „Technische Administration (system_admin)“ darf nicht mit weiteren Rollen kombiniert werden.';
        }

        if (!empty($errors)) {
            return [
                'template' => 'exception/bo/zmsentities/exception/schemavalidation.twig',
                'include' => true,
                'data' => [
                    'roles' => [
                        'messages' => $errors,
                    ],
                ],
            ];
        }

        return $normalized;
    }
}
