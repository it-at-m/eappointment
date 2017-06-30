<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Process;
use \BO\Zmsdb\Mail;
use \BO\Zmsdb\Config;
use BO\Mellon\Validator;

class ProcessDelete extends BaseController
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
        $this->testProcessData($args['id'], $args['authKey']);
        $process = (new Process)->readEntity($args['id'], $args['authKey'], 1);
        $process->status = 'deleted';
        if ($process->hasScopeAdmin()) {
            $initiator = Validator::param('initiator')->isString()->getValue();
            $config = (new Config())->readEntity();
            $mail = (new \BO\Zmsentities\Mail())->toResolvedEntity($process, $config, $initiator);
            (new Mail())->writeInQueueWithAdmin($mail);
        }
        $processDeleted = (new Process)->deleteEntity($args['id'], $args['authKey']);
        if (! $processDeleted || ! $processDeleted->hasId()) {
            throw new Exception\Process\ProcessDeleteFailed();
        }
        $message = Response\Message::create($request);
        $message->data = $processDeleted;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcessData($processId, $authKey)
    {
        $authCheck = (new Process())->readAuthKeyByProcessId($processId);
        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authCheck['authKey'] != $authKey && $authCheck['authName'] != $authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        }
    }
}
