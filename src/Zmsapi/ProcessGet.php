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
  * Handle requests concerning services
  */
class ProcessGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId, $authKey)
    {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $message = Response\Message::create(Render::$request);
        $authKeyByProcessId = (new Query())->readAuthKeyByProcessId($itemId);

        if (null === $authKeyByProcessId) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authKeyByProcessId != $authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        } else {
            $process = (new Query())->readEntity($itemId, $authKey, $resolveReferences);
            $message->data = $process;
        }

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
