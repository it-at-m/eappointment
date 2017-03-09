<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Request as Query;

/**
  * Handle requests concerning services
  */
class RequestsByCluster extends BaseController
{
    /**
     * @return String
     */
    public static function render($clusterId)
    {
        $query = new Query();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();

        $cluster = $query->readEntity($clusterId, $resolveReferences);
        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }

        $requestList = $query->readListByCluster($cluster, $resolveReferences);

        $message = Response\Message::create(Render::$request);
        $message->data = $requestList;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
