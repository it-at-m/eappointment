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

/**
 * @SuppressWarnings(Coupling)
 * @return String
 */
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
        $entity = new \BO\Zmsentities\Process($input);
        $entity->testValid();
        $this->testProcessData($entity);

        \BO\Zmsdb\Connection\Select::setClusterWideCausalityChecks();
        \BO\Zmsdb\Connection\Select::getWriteConnection();

        $process = (new Process)->readEntity($args['id'], $args['authKey'], 1);
        $initiator = Validator::param('initiator')->isString()->getValue();
        if ($initiator && $process->hasScopeAdmin()) {
            $config = (new Config())->readEntity();
            $process->status = 'updated';
            $mail = (new \BO\Zmsentities\Mail())->toResolvedEntity($process, $config, $initiator);
            (new Mail())->writeInQueueWithAdmin($mail);
        }

        $message = Response\Message::create($request);
        $message->data = (new Process)->updateEntity($entity, \App::$now, $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcessData($entity)
    {
        $authCheck = (new Process())->readAuthKeyByProcessId($entity->id);
        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authCheck['authKey'] != $entity->authKey && $authCheck['authName'] != $entity->authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        }
    }
}
