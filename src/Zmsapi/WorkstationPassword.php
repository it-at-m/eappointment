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
                $entity->password = $input['newPassword'];
            }

            $entity->id = $input['id'];
            $updatedEntity = $query->updateEntity($oldLoginName, $entity);
        } else {
            throw new Exception\Useraccount\InvalidCredentials();
        }

        $message = Response\Message::create(Render::$request);
        $message->data = $updatedEntity;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
