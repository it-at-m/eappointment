<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\UserAccount as Query;

/**
  * Handle requests concerning services
  */
class UseraccountAdd extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        Helper\User::checkRights('useraccount');
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $query = new Query();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Useraccount($input);
        $entity->testValid();

        error_log(var_export($entity, 1));

        $userAccount = $query->writeEntity($entity, $resolveReferences);

        $message = Response\Message::create(Render::$request);
        $message->data = $userAccount;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
