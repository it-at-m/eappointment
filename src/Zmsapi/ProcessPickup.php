<?php
/**
 * @package 115Mandant
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
        if (static::hasProcessCredentials($input)) {
            $process = (new Query())->readEntity($input['id'], $input['authKey'], 0);
            if ($process->scope['id'] != $workstation->scope['id']) {
                throw new Exception\Process\ProcessNoAccess();
            }
            $process->addData($input);
            $process->testValid();
            $process = (new Query())->updateEntity($process);
        } elseif (static::hasQueueNumber($input)) {
            $process = (new Query())->readByQueueNumberAndScope($input['queue']['number'], $workstation->scope['id']);
            if (!$process->id) {
                $process = (new Query())->writeNewPickup($workstation->scope, \App::$now, $input['queue']['number']);
            }
        } else {
            throw new Exception\Process\ProcessInvalid();
        }
        $message = Response\Message::create(Render::$request);
        $message->data = $process;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }

    protected static function hasProcessCredentials($input)
    {
        return (isset($input['id'])
            && isset($input['authKey'])
            && $input['id']
            && $input['authKey']
        );
    }

    protected static function hasQueueNumber($input)
    {
        return (isset($input['queue'])
            && isset($input['queue']['number'])
            && $input['queue']['number']
        );
    }
}
