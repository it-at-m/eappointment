<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Process\Service\Process as ProcessRepository;
use BO\Zmsbackend\Config\Service\Config;
use BO\Zmsbackend\Department\Service\Department;
use BO\Zmsentities\Process;
use BO\Zmsentities\Collection\ProcessList as Collection;

/**
 * @SuppressWarnings(Coupling)
 */
class ProcessPreconfirmationMail extends \BO\Zmsbackend\Api\BaseController
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
        \BO\Zmsbackend\Connection\Select::setCriticalReadSession();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $process = new Process($input);
        $process->testValid();
        $this->testProcessData($process);
        $process = (new ProcessRepository())->readEntity($process->id, $process->authKey);
        $mail = $this->writeMail($process);
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = ($mail->hasId()) ? $mail : null;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected static function writeMail(Process $process)
    {
        $config = (new \BO\Zmsbackend\Config\Service\Config())->readEntity();
        $department = (new \BO\Zmsbackend\Department\Service\Department())->readByScopeId($process->scope['id']);
        $status = ($process->isWithAppointment()) ? 'preconfirmed' : 'queued';
        $collection = static::getProcessListOverview($process, $config);

        $mail = (new \BO\Zmsentities\Mail())
            ->setTemplateProvider(new \BO\Zmsbackend\Helper\MailTemplateProvider($process))
            ->toResolvedEntity($collection, $config, $status)
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
        $authCheck = (new ProcessRepository())->readAuthKeyByProcessId($process->id);
        if (! $authCheck) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessNotFound();
        } elseif ($authCheck['authKey'] !== $process->authKey) {
            throw new \BO\Zmsbackend\Process\Exception\AuthKeyMatchFailed();
        } elseif (
            $process->toProperty()->scope->preferences->client->emailRequired->get() &&
            ! $process->getFirstClient()->hasEmail()
        ) {
            throw new \BO\Zmsbackend\Process\Exception\EmailRequired();
        }
    }

    public static function getProcessListOverview($process, $config)
    {
        $collection  = (new Collection())->addEntity($process);
        if (
            in_array(
                getenv('ZMS_ENV'),
                explode(',', $config->getPreference('appointments', 'enableSummaryByMail'))
            ) && $process->getFirstClient()->hasEmail()
        ) {
            $processList = (new ProcessRepository())->readListByMailAndStatusList(
                $process->getFirstClient()->email,
                [
                    \BO\Zmsentities\Process::STATUS_PRECONFIRMED
                ],
                2,
                50
            );

            //add list of found processes without the main process
            $collection->addList($processList->withOutProcessId($process->getId()));
        }
        return $collection;
    }
}
