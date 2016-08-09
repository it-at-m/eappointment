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
class UseraccountDelete extends BaseController
{
    /**
     * @return String
     */
    public static function render($loginName)
    {
        $userAccount = Helper\User::checkRights('useraccount');

        $query = new Query();
        $userAccount = $query->readEntity($loginName);
        $query->deleteEntity($loginName);

        $message = Response\Message::create(Render::$request);
        $message->data = $userAccount;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
