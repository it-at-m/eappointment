<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsapi\Helper\Version;
use BO\Zmsdb\Config as ConfigRepository;
use BO\Zmsdb\EventLog as EventLogRepository;
use BO\Zmsdb\Mail as Query;
use BO\Zmsdb\Process as ProcessRepository;
use BO\Zmsdb\Department as DepartmentRepository;
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
class ProcessListSummaryMail extends BaseController
{
    public const PROCESSLIST_SUMMARY_REQUEST_REPETITION_SEC = 600;

    /**
     * @SuppressWarnings(Param)
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     */
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
                Process::STATUS_CONFIRMED,
                Process::STATUS_PICKUP
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

        $message = Response\Message::create($request);
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
            throw new Exception\Mail\MailSenderFromMissing();
        }
        return $department;
    }

    protected function setWithProcessClient(Mail $entity, $mailAddress): Mail
    {
        $process = new Process();
        $client = $entity->getClient();
        $client = ($client->hasEmail()) ? $client : $process->getFirstClient()->email = $mailAddress;
        $entity->process = $process ;
        return $entity;
    }

    protected function writeLogEntry($mailAddress, ProcessList $collection)
    {
        $logRepository = new EventLogRepository();
        $newLogEntry = new EventLog();
        $newLogEntry->addData([
            'name' => EventLog::CLIENT_PROCESSLIST_REQUEST,
            'origin' => 'zmsapi ' . Version::getString(),
            'referenceType' => 'mail.recipient.hash',
            'reference' => $logRepository->hashStringValue($mailAddress),
            'context' => ['found' => $collection->getIds()],
        ])->setSecondsToLive(EventLog::LIVETIME_DAY);

        $logRepository->writeEntity($newLogEntry);
    }

    protected function testEventLogEntries($mailAddress)
    {
        $logRepository = new EventLogRepository();
        $eventLogEntries = $logRepository->readByNameAndRef(
            EventLog::CLIENT_PROCESSLIST_REQUEST,
            $logRepository->hashStringValue($mailAddress)
        );
        $youngestTime = new DateTime('-' . self::PROCESSLIST_SUMMARY_REQUEST_REPETITION_SEC . ' seconds');
        if ($eventLogEntries->count() > 0 && $eventLogEntries->getLast()->creationDateTime > $youngestTime) {
            throw new Exception\Process\ProcessListSummaryTooOften();
        }
    }
}
