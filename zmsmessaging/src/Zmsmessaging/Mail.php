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
    private $processMailScript;
    protected $startTime;

    public function __construct($verbose = false, $maxRunTime = 50, $processMailScript = __DIR__ . '/process_mail.php')
    {
        $this->startTime = microtime(true);
        parent::__construct($verbose, $maxRunTime);
        $this->processMailScript = $this->findProcessMailScript($processMailScript);
        $this->log("process_mail.php path: " . $this->processMailScript); // Log the path
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
        $this->log("Searching for process_mail.php at $path");
        if (file_exists($path)) {
            $this->log("process_mail.php found at $path");
            return realpath($path);
        } else {
            $this->log("process_mail.php not found at $path. Searching for file...");
            $files = $this->searchFile(__DIR__, 'process_mail.php');
            if (!empty($files)) {
                $this->log("process_mail.php found at " . $files[0]);
                return realpath($files[0]);
            } else {
                $this->log("process_mail.php could not be found.");
                throw new \Exception("process_mail.php could not be found.");
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

    private function convertCollectionToArray($collection)
    {
        $this->log("Converting collection to array");
        $array = [];
        foreach ($collection as $item) {
            //$this->log("Processing item: " . print_r($item, true));
            $array[] = $item;
        }
        $this->log("Conversion complete, array size: " . count($array));
        return $array;
    }

    public function initQueueTransmission($action = false)
    {
        $this->log("Initializing queue transmission");
        $resultList = [];
        if ($this->messagesQueue && count($this->messagesQueue)) {
            $this->log("Messages found in queue, count: " . count($this->messagesQueue));
            $batchSize = 5;
            $batches = array_chunk($this->messagesQueue, $batchSize);
            $processHandles = [];

            foreach ($batches as $batchIndex => $batch) {
                $this->log("Processing batch #$batchIndex with size: " . count($batch));
                $mailIds = array_map(fn($item) => $item['id'], $batch);
                $encodedMailIds = implode(',', $mailIds);
                $command = "php " . escapeshellarg($this->processMailScript) . " " . escapeshellarg($encodedMailIds);
                $processHandles[] = $this->startProcess($command, $batchIndex);
                $this->log("Started process for batch #$batchIndex with command: $command");
            }

            $this->monitorProcesses($processHandles);
        } else {
            $this->log("No messages in queue");
            $resultList[] = array(
                'errorInfo' => 'No mail entry found in Database...'
            );
        }
        return $resultList;
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

    private function monitorProcesses($processHandles)
    {
        //$this->log("Monitoring processes");
        $running = true;

        while ($running) {
            $running = false;
            foreach ($processHandles as &$handle) {
                if (is_resource($handle['process'])) {
                    $status = proc_get_status($handle['process']);
                    //$this->log("Process status: " . print_r($status, true));
                    if ($status['running']) {
                        $running = true;
                    } else {
                        $this->log("Process finished with command: " . $status['command']);
                        proc_close($handle['process']);
                        $handle['process'] = null;
                    }
                }
            }
            usleep(500000); // Sleep for 0.5 seconds before checking again
        }
        $this->log("All processes have finished");
        $this->logTotalExecutionTime(); // Log total execution time at the end
    }

    private function logTotalExecutionTime()
    {
        $endTime = microtime(true);
        $executionTime = $endTime - $this->startTime;
        $this->log(sprintf("Total execution time: %07.3f seconds", $executionTime));
    }

    public function log($message)
    {
        if (is_array($message)) {
            $message = print_r($message, true);
        }

        $time = $this->getSpendTime();
        $memory = memory_get_usage() / (1024 * 1024);
        $text = sprintf("[Init Messaging log %07.3fs %07.1fmb] %s", $time, $memory, $message);
        static::$logList[] = $text;
        if ($this->verbose) {
            error_log('verbose is: ' . $this->verbose);
            error_log($text);
        }
        return $this;
    }
}
