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
class ProcessListByScopeAndStatus extends BaseController
{
    /**
     * @return String
     */
    public static function render($scopeId, $status)
    {
        Helper\User::checkRights();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();

        $query = new Query();
        $processList = $query->readProcessListByScopeAndStatus($scopeId, $status, $resolveReferences);

        $message = Response\Message::create(Render::$request);
        $message->data = $processList;
        Render::lastModified(time(), '0');
        Render::json($message, 200);
    }
}
