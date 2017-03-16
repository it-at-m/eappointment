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
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $confirm_success = $request->getAttribute('validator')->getParameter('confirm_success')->isString()->getValue();
        $entity = new Entity($workstation->useraccount);

        if (!$entity->hasId()) {
            return Helper\Render::withHtml($response, 'page/404.twig', array());
        }

        $updated = false;

        $input = $request->getParsedBody();
        if (is_array($input) && array_key_exists('save', $input)) {
            if ($input['newPassword'] && $input['newPassword'] !== $input['repeatPassword']) {
                throw new \Exception('Passwörter stimmen nicht überein.');
            }

            $newEntity = new Entity($input);
            $entity = \App::$http->readPostResult('/workstation/password/', $newEntity)
                    ->getEntity();
            return \BO\Slim\Render::redirect('profile', [], [
                'confirm_success' => \App::$now->getTimeStamp()
            ]);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/profile.twig',
            array(
                'title' => 'Nutzerprofil',
                'menuActive' => 'profile',
                'updated' => $updated,
                'workstation' => $workstation,
                'useraccount' => $entity->getArrayCopy(),
                'confirm_success' => $confirm_success,
            )
        );
    }
}
