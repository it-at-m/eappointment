<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process;
use \BO\Zmsdb\Config;
use \BO\Zmsdb\Department;

/**
 * @SuppressWarnings(Coupling)
 */
class ProcessConfirmationMail extends BaseController
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
        $process = (new Process())->readEntity($process->id, $process->authKey);

        \BO\Zmsdb\Connection\Select::getWriteConnection();
    
        $mail = $this->writeMail($process);
        $message = Response\Message::create($request);
        $message->data = ($mail->hasId()) ? $mail : null;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected static function writeMail(\BO\Zmsentities\Process $process)
    {
        $config = (new Config)->readEntity();
        $department = (new Department())->readByScopeId($process->scope['id']);
        $mail = (new \BO\Zmsentities\Mail)->toResolvedEntity($process, $config)->withDepartment($department);
        $mail->testValid();
        if ($process->getFirstClient()->hasEmail() && $process->scope->hasEmailFrom()) {
            $mail = (new \BO\Zmsdb\Mail)->writeInQueue($mail, \App::$now);
            \App::$log->debug("Send mail", [$mail]);
        }
        return $mail;
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
