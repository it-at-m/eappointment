<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Schema\Loader;

use BO\Zmsentities\Useraccount as Entity;

class Profile extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $confirmSuccess = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $error = $request->getAttribute('validator')->getParameter('error')->isString()->getValue();
        $entity = new Entity($workstation->useraccount);

        if ($request->getMethod() === 'POST') {
            $input = $request->getParsedBody();
            $result = $this->writeUpdatedEntity($input, $entity->getId());
            if ($result instanceof Entity) {
                return \BO\Slim\Render::redirect('profile', [], [
                    'success' => 'useraccount_saved'
                ]);
            }
        }

        // TODO: there should be common functions to access configuration and user or account data
        // Currently we depend on these magic string like "useraccount".
        // A better approach would be a function called readUserAccountData($accountId)
        $userAccount = \App::$http->readGetResult('/useraccount/'. $entity->getId() .'/')->getEntity();
        $config = \App::$http->readGetResult('/config/', [], \App::CONFIG_SECURE_TOKEN)->getEntity();
        $allowedProviderList = explode(',', $config->getPreference('oidc', 'provider'));

        return \BO\Slim\Render::withHtml(
            $response,
            'page/profile.twig',
            array(
                'title' => 'Nutzerprofil',
                'menuActive' => 'profile',
                'workstation' => $workstation,
                'useraccount' => $entity->getArrayCopy(),
                'success' => $confirmSuccess,
                'error' => $error,
                'exception' => (isset($result)) ? $result : null,
                'metadata' => $this->getSchemaConstraintList(Loader::asArray(Entity::$schema)),
                'isFromOidc' => in_array($userAccount->getOidcProviderFromName(), $allowedProviderList)                
            )
        );
    }

    protected function writeUpdatedEntity($input)
    {
        $entity = (new Entity($input))->withCleanedUpFormData();
        $entity->setPassword($input);
        try {
            $entity = \App::$http->readPostResult('/workstation/password/', $entity)->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            $template = Helper\TwigExceptionHandler::getExceptionTemplate($exception);
            if ('' != $exception->template
                && \App::$slim->getContainer()->get('view')->getLoader()->exists($template)
            ) {
                return [
                    'template' => $template,
                    'data' => $exception->data
                ];
            }
            throw $exception;
        }
        return $entity;
    }
}
