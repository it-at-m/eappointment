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
            //first read for scope -> its 0 after delete
            $process = $query->readEntity($itemId, $authKey);
            $scopeId = $process->getScopeId();
            $query->deleteEntity($itemId, $authKey);
            //read with delete status and add scope to process for return
            $process = $query->readEntity($itemId, $authKey);
            $process->addScope($scopeId);
            $message->data = $process;
        }

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
