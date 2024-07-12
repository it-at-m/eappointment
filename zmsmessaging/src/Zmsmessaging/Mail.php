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
    private $cpuLimit;
    private $ramLimit;

    public function __construct($verbose = false, $maxRunTime = 50, $processMailScript = __DIR__ . '/process_mail.php')
    {
        $this->startTime = microtime(true);
        parent::__construct($verbose, $maxRunTime);
        $this->processMailScript = $this->findProcessMailScript($processMailScript);
        $this->cpuLimit = $this->getCpuLimit();
        $this->ramLimit = $this->getMemoryLimit();
        $this->log("process_mail.php path: " . $this->processMailScript);
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
            $array[] = $item;
        }
        $this->log("Conversion complete, array size: " . count($array));
        return $array;
    }

    public function initQueueTransmission($action = false)
    {
        $this->log("Initializing queue transmission...");
        $resultList = [];
        if ($this->messagesQueue && count($this->messagesQueue)) {
            $this->log("Messages queue is not empty, processing batches...");
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

    private function monitorProcesses($processHandles)
    {
        $this->log("Monitoring processes");
        $running = true;

        while ($running) {
            $running = false;
            foreach ($processHandles as &$handle) {
                if (is_resource($handle['process'])) {
                    $status = proc_get_status($handle['process']);
                    if ($status['running']) {
                        $this->logResourceUsage();
                        $running = true;
                    } else {
                        $this->log("Process finished with command: " . $status['command']);
                        proc_close($handle['process']);
                        $handle['process'] = null;
                    }
                }
            }
            usleep(100000); // Sleep for 0.1 seconds before checking again
        }
        $this->log("All processes have finished");
        $this->logTotalExecutionTime(); // Log total execution time at the end
    }

    private function logResourceUsage()
    {
        $cpuUsage = $this->getCpuUsage();
        $memoryUsage = $this->getMemoryUsage();

        $cpuLimitPercent = ($cpuUsage / $this->cpuLimit) * 100;
        $memoryLimitPercent = ($memoryUsage / $this->ramLimit) * 100;

        $this->log(sprintf("Current CPU usage: %07.2f%% of %d limit", $cpuLimitPercent, $this->cpuLimit));
        $this->log(sprintf("Current Memory usage: %07.2f%% of %dMB limit", $memoryLimitPercent, $this->ramLimit));
    }

    private function getCpuLimit()
    {
        $cpuLimitFile = '/sys/fs/cgroup/cpu/cpu.cfs_quota_us';
        if (file_exists($cpuLimitFile)) {
            $cpuLimit = intval(file_get_contents($cpuLimitFile)) / 1000; // Convert to ms
            return $cpuLimit > 0 ? $cpuLimit : null;
        }
        return null;
    }

    private function getCpuUsage()
    {
        $cpuUsageFile = '/sys/fs/cgroup/cpu/cpuacct.usage';
        if (file_exists($cpuUsageFile)) {
            $cpuUsage = intval(file_get_contents($cpuUsageFile)) / 1000000; // Convert to ms
            return $cpuUsage;
        }
        return null;
    }

    private function getMemoryLimit()
    {
        $memLimitFile = '/sys/fs/cgroup/memory/memory.limit_in_bytes';
        if (file_exists($memLimitFile)) {
            $memLimit = intval(file_get_contents($memLimitFile)) / (1024 * 1024); // Convert to MB
            return $memLimit > 0 ? $memLimit : null;
        }
        return null;
    }

    private function getMemoryUsage()
    {
        $memUsageFile = '/sys/fs/cgroup/memory/memory.usage_in_bytes';
        if (file_exists($memUsageFile)) {
            $memUsage = intval(file_get_contents($memUsageFile)) / (1024 * 1024); // Convert to MB
            return $memUsage;
        }
        return null;
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
