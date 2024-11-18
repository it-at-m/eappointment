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

class ProcessNextByCluster extends BaseController
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
        $workstation = (new Helper\User($request, 1))->checkRights();
        $query = new Query();
        $selectedDate = Validator::param('date')->isString()->getValue();
        $exclude = Validator::param('exclude')->isString()->getValue();
        $allowClusterWideCall = Validator::param('allowClusterWideCall')->isBool()->setDefault(true)->getValue();
        $dateTime = ($selectedDate) ? new DateTime($selectedDate) : \App::$now;
        $cluster = $query->readEntity($args['id']);
        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }
        $queueList = $query->readQueueList($cluster->id, $dateTime, 1);
        if (! $allowClusterWideCall) {
            $queueList = $queueList
            ->toProcessList()
            ->withScopeId($workstation->getScope()->getId())
            ->toQueueList($dateTime);
        }
        
        $message = Response\Message::create($request);
        $message->data = ProcessNextByScope::getProcess($queueList, $dateTime, $exclude);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
