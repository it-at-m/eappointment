<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Workstation;
use \BO\Zmsdb\Process;

class WorkstationProcess extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $workstation = Helper\User::checkRights();
        if ($workstation->process['id']) {
            $process = $workstation->process;
        } else {
            $input = Validator::input()->isJson()->assertValid()->getValue();
            $process = new \BO\Zmsentities\Process($input);
            $processAuthData = (new Process)->readAuthKeyByProcessId($process['id']);
            $process = (new Process)->readEntity($process['id'], $processAuthData['authKey']);
            if ('called' == $process->status || 'processing' == $process->status) {
                throw new Exception\Process\ProcessAlreadyCalled();
            }
        }

        $process->setCallTime(\App::$now);
        $workstation->process = (new Workstation)->writeAssignedProcess($workstation->id, $process);

        $message = Response\Message::create(Render::$request);
        $message->data = $workstation;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
