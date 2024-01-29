<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process;

/**
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class ProcessConfirm extends BaseController
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
        \BO\Zmsdb\Connection\Select::setCriticalReadSession();

        $initiator = Validator::param('initiator')->isString()->getValue();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(3)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Process($input);
        $entity->testValid();
        $this->testProcessData($entity);

        $userAccount = (new Helper\User($request))->readWorkstation()->getUseraccount();
        $process = (new Process())->readEntity($entity->id, $entity->authKey);
        if ('preconfirmed' != $process->status && 'reserved' != $process->status) {
            throw new Exception\Process\ProcessNotPreconfirmedAnymore();
        }
        
        $process = (new Process())->updateProcessStatus(
            $process,
            'confirmed',
            \App::$now,
            $resolveReferences,
            $userAccount
        );

        if ($initiator && $process->hasScopeAdmin()) {
            $config = (new Config())->readEntity();
            $mail = (new \BO\Zmsentities\Mail())->toResolvedEntity($process, $config, 'confirmed', $initiator);
            (new Mail())->writeInQueueWithAdmin($mail);
        }

        $message = Response\Message::create($request);
        $message->data = $process;

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
