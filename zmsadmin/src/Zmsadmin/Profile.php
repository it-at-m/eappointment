<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Schema\Loader;
use BO\Zmsentities\Useraccount;

class Profile extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $confirmSuccess = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $error = $request->getAttribute('validator')->getParameter('error')->isString()->getValue();
        $entity = new Useraccount($workstation->useraccount);

        if ($request->getMethod() === 'POST') {
            $input = $request->getParsedBody();
            $result = $this->writeUpdatedEntity($input, $entity->getId());
            if ($result instanceof Useraccount) {
                return \BO\Slim\Render::redirect('profile', [], [
                    'success' => 'useraccount_saved'
                ]);
            }
        }

        // TODO: there should be common functions to access configuration and user or account data
        // Currently we depend on these magic string like "useraccount".
        // A better approach would be a function called readUserAccountData($accountId)
        $userAccount = \App::$http->readGetResult('/useraccount/' . $entity->getId() . '/')->getEntity();
        $userAccountData = $userAccount->getArrayCopy();
        $workstationUserAccount = $entity->getArrayCopy();
        if (empty($userAccountData['departments'] ?? [])) {
            $userAccountData['departments'] = $workstationUserAccount['departments'] ?? [];
        }
        $config = \App::$http->readGetResult('/config/', [], \App::CONFIG_SECURE_TOKEN)->getEntity();
        $allowedProviderList = explode(',', $config->getPreference('oidc', 'provider') ?? '');

        return \BO\Slim\Render::withHtml(
            $response,
            'page/profile.twig',
            array(
                'title' => 'Nutzerprofil',
                'menuActive' => 'profile',
                'workstation' => $workstation,
                'useraccount' => $userAccountData,
                'success' => $confirmSuccess,
                'error' => $error,
                'exception' => (isset($result)) ? $result : null,
                'metadata' => $this->getSchemaConstraintList(Loader::asArray(Useraccount::$schema)),
                'isFromOidc' => in_array($userAccount->getOidcProviderFromName(), $allowedProviderList)
            )
        );
    }

    protected function writeUpdatedEntity($input)
    {
        $entity = (new Useraccount($input))->withCleanedUpFormData();
        // TODO: Remove the password fields when password authentication is removed in the future
        $entity->setPassword($input);
        return $this->handleEntityWrite(function () use ($entity) {
            return \App::$http->readPostResult('/workstation/password/', $entity)->getEntity();
        });
    }
}
