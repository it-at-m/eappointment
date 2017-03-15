<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Cluster as Query;

/**
  * Handle requests concerning services
  */
class ProcessListByClusterAndDate extends BaseController
{
    /**
     * @return String
     */
    public static function render($clusterId, $dayString)
    {
        Helper\User::checkRights('useraccount');

        $query = new Query();
        $dateTime = new \BO\Zmsentities\Helper\DateTime($dayString);
        $queueList = $query->readQueueList($clusterId, $dateTime);

        $message = Response\Message::create(Render::$request);
        $message->data = $queueList->toProcessList();
        Render::lastModified(time(), '0');
        Render::json($message, 200);
    }
}
