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
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
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
            $updated = true;
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/profile.twig',
            array(
                'title' => 'Nutzerprofil',
                'menuActive' => 'profile',
                'updated' => $updated,
                'useraccount' => $entity->getArrayCopy(),
            )
        );
    }
}
