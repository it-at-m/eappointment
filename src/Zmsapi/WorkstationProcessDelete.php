<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Workstation;
use \BO\Zmsdb\Process;

/**
  * Handle requests concerning services
  */
class WorkstationProcessDelete extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        Helper\User::checkRights();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $workstation = new \BO\Zmsentities\Workstation($input);
        $workstation->testValid();
        (new Process)->writeRemovedWorkstation($workstation);
        unset($workstation->process);

        $message = Response\Message::create(Render::$request);
        $message->data = $workstation;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
