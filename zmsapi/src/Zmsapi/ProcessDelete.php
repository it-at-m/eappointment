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
        \BO\Zmsdb\Connection\Select::getWriteConnection();
        $process = (new Process)->readEntity($args['id'], new \BO\Zmsdb\Helper\NoAuth(), 2);
        $this->testProcessData($process, $args['authKey']);
        if ('reserved' == $process->status) {
            if (!(new Process)->writeBlockedEntity($process)) {
                throw new Exception\Process\ProcessDeleteFailed(); // @codeCoverageIgnore
            }
            $processDeleted = $process;
        } else {
            $processDeleted = (new Process)->writeCanceledEntity($args['id'], $args['authKey']);
            if (! $processDeleted || ! $processDeleted->hasId()) {
                throw new Exception\Process\ProcessDeleteFailed(); // @codeCoverageIgnore
            }
        }
        $this->writeMails($request, $process);
        $message = Response\Message::create($request);
        $message->data = $processDeleted;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function writeMails($request, $process)
    {
        if ($process->hasScopeAdmin() && $process->sendAdminMailOnDeleted()) {
            $authority = $request->getUri()->getAuthority();
            $validator = $request->getAttribute('validator');
            $initiator = $validator->getParameter('initiator')
                ->isString()
                ->setDefault("$authority API-User")
                ->getValue();
            $config = (new Config())->readEntity();
            $mail = (new \BO\Zmsentities\Mail())->toResolvedEntity($process, $config, 'deleted', $initiator);
            (new Mail())->writeInQueueWithAdmin($mail, \App::$now);
        }
    }

    protected function testProcessData($process, $authKey)
    {
        if (! $process) {
            throw new Exception\Process\ProcessNotFound();
        }
        $authName = $process->getFirstClient()['familyName'];
        if ($process['authKey'] != $authKey && $authName != $authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        }
    }
}
