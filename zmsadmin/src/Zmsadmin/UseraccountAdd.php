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
        $submittedUserAccount = null;
        if ($request->getMethod() === 'POST') {
            $input['password'] = (
                isset($input['changePassword']) && is_array($input['changePassword'])
            ) ? ($input['changePassword'][0] ?? null) : null;
            $submittedUserAccount = $input; // Preserve submitted data for form re-population
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
        $allowedProviderList = explode(',', $config->getPreference('oidc', 'provider') ?? '');

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
                'userAccount' => $submittedUserAccount, // Use submitted data to preserve form values on error
                'selectedDepartment' => $selectedDepartment,
                'oidcProviderList' => array_filter($allowedProviderList),
                'metadata' => $this->getSchemaConstraintList(Loader::asArray(Entity::$schema))
            ]
        );
    }

    protected function writeNewEntity($input)
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

        $entity = new Entity($input);
        if (isset($input['oidcProvider']) && '' != $input['oidcProvider']) {
            $entity->id = $entity->id . '@' . $input['oidcProvider'];
        }
        $entity = $entity->withCleanedUpFormData(true);
        return $this->handleEntityWrite(function () use ($entity) {
            return \App::$http->readPostResult('/useraccount/', $entity)->getEntity();
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
