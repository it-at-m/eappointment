<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Config;
use \BO\Zmsdb\Mail;
use BO\Mellon\Validator;
use \BO\Zmsdb\Process;

class ProcessUpdate extends BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();

        $input = Validator::input()->isJson()->assertValid()->getValue();
        $process = new \BO\Zmsentities\Process($input);
        $process->testValid();
        $this->testProcessData($process);

        $processUpdated = (new Process)->updateEntity($process, $resolveReferences);
        if ($process->hasScopeAdmin()) {
            $initiator = Validator::param('initiator')->isString()->getValue();
            $config = (new Config())->readEntity();
            $process->status = 'updated';
            $mail = (new \BO\Zmsentities\Mail())->toResolvedEntity($process, $config, $initiator);
            (new Mail())->writeInQueueWithAdmin($mail);
        }

        $message = Response\Message::create($request);
        $message->data = $processUpdated;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcessData($process)
    {
        $authCheck = (new Process())->readAuthKeyByProcessId($process->id);
        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authCheck['authKey'] != $process->authKey && $authCheck['authName'] != $process->authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        }
    }
}
