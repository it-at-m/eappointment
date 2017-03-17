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
class WorkstationProcess extends BaseController
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
        $process = new \BO\Zmsentities\Process($workstation->process);
        $workstation->process = $process->setCallTime(\App::$now);
        $workstation->process = (new Process)->writeAssignedWorkstation($workstation);

        $message = Response\Message::create(Render::$request);
        $message->data = $workstation;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
