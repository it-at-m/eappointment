<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process as Query;

/**
  * Create or update a process for pickup
  */
class ProcessPickup extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $workstation = Helper\User::checkRights();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Process($input);
        if ($entity->hasProcessCredentials()) {
            $process = (new Query())->readEntity($entity['id'], $entity['authKey'], 0);
            if ($process->scope['id'] != $workstation->scope['id']) {
                throw new Exception\Process\ProcessNoAccess();
            }
            $process->addData($input);
            $process->testValid();
            $process = (new Query())->updateEntity($process);
        } elseif ($entity->hasQueueNumber()) {
            $process = (new Query())->readByQueueNumberAndScope($entity['queue']['number'], $workstation->scope['id']);
            if (!$process->id) {
                $process = (new Query())->writeNewPickup($workstation->scope, \App::$now, $entity['queue']['number']);
            }
        } else {
            throw new Exception\Process\ProcessInvalid();
        }
        $message = Response\Message::create(Render::$request);
        $message->data = $process;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
