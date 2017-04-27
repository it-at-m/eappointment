<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process as Query;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
  * Handle requests concerning services
  */
class ProcessQueued extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $workstation = (new Helper\User($request))->checkRights();
        $message = Response\Message::create($request);
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Process($input);
        $entity->testValid();

        if ($entity->hasProcessCredentials()) {
            $process = (new Query())->readEntity($entity['id'], $entity['authKey'], 0);
            if ($process->scope['id'] != $workstation->scope['id']) {
                throw new Exception\Process\ProcessNoAccess();
            }
        } elseif ($entity->hasQueueNumber()) {
            $process = (new Query())->readByQueueNumberAndScope($entity['queue']['number'], $workstation->scope['id']);
        } else {
            throw new Exception\Process\ProcessInvalid();
        }
        
        $process->status = 'queued';
        $process = (new Query())->updateEntity($process);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
