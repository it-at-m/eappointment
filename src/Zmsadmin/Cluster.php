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
        $entityId = Validator::value($args['clusterId'])->isNumber()->getValue();
        $entity = \App::$http->readGetResult('/cluster/' . $entityId . '/', ['resolveReferences' => 2])->getEntity();

        if (!$entity->hasId()) {
            return Helper\Render::withHtml($response, 'page/404.twig', array());
        }

        $department = \App::$http->readGetResult(
            '/scope/' . $entity->scopes[0]['id'] . '/department/',
            ['resolveReferences' => 2]
        )->getEntity();

        $input = $request->getParsedBody();
        if (is_array($input) && array_key_exists('save', $input)) {
            //var_dump($input);
            try {
                $entity = new Entity($input);
                $entity->id = $entityId;
                $entity = \App::$http->readPostResult('/cluster/' . $entity->id . '/', $entity)
                        ->getEntity();
            } catch (\Exception $exception) {
                return Helper\Render::error($exception);
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/cluster.twig',
            array(
                'title' => 'Cluster',
                'menuActive' => 'owner',
                'workstation' => $workstation,
                'cluster' => $entity->getArrayCopy(),
                'departent' => $department,
                'scopeList' => $department->getScopeList(),
            )
        );
    }
}
