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

class WorkstationProcessGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($processId)
    {
        Helper\User::checkRights();
        $query = new Process();
        $processAuthData = $query->readAuthKeyByProcessId($processId);
        $process = $query->readEntity($processId, $processAuthData['authKey']);
        if (! $process) {
            throw new Exception\Process\ProcessNotFound();
        }

        $message = Response\Message::create(Render::$request);
        $message->data = $process;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
