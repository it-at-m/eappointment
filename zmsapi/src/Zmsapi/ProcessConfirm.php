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
use \BO\Mellon\Validator;

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

        $this->writeMails($request, $process);
        $message = Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
    protected function writeMails($request, $process)
    {
        if ($process->hasScopeAdmin() && $process->sendAdminMailOnConfirmation() === 1) {
            $authority = $request->getUri()->getAuthority();
            $validator = $request->getAttribute('validator');
            $initiator = $validator->getParameter('initiator')
                ->isString()
                ->setDefault("$authority API-User")
                ->getValue();
            $config = (new Config())->readEntity();
            $mail = (new \BO\Zmsentities\Mail())->toResolvedEntity($process, $config, 'appointment', $initiator);
            (new Mail())->writeInQueueWithAdmin($mail, \App::$now);
        }
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
