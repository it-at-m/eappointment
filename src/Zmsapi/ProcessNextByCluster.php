<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Cluster as Query;
use \BO\Zmsentities\Helper\DateTime;

/**
  * Handle requests concerning services
  */
class ProcessNextByCluster extends BaseController
{
    /**
     * @return String
     */
    public static function render($clusterId)
    {
        $query = new Query();
        $selectedDate = Validator::param('date')->isString()->getValue();
        $dateTime = ($selectedDate) ? new DateTime($selectedDate) : \App::$now;
        $cluster = $query->readEntity($clusterId);
        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }
        $queueList = $query->readQueueList($cluster->id, $dateTime);
        $process = $queueList->getNextProcess($dateTime);
        if (! $process) {
            throw new Exception\Process\ProcessNotFound();
        }

        $message = Response\Message::create(Render::$request);
        $message->data = $process;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
