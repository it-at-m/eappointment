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
        $userAccount = \App::$http->readGetResult('/useraccount/'. $userAccountName .'/')->getEntity();
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
        $allowedProviderList = explode(',', $config->getPreference('oidc', 'provider'));

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
        $entity = (new Entity($input))->withCleanedUpFormData();
        $entity->setPassword($input);
        try {
            $entity = \App::$http->readPostResult('/useraccount/'. $userAccountName .'/', $entity)->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            $template = Helper\TwigExceptionHandler::getExceptionTemplate($exception);
            if ('' != $exception->template
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
