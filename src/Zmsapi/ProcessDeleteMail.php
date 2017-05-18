<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Mail as Query;
use \BO\Zmsdb\Config;
use \BO\Zmsdb\Process;

/**
 * @SuppressWarnings(Coupling)
 */
class ProcessDeleteMail extends BaseController
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
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $process = new \BO\Zmsentities\Process($input);
        $process->testValid();
        $this->testProcessData($process);

        $config = (new Config())->readEntity();
        $mail = (new \BO\Zmsentities\Mail())->toResolvedEntity($process, $config);

        if ($process->getFirstClient()->hasEmail()) {
            $mail = (new \BO\Zmsdb\Mail)->writeInQueue($mail);
            \App::$log->debug("Send mail", [$mail]);
        }

        $message = Response\Message::create($request);
        $message->data = $mail;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }

    protected function testProcessData($process)
    {
        $authCheck = (new Process())->readAuthKeyByProcessId($process->id);
        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authCheck['authKey'] != $process->authKey && $authCheck['authName'] != $process->authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        } elseif ($process->toProperty()->scope->preferences->client->emailRequired->get() &&
            ! $process->getFirstClient()->hasEmail()
        ) {
            throw new Exception\Process\EmailRequired();
        }
    }
}
