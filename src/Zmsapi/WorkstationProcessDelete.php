<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Workstation;

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
        $workstation = Helper\User::checkRights();
        if ('pickup' != $workstation->process['status']) {
            $workstation->process['queue']['callCount']++;
        }
        $workstation->process = (new \BO\Zmsentities\Process($workstation->process))->setStatusBySettings();
        (new Workstation)->writeRemovedProcess($workstation);
        unset($workstation->process);

        $message = Response\Message::create(Render::$request);
        $message->data = $workstation;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
