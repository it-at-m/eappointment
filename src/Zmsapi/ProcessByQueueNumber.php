<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\ProcessStatusQueued;
use \BO\Zmsdb\Scope;

/**
  * Handle requests concerning services
  */
class ProcessByQueueNumber extends BaseController
{
    /**
     * @return String
     */
    public static function render($scopeId, $queueNumber)
    {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $message = Response\Message::create(Render::$request);

        $scope = (new Scope())->readEntity($scopeId);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }

        $process = ProcessStatusQueued::init()->readByQueueNumberAndScope($queueNumber, $scopeId, $resolveReferences);
        if (! $process->hasId()) {
            throw new Exception\Process\ProcessNotFound();
        }

        $message->data = $process;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
