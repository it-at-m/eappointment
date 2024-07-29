<?php
/**
 *
* @package Zmsmessaging
*
*/
namespace BO\Zmsmessaging;

use BO\Zmsmessaging\BaseController;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use \BO\Zmsentities\Mimepart;

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
                    $this->sendQueueItemMultiProcessing($mailId, $action);
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
                    $command = "php " . escapeshellarg($this->processMailScript) . " " . escapeshellarg($encodedMailIds) . " " . escapeshellarg($action);
                    $this->log("Prepared command for batch #$index");
                    $commands[] = $command;
                }
    
                $this->executeCommandsSimultaneously($commands);
            } else {
                $this->log("Messages queue has 100 or more items, processing in batches of 10...");
                $batchSize = 12;
                $batches = array_chunk($this->messagesQueue, $batchSize);
                $this->log("Messages divided into " . count($batches) . " batches.");
                $commands = [];
    
                foreach ($batches as $index => $batch) {
                    $mailIds = array_map(fn($item) => $item['id'], $batch);
                    $encodedMailIds = implode(',', $mailIds);
                    $command = "php " . escapeshellarg($this->processMailScript) . " " . escapeshellarg($encodedMailIds) . " " . escapeshellarg($action);
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

    private function executeCommandsSimultaneously($commandsWithIds)
    {
        $this->log("Executing commands simultaneously...");
        $processHandles = [];
    
        foreach ($commandsWithIds as $index => $commandWithIds) {
            $command = $commandWithIds['command'];
            $ids = $commandWithIds['ids'];
            $this->log("Starting process for batch #$index with IDs: $ids");
            $processHandles[] = $this->startProcess($command, $index, $ids);
        }
    
        $this->monitorProcesses($processHandles);
    }    

    private function startProcess($command, $batchIndex, $ids)
    {
        $this->log("Starting process batch #$batchIndex with IDs: $ids");
        $descriptorSpec = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"]  // stderr
        ];
    
        $process = proc_open($command, $descriptorSpec, $pipes);
        if (is_resource($process)) {
            $this->log("Process batch #$batchIndex started successfully for IDs: $ids");
            return [
                'process' => $process,
                'pipes' => $pipes,
                'ids' => $ids  // Include IDs in the handle
            ];
        } else {
            $this->log("Failed to start process batch #$batchIndex for IDs: $ids");
            return null;
        }
    }    

    private function getMailById($itemId)
    {
        $endpoint = '/mails/' . $itemId . '/';

        try {
            $response = \App::$http->readGetResult($endpoint);
            return $response->getEntity();
        } catch (\Exception $e) {
            $this->log("Error fetching mail data: " . $e->getMessage() . "\n\n");
            echo "Error fetching mail data: " . $e->getMessage() . "\n\n";
            return null;
        }
    }

    public function sendQueueItemMultiProcessing($itemId, $action = false)
    {
        $this->log("Fetching mail data for ID: $itemId");
        echo "\nFetching mail data for ID: $itemId\n";

        $item = $this->getMailById($itemId);

        if (empty($item)) {
            $this->log("No mail data for mail ID: $itemId\n\n");
            echo "No mail data for mail ID: $itemId\n\n";
            return;
        }

        $entity = new \BO\Zmsentities\Mail($item);

        try {

            $mailer = $this->getValidMailer($entity);
            if (! $mailer) {
                throw new \Exception("No valid mailer");
            }
            $result = $this->sendMailer($entity, $mailer, $action);

            if ($result instanceof PHPMailer) {
                $result = array(
                    'id' => ($result->getLastMessageID()) ? $result->getLastMessageID() : $entity->id,
                    'recipients' => $result->getAllRecipientAddresses(),
                    'mime' => $result->getMailMIME(),
                    'attachments' => $result->getAttachments(),
                    'customHeaders' => $result->getCustomHeaders(),
                );
                if ($action) {
                    $this->deleteEntityFromQueue($entity);
                    $this->log("Mail sent and deleted successfully for ID: $itemId" . "\n\n");
                    echo "Mail sent and deleted successfully for ID: $itemId\n\n";
                }
            } else {
                $result = array(
                    'errorInfo' => $result->ErrorInfo,
                );
                $this->log("Mail could not be sent. PHPMailer Error: {$result['errorInfo']}\n\n");
                echo "Mail could not be sent. PHPMailer Error: {$result['errorInfo']}\n\n";
            }

        } catch (PHPMailerException $e) {
            $this->log("Mail could not be sent. PHPMailer Error: {$e->getMessage()}\n\n");
            echo "Mail could not be sent. PHPMailer Error: {$e->getMessage()}\n\n";
        } catch (Exception $e) {
            $this->log("Mail could not be sent. General Error: {$e->getMessage()}\n\n");
            echo "Mail could not be sent. General Error: {$e->getMessage()}\n\n";
        }
    }

    protected function readMailer(\BO\Zmsentities\Mail $entity)
    {
        $this->testEntity($entity);
        $encoding = 'base64';

        $htmlPart = '';
        $textPart = '';

        foreach ($entity->multipart as $part) {
            $mimepart = new Mimepart($part);
            if ($mimepart->isText()) {
                $textPart = $mimepart->getContent();
            }
            if ($mimepart->isHtml()) {
                $htmlPart = $mimepart->getContent();
            }
            if ($mimepart->isIcs()) {
                $icsPart = $mimepart->getContent();
            }
        }

        $mailer = new PHPMailer(true);
        $mailer->CharSet = 'UTF-8';
        $mailer->SetLanguage("de");
        $mailer->Encoding = $encoding;
        $mailer->IsHTML(true);
        $mailer->XMailer = \App::IDENTIFIER;
        $mailer->Subject = $entity['subject'];
        $mailer->AltBody = (isset($textPart)) ? $textPart : '';
        $mailer->Body = (isset($htmlPart)) ? $htmlPart : '';
        $mailer->SetFrom($entity['department']['email'], $entity['department']['name']);
        $mailer->AddAddress($entity->getRecipient(), $entity->client['familyName']);

        if (null !== $entity->getIcsPart()) {
            $mailer->AddStringAttachment(
                $icsPart,
                "Termin.ics",
                $encoding,
                "text/calendar; charset=utf-8; method=REQUEST"
            );
        }

        if (\App::$smtp_enabled) {
            $mailer->IsSMTP();
            $mailer->SMTPAuth = \App::$smtp_auth_enabled;
            $mailer->SMTPSecure = \App::$smtp_auth_method;
            $mailer->Port = \App::$smtp_port;
            $mailer->Host = \App::$smtp_host;
            $mailer->Username = \App::$smtp_username;
            $mailer->Password = \App::$smtp_password;
            if (\App::$smtp_skip_tls_verify) {
                $mailer->SMTPOptions['ssl'] = [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ];
            }
        }

        return $mailer;

    }

    protected function getValidMailer(\BO\Zmsentities\Mail $entity)
    {
        $message = '';
        $messageId = $entity['id'];
        try {
            $mailer = $this->readMailer($entity);
        // @codeCoverageIgnoreStart
        } catch (PHPMailerException $exception) {
            $message = "Message #$messageId PHPMailer Failure: ". $exception->getMessage();
            $code = $exception->getCode();
            \App::$log->warning($message, []);
        } catch (\Exception $exception) {
            $message = "Message #$messageId Exception Failure: ". $exception->getMessage();
            $code = $exception->getCode();
            \App::$log->warning($message, []);
        }
        if ($message) {
            if (428 == $code || 422 == $code) {
                $this->log("Build Mailer Failure ". $code .": deleteEntityFromQueue() - ". \App::$now->format('c'));
                $this->deleteEntityFromQueue($entity);
            } else {
                $this->log(
                    "Build Mailer Failure ". $code .": removeEntityOlderThanOneHour() - ". \App::$now->format('c')
                );
                $this->removeEntityOlderThanOneHour($entity);
            }
           
            $log = new Mimepart(['mime' => 'text/plain']);
            $log->content = $message;
            $this->log("Build Mailer Exception log message: ". $message);
            $this->log("Build Mailer Exception log readPostResult start - ". \App::$now->format('c'));
            \App::$http->readPostResult('/log/process/'. $entity->process['id'] .'/', $log, ['error' => 1]);
            $this->log("Build Mailer Exception log readPostResult finished - ". \App::$now->format('c'));
            return false;
        }

        // @codeCoverageIgnoreEnd
        return $mailer;
    }
}
