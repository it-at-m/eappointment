<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\Mail;
use \BO\Zmsdb\Config;
use BO\Mellon\Validator;

class ProcessDelete extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId, $authKey)
    {
        $query = new Query();
        $message = Response\Message::create(Render::$request);
        $authCheck = (new Query())->readAuthKeyByProcessId($itemId);
        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authCheck['authKey'] != $authKey && $authCheck['authName'] != $authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        } else {
            $process = $query->readEntity($itemId, $authKey);
            $process->status = 'deleted';
            $query->deleteEntity($itemId, $authKey);
            if ($process->hasScopeAdmin()) {
                $initiator = Validator::param('initiator')->isString()->getValue();
                $config = (new Config())->readEntity();
                $mail = (new \BO\Zmsentities\Mail())->toAdminInfoMail($process, $config, $initiator);
                (new Mail())->writeInQueueWithAdmin($mail);
            }
            $message->data = $query->readEntity($itemId, $authKey);
        }

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
