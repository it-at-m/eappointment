<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

use BO\Zmsentities\Useraccount as Entity;
use BO\Mellon\Validator;

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

        if ($request->isPost()) {
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

        // die locals der schema properties passen als Fehlermeldung aber nicht als Beschreibung
        // oder sind unvollständig z.B. aussagekräftiger Nutzername -> länger als 4 Zeichen
        // oder sind unnötig als Beschreibung z.B. 'valide Email im Format ..'
        // außerdem fehlen Beschreibungen wie bei departements
        $metadata['properties'] = [ // die locals der schema properties passen als Fehler aber nicht als Beschreibung
            'id' => [ 'description' => [
                'minLength' => 'Es muss ein aussagekräftiger Nutzername eingegeben werden; länger als 4 Buchstaben.',
                'maxLength' => 'Der Nutzername sollte 40 Zeichen nicht überschreiten.',
            ]],
            'email' => [ 'description' => [
                'minLength' => 'Es muss eine E-Mail-Adresse angegeben werden.',
            ]],
            'changePassword' => [ 'description' => [
                'minLength' => 'Die Länge des Passwortes muss mindestens 6 Zeichen betragen.',
                'sameValues' => 'Die Passwortwiederholung muss identisch zum Passwort sein.',
            ]],
            'departments' => [ 'description' => [
                'choice' => 'Wählen sie mindestens eine Behörde aus.',
            ]]
        ];

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
                'metadata' => $metadata,
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
                && \App::$slim->getContainer()->view->getLoader()->exists($template)
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
