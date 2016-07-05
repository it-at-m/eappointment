<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\UserAccount as Query;

/**
  * Handle requests concerning services
  */
class UseraccountGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($loginName)
    {
        $userAccount = Helper\User::checkRights('useraccount');

        $query = new Query();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $userAccount = $query->readEntity($loginName, $resolveReferences);

        $message = Response\Message::create(Render::$request);
        $message->data = $userAccount->hasId() ? $userAccount : null;
        Render::lastModified(time(), '0');
        Render::json($message, Helper\User::getStatus($userAccount));
    }
}
