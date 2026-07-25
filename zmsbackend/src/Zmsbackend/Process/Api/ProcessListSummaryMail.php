<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Helper\Version;
use BO\Zmsbackend\Config\Service\Config as ConfigRepository;
use BO\Zmsbackend\EventLog\Service\EventLog as EventLogRepository;
use BO\Zmsbackend\Mail\Service\Mail as Query;
use BO\Zmsbackend\Process\Service\Process as ProcessRepository;
use BO\Zmsbackend\Department\Service\Department as DepartmentRepository;
use BO\Zmsentities\Client;
use BO\Zmsentities\Mail;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\EventLog;
use BO\Zmsentities\Process;
use BO\Zmsentities\Department;
use BO\Zmsentities\Helper\DateTime;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @SuppressWarnings(Coupling)
 */
class ProcessListSummaryMail extends \BO\Zmsbackend\Api\BaseController
{
    public const PROCESSLIST_SUMMARY_REQUEST_REPETITION_SEC = 600;

    /**
     * @SuppressWarnings(Param)
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $mailAddress = $validator->getParameter('mail')->isMail()->hasDNS()->assertValid()->getValue();
        $limit = $validator->getParameter('limit')->isNumber()->setDefault(50)->getValue();
        $this->testEventLogEntries($mailAddress);

        $collection = (new ProcessRepository())->readListByMailAndStatusList(
            $mailAddress,
            [
                \BO\Zmsentities\Process::STATUS_CONFIRMED
            ],
            2,
            $limit
        );

        $config = (new ConfigRepository())->readEntity();
        $department = $this->readDepartment($config, $collection->getFirst());

        $mail = (new Mail())->toResolvedEntity($collection, $config, 'overview')->withDepartment($department);
        $mail = $this->setWithProcessClient($mail, $mailAddress);
        $mail->testValid();

        $persisted = null;
        if ($department) {
            $persisted = (new Query())->writeInQueue($mail, \App::$now, false);
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $persisted;

        $this->writeLogEntry($mailAddress, $collection);
        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
    }

    protected function readDepartment($config, $process = null): Department
    {
        $department = (null != $process && null != $process->getScopeId()) ?
            (new DepartmentRepository())->readByScopeId($process->getScopeId(), 0) :
            (new DepartmentRepository())->readEntity($config->getPreference('mailings', 'noReplyDepartmentId'));
        if (null === $department) {
            throw new \BO\Zmsbackend\Mail\Exception\MailSenderFromMissing();
        }
        return $department;
    }

    protected function setWithProcessClient(Mail $entity, $mailAddress): Mail
    {
        $process = new \BO\Zmsbackend\Process\Service\Process();
        $client = $entity->getClient();
        if ($client === null || !$client->hasEmail()) {
            $process->getFirstClient()->email = $mailAddress;
        }
        $entity->process = $process ;

        return $entity;
    }

    protected function writeLogEntry($mailAddress, ProcessList $collection)
    {
        $logRepository = new EventLogRepository();
        $newLogEntry = new \BO\Zmsbackend\EventLog\Service\EventLog();
        $newLogEntry->addData([
            'name' => \BO\Zmsbackend\EventLog\Service\EventLog::CLIENT_PROCESSLIST_REQUEST,
            'origin' => 'zmsbackend ' . Version::getString(),
            'referenceType' => 'mail.recipient.hash',
            'reference' => $logRepository->hashStringValue($mailAddress),
            'context' => ['found' => $collection->getIds()],
        ])->setSecondsToLive(\BO\Zmsbackend\EventLog\Service\EventLog::LIVETIME_DAY);

        $logRepository->writeEntity($newLogEntry);
    }

    protected function testEventLogEntries($mailAddress)
    {
        $logRepository = new EventLogRepository();
        $eventLogEntries = $logRepository->readByNameAndRef(
            \BO\Zmsbackend\EventLog\Service\EventLog::CLIENT_PROCESSLIST_REQUEST,
            $logRepository->hashStringValue($mailAddress)
        );
        $youngestTime = new DateTime('-' . self::PROCESSLIST_SUMMARY_REQUEST_REPETITION_SEC . ' seconds');
        if ($eventLogEntries->count() > 0 && $eventLogEntries->getLast()->creationDateTime > $youngestTime) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessListSummaryTooOften();
        }
    }
}
