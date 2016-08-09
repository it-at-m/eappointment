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
class ClusterDelete extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $query = new Query();
        $message = Response\Message::create(Render::$request);
        $cluster = $query->readEntity($itemId);
        $query->deleteEntity($itemId);
        $message->data = $cluster;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
