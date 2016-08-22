<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Cluster as Entity;
use BO\Mellon\Validator;

class Cluster extends BaseController
{
    /**
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {

        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $entity = \App::$http->readGetResult('/cluster/' . $entityId . '/')->getEntity();

        if (!$entity->hasId()) {
            return Helper\Render::withHtml($response, 'page/404.twig', array());
        }

        $scopeList = \App::$http->readGetResult(
            '/scope/provider/' . $entity->scopes[0]['provider']['id'] . '/'
        )->getCollection();

        return Helper\Render::checkedHtml(
            self::$errorHandler,
            $response,
            'page/cluster.twig',
            array(
                'title' => 'Cluster',
                'menuActive' => 'owner',
                'cluster' => $entity->getArrayCopy(),
                'scopeList' => $scopeList
            )
        );
    }
}
