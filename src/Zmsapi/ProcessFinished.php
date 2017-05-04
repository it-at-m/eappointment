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

class ProcessFinished extends BaseController
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

        if ($entity->hasProcessCredentials() && ('pending' == $entity['status'] || 'finished' == $entity['status'])) {
            if ($entity->scope['id'] != $workstation->scope['id']) {
                throw new Exception\Process\ProcessNoAccess();
            }
        } else {
            throw new Exception\Process\ProcessInvalid();
        }

        $query = new Query();
        $process = ('pending' == $entity['status']) ?
            $query->updateEntity($entity) :
            $query->writeEntityFinished($entity, \App::$now);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
