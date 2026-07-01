<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Mail\Service\Mail;
use BO\Zmsbackend\Config\Service\Config;
use BO\Zmsbackend\Process\Service\Process as ProcessRepository;
use BO\Zmsbackend\Department\Service\Department as DepartmentRepository;
use BO\Zmsentities\Process;

/**
 * @SuppressWarnings(Coupling)
 */
class ProcessDeleteMail extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return string
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $process = new Process($input);
        $initiator = Validator::param('initiator')->isString()->getValue();

        $process->testValid();
        $this->testProcessData($process);

        \BO\Zmsbackend\Connection\Select::getWriteConnection();

        $mail = $this->writeMail($process, $initiator);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $mail;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }

    protected static function writeMail(Process $process, $initiator = null)
    {
        $config = (new \BO\Zmsbackend\Config\Service\Config())->readEntity();
        $department = (new DepartmentRepository())->readByScopeId($process->scope->id);
        $collection = ProcessConfirmationMail::getProcessListOverview($process, $config);

        $mail = (new \BO\Zmsentities\Mail())
            ->setTemplateProvider(new \BO\Zmsbackend\Helper\MailTemplateProvider($process))
            ->toResolvedEntity($collection, $config, 'deleted', $initiator)
            ->withDepartment($department);
        $mail->testValid();
        if ($process->getFirstClient()->hasEmail() && $process->scope->hasEmailFrom()) {
            $mail = (new \BO\Zmsbackend\Mail\Service\Mail())->writeInQueue($mail, \App::$now, false);
            \App::$log->debug("Send mail", [$mail]);
        }
        return $mail;
    }

    protected function testProcessData($process)
    {
        $authCheck = (new ProcessRepository())->readAuthKeyByProcessId($process->getId());
        if (! $authCheck) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessNotFound();
        } elseif (
            $process->toProperty()->scope->preferences->client->emailRequired->get() &&
            ! $process->getFirstClient()->hasEmail()
        ) {
            throw new \BO\Zmsbackend\Process\Exception\EmailRequired();
        }
    }
}
