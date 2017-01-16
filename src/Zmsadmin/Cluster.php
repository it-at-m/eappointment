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
     * @SuppressWarnings(Param)
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $entity = \App::$http->readGetResult('/cluster/' . $entityId . '/', ['resolveReferences' => 2])->getEntity();

        if (!$entity->hasId()) {
            return Helper\Render::withHtml($response, 'page/404.twig', array());
        }

        $department = \App::$http->readGetResult(
            '/scope/' . $entity->scopes[0]['id'] . '/department/',
            ['resolveReferences' => 2]
        )->getEntity();

        return \BO\Slim\Render::withHtml(

            $response,
            'page/cluster.twig',
            array(
                'title' => 'Cluster',
                'menuActive' => 'owner',
                'workstation' => $workstation,
                'cluster' => $entity->getArrayCopy(),
                'scopeList' => $department->getScopeList(),
            )
        );
    }
}
