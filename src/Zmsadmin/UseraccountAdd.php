<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

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
        if (is_array($input) && array_key_exists('id', $input)) {
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

        // die locals der schema properties passen als Fehlermeldung aber nicht als Beschreibung
        // oder sind unvollständig z.B. aussagekräftiger Nutzername -> länger als 4 Zeichen
        // oder sind unnötig als Beschreibung z.B. 'valide Email im Format ..'
        // außerdem fehlen Beschreibungen wie bei departements
        $metadata['properties'] = [
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
                'ownerList' => $ownerList->toDepartmentListByOrganisationName(),
                'workstation' => $workstation,
                'success' => $confirmSuccess,
                'action' => 'add',
                'title' => 'Nutzer: Einrichtung und Administration',
                'metadata' => $metadata,
                'menuActive' => 'useraccount',
                'exception' => (isset($result)) ? $result : null,
                'userAccount' => (isset($result)) ? $input : null,
                'selectedDepartment' => $selectedDepartment,
            ]
        );
    }

    protected function writeNewEntity($input)
    {
        $entity = new Entity($input);
        $entity = $entity->withCleanedUpFormData(true);
        try {
            $entity = \App::$http->readPostResult('/useraccount/', $entity)->getEntity();
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
