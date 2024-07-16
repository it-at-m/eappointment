<?php
/**
 *
* @package Zmsmessaging
*
*/
namespace BO\Zmsmessaging;

use BO\Zmsmessaging\BaseController;

class Mail extends BaseController
{
    protected $messagesQueue = null;
    private $processMailScript;
    protected $startTime;
    private $cpuLimit;
    private $ramLimit;

    public function __construct($verbose = false, $maxRunTime = 50, $processMailScript = __DIR__ . '/MailProcessor.php')
    {
        $this->startTime = microtime(true);
        parent::__construct($verbose, $maxRunTime);
        $this->processMailScript = $this->findProcessMailScript($processMailScript);
        $this->cpuLimit = $this->getCpuLimit();
        $this->ramLimit = $this->getMemoryLimit();
        $this->log("MailProcessor.php path: " . $this->processMailScript);
        $this->log("Read Mail QueueList start with limit " . \App::$mails_per_minute . " - " . \App::$now->format('c'));
        $queueList = \App::$http->readGetResult('/mails/', [
            'resolveReferences' => 2,
            'limit' => \App::$mails_per_minute
        ])->getCollection();
        if (null !== $queueList) {
            $this->messagesQueue = $this->convertCollectionToArray($queueList->sortByCustomKey('createTimestamp'));
            $this->log("QueueList sorted by createTimestamp - " . \App::$now->format('c'));
        } else {
            $this->log("QueueList is null - " . \App::$now->format('c'));
        }
    }

    private function findProcessMailScript($path)
    {
        $this->log("Searching for MailProcessor.php at $path");
        if (file_exists($path)) {
            $this->log("MailProcessor.php found at $path");
            return realpath($path);
        } else {
            $this->log("MailProcessor.php not found at $path. Searching for file...");
            $files = $this->searchFile(__DIR__, 'MailProcessor.php');
            if (!empty($files)) {
                $this->log("MailProcessor.php found at " . $files[0]);
                return realpath($files[0]);
            } else {
                $this->log("MailProcessor.php could not be found.");
                throw new \Exception("MailProcessor.php could not be found.");
            }
        }
    }

    private function searchFile($directory, $filename)
    {
        $this->log("Starting file search in directory: $directory for filename: $filename");
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $files = [];
        foreach ($iterator as $file) {
            if ($file->getFilename() === $filename) {
                $this->log("File found: " . $file->getPathname());
                $files[] = $file->getPathname();
            }
        }

        if (empty($files)) {
            $this->log("No files found during search.");
        }

        return $files;
    }

    public function initQueueTransmission($action = false)
    {
        $this->log("Initializing queue transmission...");
        $resultList = [];
        if ($this->messagesQueue && count($this->messagesQueue)) {
            $this->log("Messages queue is not empty, processing...");

            if (count($this->messagesQueue) <= 10) {
                $this->log("Messages queue has less than or 10 items, sending immediately...");
                foreach ($this->messagesQueue as $message) {
                    $mailId = $message['id'];
                    $this->sendAndDeleteEmail($mailId, $message); 
                }
            } else if (count($this->messagesQueue) <= 100) {
                $this->log("Messages queue has more than 10 items, processing in batches of 5...");
                $batchSize = 5;
                $batches = array_chunk($this->messagesQueue, $batchSize);
                $this->log("Messages divided into " . count($batches) . " batches.");
                $commands = [];

                foreach ($batches as $index => $batch) {
                    $mailIds = array_map(fn($item) => $item['id'], $batch);
                    $encodedMailIds = implode(',', $mailIds);
                    $command = "php " . escapeshellarg($this->processMailScript) . " " . escapeshellarg($encodedMailIds);
                    $this->log("Prepared command for batch #$index: $command");
                    $commands[] = $command;
                }

                $this->executeCommandsSimultaneously($commands);
            } else {
                $this->log("Messages queue has 100 or more items, processing in batches of 10...");
                $batchSize = 10;
                $batches = array_chunk($this->messagesQueue, $batchSize);
                $this->log("Messages divided into " . count($batches) . " batches.");
                $commands = [];

                foreach ($batches as $index => $batch) {
                    $mailIds = array_map(fn($item) => $item['id'], $batch);
                    $encodedMailIds = implode(',', $mailIds);
                    $command = "php " . escapeshellarg($this->processMailScript) . " " . escapeshellarg($encodedMailIds);
                    $this->log("Prepared command for batch #$index: $command");
                    $commands[] = $command;
                }

                $this->executeCommandsSimultaneously($commands);
            }
        } else {
            $this->log("Messages queue is empty.");
            $resultList[] = array(
                'errorInfo' => 'No mail entry found in Database...'
            );
        }
        $this->log("Queue transmission initialization complete.");
        return $resultList;
    }

    private function executeCommandsSimultaneously($commands)
    {
        $this->log("Executing commands simultaneously...");
        $processHandles = [];

        foreach ($commands as $index => $command) {
            $this->log("Starting process for batch #$index with command: $command");
            $processHandles[] = $this->startProcess($command, $index);
        }

        $this->monitorProcesses($processHandles);
    }

    private function startProcess($command, $batchIndex)
    {
        $this->log("Starting process batch #$batchIndex with command: $command");
        $descriptorSpec = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"]  // stderr
        ];

        $process = proc_open($command, $descriptorSpec, $pipes);
        if (is_resource($process)) {
            $this->log("Process batch #$batchIndex started successfully");
            return [
                'process' => $process,
                'pipes' => $pipes
            ];
        } else {
            $this->log("Failed to start process batch #$batchIndex: $command");
            return null;
        }
    }
}
