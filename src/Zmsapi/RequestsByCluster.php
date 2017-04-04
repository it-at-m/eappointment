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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();

        $cluster = (new \BO\Zmsdb\Cluster)->readEntity($clusterId, $resolveReferences);
        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }

        $requestList = $query->readListByCluster($cluster, $resolveReferences - 1);
        error_log(var_export($requestList, 1));

        $message = Response\Message::create(Render::$request);
        $message->data = $requestList;
        Render::lastModified(time(), '0');

        //Also return 200 and original message if requestList is empty
        Render::json($message, 200);
    }
}
