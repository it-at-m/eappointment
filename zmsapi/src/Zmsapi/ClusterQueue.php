<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Cluster as Query;
use \BO\Zmsentities\Helper\DateTime;

class ClusterQueue extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        (new Helper\User($request))->checkRights('cluster');
        $query = new Query();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $selectedDate = Validator::param('date')->isString()->getValue();
        $dateTime = ($selectedDate) ? (new DateTime($selectedDate))->modify(\App::$now->format('H:i')) : \App::$now;

        $cluster = $query->readEntity($args['id'], $resolveReferences);
        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }

        $message = Response\Message::create($request);
        $message->data = $query->readQueueList($cluster->id, $dateTime, $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
