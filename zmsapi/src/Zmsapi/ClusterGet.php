<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Cluster as Query;

class ClusterGet extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $message = Response\Message::create($request);

        $getScopeIsOpened = Validator::param('getIsOpened')->isNumber()->setDefault(0)->getValue();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();

        $user = new Helper\User($request);
        if ($user->hasLogin() || $resolveReferences > 0) {
            $resolveReferences = ($resolveReferences > 0 ) ? $resolveReferences : 1;
            $user->checkPermissions();
        } else {
            $message->meta->reducedData = true;
        }


        $cluster = ($getScopeIsOpened)
            ? (new Query())->readEntityWithOpenedScopeStatus($args['id'], \App::$now, $resolveReferences)
            : (new Query())->readEntity($args['id'], $resolveReferences);

        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }

        if ($user->hasLogin() || $resolveReferences > 0) {
            $user->checkPermissions(
                new \BO\Zmsentities\Useraccount\EntityAccess($cluster)
            );
        }

        $message->data = $cluster;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
