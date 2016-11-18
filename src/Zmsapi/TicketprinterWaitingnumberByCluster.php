<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Ticketprinter as Query;
use \BO\Zmsdb\Cluster;
use \BO\Zmsdb\Process;

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
        $ticketprinter = (new Query())->readByHash($hash);

        if (! $ticketprinter->hasId()) {
            throw new Exception\Ticketprinter\TicketprinterHashNotValid();
        }
        if (! $ticketprinter->isEnabled()) {
            throw new Exception\Ticketprinter\TicketprinterNotEnabled();
        }

        $cluster = (new Cluster())->readEntity($clusterId, 0);
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
