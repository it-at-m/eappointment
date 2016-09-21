<?php
/**
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\Config as Config;

/**
  * Handle requests concerning services
  */
class ProcessIcs extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId, $authKey)
    {
        $message = Response\Message::create(Render::$request);
        $process = (new Query())->readEntity($itemId, $authKey, 2);
        $authKeyByProcessId = (new Query())->readAuthKeyByProcessId($process->id);

        if (null === $authKeyByProcessId) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authKeyByProcessId != $process->authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        } else {
            $config = (new Config())->readEntity();
            $ics = \BO\Zmsentities\Helper\Messaging::getMailIcs($process, $config);
            $message->data = $ics;
        }

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
