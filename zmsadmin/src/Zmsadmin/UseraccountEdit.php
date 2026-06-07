<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin;

use BO\Zmsentities\Collection\RoleList;
use BO\Zmsentities\Exception\UserAccountMissingRights;
use BO\Zmsentities\Schema\Loader;
use BO\Zmsentities\Useraccount as Entity;
use BO\Mellon\Validator;
use BO\Zmsclient\Auth;

class UseraccountEdit extends BaseController
{
    /**
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        if (! $workstation->getUseraccount()->hasPermissions(['useraccount'])) {
            throw new UserAccountMissingRights();
        }

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

        $roleList = new RoleList();
        $userAccountRoles = [];

        // Until all controllers have been updated, only superusers should be allowed to assign the new roles
        // @todo: remove isSuperUser() and replace with hasPermissions(['useraccount']) with ZMSKVR-1173
        if ($workstation->getUseraccount()->isSuperUser()) {
            $roleResult = \App::$http->readGetResult('/roles/', []);
            if ($roleResult) {
                $loaded = $roleResult->getCollection();
                if ($loaded !== null) {
                    $roleList = $loaded;
                }
            }
            $userAccountRoles = is_array($userAccount->roles) ? $userAccount->roles : [];
        }

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
                'isFromOidc' => in_array($userAccount->getOidcProviderFromName(), $allowedProviderList),
                'roleList' => $roleList,
                'userAccountRoles' => $userAccountRoles,
            ]
        );
    }

    protected function writeUpdatedEntity($input, $userAccountName)
    {
        $entity = (new Entity($input))->withCleanedUpFormData();
        // TODO: Remove the password fields when password authentication is removed in the future
        $entity->setPassword($input);
        return $this->handleEntityWrite(function () use ($entity, $userAccountName) {
            return \App::$http
                ->readPostResult('/useraccount/' . $userAccountName . '/', $entity)
                ->getEntity();
        });
    }
}
