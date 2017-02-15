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
class ClusterCalldisplayImageDataUpdate extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $message = Response\Message::create(Render::$request);
        $query = new Query();
        $cluster = $query->readEntity($itemId);
        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }

        Helper\User::checkRights('cluster');
        $input = Validator::input()->isJson()->getValue();
        $entity = new \BO\Zmsentities\Mimepart($input);

        $message->data = $query->writeImageData($itemId, $entity);
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
