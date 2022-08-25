<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsapi;

use BO\Mellon\ValidMail;
use BO\Slim\Render;
use BO\Zmsapi\Helper\Version;
use BO\Zmsdb\Config as ConfigRepository;
use BO\Zmsdb\EventLog as EventLogRepository;
use BO\Zmsdb\Mail as Query;
use BO\Zmsdb\Process as ProcessRepository;
use BO\Zmsentities\Client;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\EventLog;
use BO\Zmsentities\Process;
use BO\Zmsentities\Helper\DateTime;
use BO\Zmsentities\Helper\Mailing;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request as SlimRequest;

class ProcessListSummaryMail extends BaseController
{
    public const PROCESSLIST_SUMMARY_REQUEST_REPETITION_SEC = 600;

    /**
     * @SuppressWarnings(Param)
     * @param RequestInterface|SlimRequest $request
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $mailAddress   = $request->getQueryParam('mail');
        $logRepository = new EventLogRepository();
        $eventLogEntries = $logRepository->readByNameAndRef(
            EventLog::CLIENT_PROCESSLIST_REQUEST,
            $logRepository->hashStringValue($mailAddress)
        );
        $youngestTime = new DateTime('-' . self::PROCESSLIST_SUMMARY_REQUEST_REPETITION_SEC . ' seconds');
        if ($eventLogEntries->count() > 0 && $eventLogEntries->getLast()->creationDateTime > $youngestTime) {
            throw new Exception\Process\ProcessListSummaryTooOften();
        }

        $processes = new ProcessList();
        $client = (new Client())->addData(['email' => $mailAddress, 'familyName' => $request->getQueryParam('name') ?? '']);

        //validate email address
        if ((new ValidMail($mailAddress))->isMail()->hasFailed() === false) {
            $processes = (new ProcessRepository())->readByMailAndStatuses(
                $mailAddress,
                [
                    Process::STATUS_CALLED,
                    Process::STATUS_CONFIRMED,
                    Process::STATUS_PENDING,
                    Process::STATUS_PICKUP,
                    Process::STATUS_PROCESSING,
                    Process::STATUS_RESERVED,
                ]
            );

            if (count($processes) > 0) {
                $client = $processes->getFirst()->getFirstClient();
            }
        }

        $lang = $request->getQueryParam('language');
        $lang = $lang && strlen($lang) === 2 ? $lang : 'de';
        $mail = (new Mailing((new ConfigRepository)->readEntity()))->createProcessListSummaryMail($processes, $client, $lang);
        $persisted = (new Query())->writeInQueue($mail, \App::$now);

        $message = Response\Message::create($request);
        $message->data = $persisted;

        $newLogEntry = new EventLog();
        $newLogEntry->addData([
            'name' => EventLog::CLIENT_PROCESSLIST_REQUEST,
            'origin' => 'zmsapi ' . Version::getString(),
            'referenceType' => 'mail.recipient.hash',
            'reference' => $logRepository->hashStringValue($mailAddress),
            'context' => ['found' => $processes->getIds()],
        ])->setSecondsToLive(EventLog::LIVETIME_DAY);

        $logRepository->writeEntity($newLogEntry);

        $response = Render::withLastModified($response, time(), '0');

        return Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
