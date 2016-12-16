<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Cluster as Query;

/**
  * Handle requests concerning services
  */
class ClusterQueue extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $query = new Query();
        $message = Response\Message::create(Render::$request);
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $cluster = $query->readEntity($itemId, $resolveReferences);
        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }
        $message->data = $query->readQueueList($itemId, \App::$now);

        Render::lastModified(time(), '0');
        Render::json($message, $message->getStatuscode());
    }
}
