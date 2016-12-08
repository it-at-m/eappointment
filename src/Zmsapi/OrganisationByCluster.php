<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Organisation as Query;
use \BO\Zmsdb\Cluster;

/**
  * Handle requests concerning services
  */
class OrganisationByCluster extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $cluster = (new Cluster())->readEntity($itemId, 0);
        if (! $cluster->hasId()) {
            throw new Exception\Cluster\ClusterNotFound();
        }
        $organisation = (new Query())->readByClusterId($itemId, $resolveReferences);
        if (! $organisation) {
            throw new Exception\Organisation\OrganisationNotFound();
        }
        $message = Response\Message::create(Render::$request);
        $message->data = $organisation;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
