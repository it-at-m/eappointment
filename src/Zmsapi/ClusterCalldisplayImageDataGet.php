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
class ClusterCalldisplayImageDataGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        Helper\User::checkRights('cluster');
        $query = new Query();
        $cluster = $query->readEntity($itemId)->withLessData();
        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }
        $message = Response\Message::create(Render::$request);
        $message->data = $query->readImageData($itemId);

        Render::lastModified(time(), '0');
        Render::json($message, $message->getStatuscode());
    }
}
