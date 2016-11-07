<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Process as Query;

class ProcessDelete extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId, $authKey)
    {
        $query = new Query();
        $message = Response\Message::create(Render::$request);
        $authKeyByProcessId = $query->readAuthKeyByProcessId($itemId);

        if (null === $authKeyByProcessId) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authKeyByProcessId != $authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        } else {
            //read process before delete to get original settings
            $process = $query->readEntity($itemId, $authKey);
            //set delete status to process for return
            if ($query->deleteEntity($itemId, $authKey)) {
                $process->status = 'deleted';
            }
            $message->data = $process;
        }

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
