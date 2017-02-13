<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Owner as Query;

/**
  * Handle requests concerning services
  */
class OwnerList extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $workstation = Helper\User::checkRights();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $ownerList = (new Query())->readList($resolveReferences);
        $message = Response\Message::create(Render::$request);
        $message->data = $ownerList->withAccess($workstation->getUseraccount());
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
