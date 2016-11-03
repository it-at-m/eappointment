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
class TicketprinterWaitingnumberByCluster extends BaseController
{
    /**
     * @return String
     */
    public static function render($clusterId, $hash)
    {
        $message = Response\Message::create(Render::$request);
        $ticketprinter = (new \BO\Zmsdb\Ticketprinter())->readByHash($hash);
        if (! $ticketprinter->hasId()) {
            throw new Exception\Ticketprinter\TicketprinterHashNotValid();
        }

        $query = new Query();
        $cluster = $query->readEntity($clusterId);
        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }

        /*
        $scope = $query->readScopeWithShortestWaitingTime($cluster->id, \App::$now);
        $message->data = $process;
        */

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
