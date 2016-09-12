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

    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $entity = \App::$http->readGetResult('/organisation/'. $entityId .'/')->getEntity();

        if (null === $entity || !$entity->hasId()) {
            return \BO\Slim\Render::withHtml($response, 'page/404.twig', array());
        }

        $input = $request->getParsedBody();
        if (array_key_exists('save', (array) $input)) {
            try {
                $entity = new Entity($input);
                $entity->id = $entityId;
                $entity = \App::$http->readPostResult(
                    '/organisation/'. $entity->id .'/',
                    $entity
                )->getEntity();
            } catch (\Exception $exception) {
                return Helper\Render::error($exception);
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/organisation.twig',
            array(
                'title' => 'Bezirk - Einrichtung und Administration',
                'workstation' => $workstation,
                'organisation' => $entity,
                'menuActive' => 'owner'
            )
        );
    }
}
