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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $query = new Query();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Workstation($input);
        $entity->testValid();
        $workstation = $query->updateEntity($entity, $resolveReferences);

        $message = Response\Message::create(Render::$request);
        $message->data = ($workstation->hasId()) ? $workstation : null;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
