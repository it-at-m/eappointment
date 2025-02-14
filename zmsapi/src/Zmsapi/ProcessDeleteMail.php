<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Config;
use BO\Zmsdb\Process as ProcessRepository;
use BO\Zmsdb\Department as DepartmentRepository;
use BO\Zmsentities\Process;

/**
 * @SuppressWarnings(Coupling)
 */
class ProcessDeleteMail extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return string
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $process = new Process($input);

        $process->testValid();
        $this->testProcessData($process);

        \BO\Zmsdb\Connection\Select::getWriteConnection();

        $mail = $this->writeMail($process);

        $message = Response\Message::create($request);
        $message->data = $mail;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }

    protected static function writeMail(Process $process)
    {
        $config = (new Config())->readEntity();
        $department = (new DepartmentRepository())->readByScopeId($process->scope['id']);
        $collection = ProcessConfirmationMail::getProcessListOverview($process, $config);

        $mail = (new \BO\Zmsentities\Mail())
            ->setTemplateProvider(new \BO\Zmsdb\Helper\MailTemplateProvider($process))
            ->toResolvedEntity($collection, $config, 'deleted')
            ->withDepartment($department);
        $mail->testValid();
        error_log("outside");
        error_log($process->getFirstClient()->hasEmail());
        error_log($process->scope->hasEmailFrom());
        if ($process->getFirstClient()->hasEmail() && $process->scope->hasEmailFrom()) {
            error_log("inside");
            $mail = (new \BO\Zmsdb\Mail())->writeInQueue($mail, \App::$now, false);
            \App::$log->debug("Send mail", [$mail]);
        }
        return $mail;
    }

    protected function testProcessData($process)
    {
        $authCheck = (new ProcessRepository())->readAuthKeyByProcessId($process->getId());
        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif (
            $process->toProperty()->scope->preferences->client->emailRequired->get() &&
            ! $process->getFirstClient()->hasEmail()
        ) {
            throw new Exception\Process\EmailRequired();
        }
    }
}
