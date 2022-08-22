<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process as ProcessRepository;
use \BO\Zmsdb\Config;
use \BO\Zmsdb\Department;
use BO\Zmsentities\Process;

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
        \BO\Zmsdb\Connection\Select::setCriticalReadSession();

        $input = Validator::input()->isJson()->assertValid()->getValue();
        $process = new Process($input);
        $process->testValid();
        $this->testProcessData($process);
        $process = (new ProcessRepository())->readEntity($process->id, $process->authKey);

        $mail = $this->writeMail($process);
        $message = Response\Message::create($request);
        $message->data = ($mail->hasId()) ? $mail : null;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected static function writeMail(Process $process)
    {
        $config = (new Config)->readEntity();
        $department = (new Department())->readByScopeId($process->scope['id']);
        $processes  = [$process];
        $status = ($process->isWithAppointment()) ? 'appointment' : 'queued';

        if ($process->getFirstClient()->hasEmail()) {
            $additional = (new ProcessRepository())->readByMailAndStatuses(
                $process->getFirstClient()->email,
                [
                    Process::STATUS_CALLED,
                    Process::STATUS_CONFIRMED,
                    Process::STATUS_PENDING,
                    Process::STATUS_PICKUP,
                    Process::STATUS_PROCESSING,
                    Process::STATUS_RESERVED,
                ]
            );
            /** @var Process $compared */
            foreach ($additional as $key => $compared) {
                if ($compared->getId() === $process->getId()) {
                    unset($additional[$key]);
                }
            }
            $processes = array_merge($processes, $additional);
        }

        $mail = (new \BO\Zmsentities\Mail)
            ->toResolvedEntity($processes, $config, $status)
            ->withDepartment($department);
        $mail->testValid();
        if ($process->getFirstClient()->hasEmail() && $process->scope->hasEmailFrom()) {
            $mail = (new \BO\Zmsdb\Mail)->writeInQueue($mail, \App::$now, false);
            \App::$log->debug("Send mail", [$mail]);
        }
        return $mail;
    }

    protected function testProcessData($process)
    {
        $authCheck = (new ProcessRepository())->readAuthKeyByProcessId($process->id);
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
