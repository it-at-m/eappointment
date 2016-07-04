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
        $status = 200;
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $query = new Query();
        $message = Response\Message::create(Render::$request);
        $userAccount = $query->readEntity($loginName, $resolveReferences);
        if (false === $userAccount->hasLoginName()) {
            $status = 404;
            $message->meta->error = 'No Useraccount found';
        }
        $message->data = $userAccount;
        Render::lastModified(time(), '0');
        Render::json($message, $status);
    }
}
