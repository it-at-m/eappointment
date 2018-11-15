<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
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
        $entity = new Entity($workstation->useraccount);
        $input = $request->getParsedBody();

        if (is_array($input) && array_key_exists('id', $input)) {
            $result = $this->writeUpdatedEntity($input);
            if ($result instanceof Entity) {
                return \BO\Slim\Render::redirect('profile', [], [
                    'success' => 'password_changed'
                ]);
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/profile.twig',
            array(
                'title' => 'Nutzerprofil',
                'menuActive' => 'profile',
                'workstation' => $workstation,
                'useraccount' => $entity->getArrayCopy(),
                'success' => $confirmSuccess,
                'exception' => (isset($result)) ? $result : null
            )
        );
    }

    protected function writeUpdatedEntity($input)
    {
        $entity = (new Entity($input))->withCleanedUpFormData();
        $entity->withPassword($input);
        try {
            $entity = \App::$http->readPostResult('/workstation/password/', $entity)->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            $template = Helper\TwigExceptionHandler::getExceptionTemplate($exception);
            if ('' != $exception->template
                && \App::$slim->getContainer()->view->getLoader()->exists($template)
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
