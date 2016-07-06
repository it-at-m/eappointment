<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Workstation as Query;

/**
  * Handle requests concerning services
  */
class WorkstationGet extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $userAccount = Helper\User::checkRights('organisation', 'department', 'cluster', 'useraccount');

        $query = new Query();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $workstation = $query->readEntity($userAccount->id, $resolveReferences);

        $message = Response\Message::create(Render::$request);
        $message->data = $workstation;
        Render::lastModified(time(), '0');
        Render::json($message, Helper\User::getStatus($workstation, true));
    }
}
