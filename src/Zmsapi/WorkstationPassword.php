<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Useraccount as UserAccountQuery;

/**
 * Handle requests concerning services
 */
class WorkstationPassword extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $workstation = Helper\User::checkRights();
        $useraccount = $workstation->useraccount;


        $oldLoginName = $useraccount['id'];
        $oldPassword = $input['password'];

        $query = new UserAccountQuery();

        if ($query->readIsUserExisting($oldLoginName, $oldPassword)) {
            $entity = $query->readEntity($oldLoginName);

            if (!empty($input['newPassword'])) {
                $entity->password = $input['password'];
            }

            $entity->id = $input['id'];

            error_log($entity->id);
            error_log('updating');
            $updatedEntity = $query->updateEntity($oldLoginName, $entity);

            error_log($updatedEntity);
        } else {
            error_log('nope');
            // @TODO Error response
        }

        /*
          $oldLoginName = $useraccountloginName;

          $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
          $query = new Query();
          $entity = new \BO\Zmsentities\Useraccount($input);
          $entity->testValid();
          $workstation = $query->updateEntity($entity, $resolveReferences);
        */
        $message = Response\Message::create(Render::$request);
        $message->data = $updatedEntity;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
