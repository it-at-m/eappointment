<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Scope;

class ProcessByQueueNumber extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $scope = (new Scope())->readWithWorkstationCount(
            $args['id'],
            \App::$now,
            ($resolveReferences > 0) ? $resolveReferences - 1 : 0
        );
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $queueList = (new \BO\Zmsdb\Scope())->readQueueListWithWaitingTime($scope, \App::$now, $resolveReferences + 1);
        $process = $queueList->getQueueByNumber($args['number']);
        if ($process) {
            $process = $process->getProcess();
            $process->scope = $scope;
        } else {
            throw new Exception\Process\ProcessByQueueNumberNotFound();
        }

        $message = Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
