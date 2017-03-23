<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Workstation as Query;
use \BO\Zmsentities\Helper\DateTime;

/**
  * Handle requests concerning services
  */
class WorkstationListByScope extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $query = new Query();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $scope = (new \BO\Zmsdb\Scope)->readEntity($itemId, 0)->withLessData();
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $workstationList = $query->readLoggedInListByScope($itemId, \App::$now, $resolveReferences);

        $message = Response\Message::create(Render::$request);
        $message->data = $workstationList;

        Render::lastModified(time(), '0');
        Render::json($message, $message->getStatuscode());
    }
}
