<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process as Query;

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
        $collection = $query->readProcessListByClusterAndTime($clusterId, $dateTime);

        $message = Response\Message::create(Render::$request);
        $message->data = $collection;
        Render::lastModified(time(), '0');
        Render::json($message, 200);
    }
}
