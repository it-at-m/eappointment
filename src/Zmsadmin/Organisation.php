<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;
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
        $organisationId = Validator::value($args['id'])->isNumber()->getValue();
        $organisation = \App::$http->readGetResult(
            '/organisation/'. $organisationId .'/'
        )->getEntity();

        if (!isset($organisation['id'])) {
            return \BO\Slim\Render::withError($response, 'page/404.twig', array());
        }

        $input = $request->getParsedBody();
        if (array_key_exists('save', (array)$input)) {
            $entity = new Entity($input);
            $entity->id = $organisationId;
            $organisation = \App::$http->readPostResult(
                '/organisation/'. $entity->id .'/',
                $entity
            )->getEntity();
        } elseif (array_key_exists('delete', (array)$input)) {
            $organisation = \App::$http->readDeleteResult(
                '/organisation/'. $organisationId .'/'
            )->getEntity();
        }

        return \BO\Slim\Render::withHtml($response, 'page/organisation.twig', array(
            'title' => 'Bezirk - Einrichtung und Administration',
            'organisation' => $organisation->getArrayCopy(),
            'menuActive' => 'owner'
        ));
    }
}
