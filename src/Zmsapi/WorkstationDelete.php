<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;

use \BO\Mellon\Validator;

use \BO\Zmsdb\Workstation as Query;

class WorkstationDelete extends BaseController
{
    /**
     * @return String
     */
    public static function render($loginname)
    {
        Helper\User::checkRights();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $query = new Query();
        $workstation = $query->writeEntityLogoutByName($loginname, $resolveReferences);

        $message = Response\Message::create(Render::$request);
        $message->data = $workstation;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
