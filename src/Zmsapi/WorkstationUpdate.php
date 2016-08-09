<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Workstation as Query;

/**
  * Handle requests concerning services
  */
class WorkstationUpdate extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        Helper\User::checkRights('organisation', 'department', 'cluster', 'useraccount');

        $query = new Query();
        $input = Validator::input()->isJson()->getValue();
        $entity = new \BO\Zmsentities\Workstation($input);
        $workstation = $query->updateEntity($entity);

        $message = Response\Message::create(Render::$request);
        $message->data = ($workstation->hasId()) ? $workstation : null;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
