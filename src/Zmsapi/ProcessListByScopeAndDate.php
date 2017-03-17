<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope as Query;

/**
  * Handle requests concerning services
  */
class ProcessListByScopeAndDate extends BaseController
{
    /**
     * @return String
     */
    public static function render($scopeId, $dayString)
    {
        Helper\User::checkRights();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();

        $query = new Query();
        $dateTime = new \BO\Zmsentities\Helper\DateTime($dayString);
        $queueList = $query->readQueueList($scopeId, $dateTime, $resolveReferences);

        $message = Response\Message::create(Render::$request);
        $message->data = $queueList->toProcessList();
        Render::lastModified(time(), '0');
        Render::json($message, 200);
    }
}
