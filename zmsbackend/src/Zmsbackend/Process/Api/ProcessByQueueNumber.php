<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Scope\Service\Scope;

class ProcessByQueueNumber extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readWithWorkstationCount(
            $args['id'],
            \App::$now,
            ($resolveReferences > 0) ? $resolveReferences - 1 : 0
        );
        if (! $scope) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound();
        }
        $queueList = (new \BO\Zmsbackend\Scope\Service\Scope())->readQueueListWithWaitingTime($scope, \App::$now, $resolveReferences + 1);
        $process = $queueList->getQueueByNumber($args['number']);
        if ($process) {
            $process = $process->getProcess();
            $process->scope = $scope;
        } else {
            throw new \BO\Zmsbackend\Process\Exception\ProcessByQueueNumberNotFound();
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
