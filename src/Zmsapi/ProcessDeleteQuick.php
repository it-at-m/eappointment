<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Process;
use \BO\Zmsdb\ProcessStatusArchived;
use \BO\Zmsdb\Mail;
use \BO\Zmsdb\Config;
use BO\Mellon\Validator;

class ProcessDeleteQuick extends ProcessDelete
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
        $workstation = (new Helper\User($request, 1))->checkRights('basic');
        \BO\Zmsdb\Connection\Select::getWriteConnection();
        $process = (new Process)->readEntity($args['id'], new \BO\Zmsdb\Helper\NoAuth(), 1);
       
        $this->testProcess($workstation, $process);

        $process->status = 'blocked';
        $this->writeMails($request, $process);
        $status = (new Process)->writeBlockedEntity($process);
        if (! $status) {
            throw new Exception\Process\ProcessDeleteFailed(); // @codeCoverageIgnore
        }
        $message = Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcess($workstation, $process)
    {
        if (!$process->hasId()) {
            throw new Exception\Process\ProcessNotFound();
        }
        if ($process->getCurrentScope()->getId() != $workstation->getScope()->getId()) {
            throw new Exception\Process\ProcessNoAccess();
        }

        if ('called' == $process->status || 'processing' == $process->status) {
            throw new Exception\Process\ProcessAlreadyCalled();
        }
        $process->testValid();
    }
}
