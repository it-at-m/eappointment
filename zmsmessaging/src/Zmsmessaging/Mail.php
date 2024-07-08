<?php
/**
 *
 * @package Zmsmessaging
 *
 */
namespace BO\Zmsmessaging;

use \BO\Zmsentities\Mimepart;

class Mail extends BaseController
{
    protected $messagesQueue = null;

    public function __construct($verbose = false, $maxRunTime = 50)
    {
        parent::__construct($verbose, $maxRunTime);
        $this->log("Read Mail QueueList start with limit " . \App::$mails_per_minute . " - " . \App::$now->format('c'));
        $queueList = \App::$http->readGetResult('/mails/', [
            'resolveReferences' => 2,
            'limit' => \App::$mails_per_minute
        ])->getCollection();
        if (null !== $queueList) {
            $this->messagesQueue = $queueList->sortByCustomKey('createTimestamp');
            $this->log("QueueList sorted by createTimestamp - " . \App::$now->format('c'));
        }
    }

    public function initQueueTransmission($action = false)
    {
        $resultList = [];
        if ($this->messagesQueue && count($this->messagesQueue)) {
            $batchSize = 5;
            $batches = array_chunk($this->messagesQueue, $batchSize);
            $processHandles = [];

            foreach ($batches as $batch) {
                $mailIds = array_map(fn($item) => $item['id'], $batch);
                $encodedMailIds = implode(',', $mailIds);
                $processHandles[] = $this->startProcess("php process_mail.php " . escapeshellarg($encodedMailIds));
            }

            $this->waitForAllProcesses($processHandles);
        } else {
            $resultList[] = array(
                'errorInfo' => 'No mail entry found in Database...'
            );
        }
        return $resultList;
    }

    private function startProcess($command)
    {
        $descriptorSpec = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"]  // stderr
        ];

        $process = proc_open($command, $descriptorSpec, $pipes);
        return $process;
    }

    private function waitForAllProcesses($processHandles)
    {
        foreach ($processHandles as $handle) {
            if (is_resource($handle)) {
                proc_close($handle);
            }
        }
    }
}
