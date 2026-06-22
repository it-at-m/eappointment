<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Zmsentities\Owner as Entity;
use BO\Zmsentities\Exception\UserAccountMissingRights;
use BO\Slim\Render;

class Owner extends BaseController
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
        if (!$workstation->getUseraccount()->hasPermissions(['jurisdiction'])) {
            throw new UserAccountMissingRights();
        }
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $entity = \App::$http->readGetResult('/owner/' . $entityId . '/')->getEntity();

        $input = $request->getParsedBody();
        if (array_key_exists('save', (array) $input)) {
            $entity = (new Entity($input))->withCleanedUpFormData();
            $entity->id = $entityId;
            \App::$http->readPostResult('/owner/' . $entity->id . '/', $entity)
                ->getEntity();
            return Render::redirect(
                'owner',
                [
                    'id' => $entityId
                ],
                [
                    'success' => 'owner_saved'
                ]
            );
        }

        return Render::withHtml(
            $response,
            'page/owner.twig',
            array(
                'title' => 'Kunde','workstation' => $workstation->getArrayCopy(),'menuActive' => 'owner',
                'owner' => $entity->getArrayCopy(),
                'workstation' => $workstation->getArrayCopy(),
                'success' => $success
            )
        );
    }
}
