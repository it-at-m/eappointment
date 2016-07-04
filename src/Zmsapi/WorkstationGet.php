<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Workstation as Query;
use \BO\Zmsdb\UserAccount;

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
        $status = 200;
        $message = Response\Message::create(Render::$request);
        $query = new Query();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $xAuthKey = Render::$request->getHeader('X-AuthKey');
        if (!current($xAuthKey)) {
            $status = 401;
        }

        $userAccount = (new UserAccount())
            ->readEntityByAuthKey(current($xAuthKey))
            ->testRights(
                'organisation',
                'department',
                'cluster',
                'useraccount'
            );

        $workstation = $query->readEntity($userAccount->id, $resolveReferences);
        $message->data = $workstation;
        Render::lastModified(time(), '0');
        Render::json($message, $status);
    }
}
