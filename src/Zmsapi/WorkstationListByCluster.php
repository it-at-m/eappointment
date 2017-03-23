<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Workstation as Query;
use \BO\Zmsentities\Helper\DateTime;

/**
  * Handle requests concerning services
  */
class WorkstationListByCluster extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $query = new Query();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $cluster = (new \BO\Zmsdb\Cluster)->readEntity($itemId, 0);
        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }
        $workstationList = $query->readLoggedInListByCluster($itemId, \App::$now, $resolveReferences);

        $message = Response\Message::create(Render::$request);
        $message->data = $workstationList;

        Render::lastModified(time(), '0');
        Render::json($message, $message->getStatuscode());
    }
}
