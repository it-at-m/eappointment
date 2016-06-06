<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;
use BO\Zmsentities\Organisation as Entity;

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
        $input = $request->getParsedBody();

        $organisation = \App::$http->readGetResult(
            '/organisation/'. $args['id'] .'/'
        )->getEntity();

        if (!isset($organisation['id'])) {
            return \BO\Slim\Render::withError($response, 'page/404.twig', array());
        }

        if (array_key_exists('save', $input)) {
            $entity = new Entity($input);
            $entity->id = $args['id'];
            $organisation = \App::$http->readPostResult(
                '/organisation/'. $entity->id .'/',
                $entity
            )->getEntity();
        } elseif (array_key_exists('delete', $input)) {
            $organisation = \App::$http->readDeleteResult(
                '/organisation/'. $args['id'] .'/'
            )->getEntity();
        }

        return \BO\Slim\Render::withHtml($response, 'page/organisation.twig', array(
            'title' => 'Bezirk - Einrichtung und Administration',
            'organisation' => $organisation->getArrayCopy(),
            'menuActive' => 'owner'
        ));
    }
}
