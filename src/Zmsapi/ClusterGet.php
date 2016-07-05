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
class ClusterGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $cluster = (new Query())->readEntity($itemId, $resolveReferences);
        $message = Response\Message::create(Render::$request);
        $message->data = $cluster;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
