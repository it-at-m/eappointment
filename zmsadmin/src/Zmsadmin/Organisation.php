<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Organisation as Entity;
use BO\Mellon\Validator;

/**
  * Handle requests concerning services
  *
  */
class Organisation extends BaseController
{
    /**
     * @return String
     */

    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $entity = \App::$http->readGetResult(
            '/organisation/' . $entityId . '/',
            ['resolveReferences' => 1]
        )->getEntity();

        $input = $request->getParsedBody();
        if (array_key_exists('save', (array) $input)) {
            $entity = (new Entity($input))->withCleanedUpFormData();
            $entity->id = $entityId;
            $entity = \App::$http->readPostResult('/organisation/' . $entity->id . '/', $entity)->getEntity();
            return \BO\Slim\Render::redirect(
                'organisation',
                [
                    'id' => $entityId
                ],
                [
                    'success' => 'organisation_saved'
                ]
            );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/organisation.twig',
            array(
                'title' => 'Bezirk - Einrichtung und Administration',
                'workstation' => $workstation,
                'organisation' => $entity,
                'menuActive' => 'owner',
                'success' => $success
            )
        );
    }
}
