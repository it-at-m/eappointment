<?php
/**
 *
* @package Zmsmessaging
*
*/
namespace BO\Zmsmessaging;

use \BO\Zmsentities\Mimepart;
use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\Exception as PHPMailerException;

class Mail extends BaseController
{
    protected $messagesQueue = null;
    private $processMailScript;
    protected $startTime;
    private $cpuLimit;
    private $ramLimit;

    public function __construct($verbose = false, $maxRunTime = 50, $processMailScript = __DIR__ . '/MailProcessor.php')
    {
        parent::__construct($verbose, $maxRunTime);
        $this->processMailScript = $this->findProcessMailScript($processMailScript);
        //$this->cpuLimit = $this->getCpuLimit();
        $this->ramLimit = $this->getMemoryLimit();
        $this->log("MailProcessor.php path: " . $this->processMailScript);
        $this->log("Read Mail QueueList start with limit ". \App::$mails_per_minute ." - ". \App::$now->format('c'));
        $queueList = \App::$http->readGetResult('/mails/', [
            'resolveReferences' => 2,
            'limit' => \App::$mails_per_minute
        ])->getCollection();
        if (null !== $queueList) {
            $this->messagesQueue = $queueList->sortByCustomKey('createTimestamp');
            $this->log("QueueList sorted by createTimestamp - ". \App::$now->format('c'));
        } else {
            $this->log("QueueList is null - " . \App::$now->format('c'));
        }
    }

    public function initQueueTransmission($action = false)
    {
        $resultList = [];
        if ($this->messagesQueue && count($this->messagesQueue)) {
            foreach ($this->messagesQueue as $item) {
                if ($this->maxRunTime < $this->getSpendTime()) {
                    $this->log("Max Runtime exceeded - ". \App::$now->format('c'));
                    break;
                }
                try {

                    if (count($this->messagesQueue) <= 10) {
                        $this->log("Messages queue has less than or 10 items, sending immediately...");
                        foreach ($this->messagesQueue as $message) {
                            $mailId = $message['id'];
                            $this->sendQueueItem($action, $item);
                        }
                    } else {
                        $batchSize = (count($this->messagesQueue) <= 100) ? 5 : 12;
                        $this->log("Messages queue has more than 10 items, processing in batches of $batchSize...");
                        $batches = array_chunk(iterator_to_array($this->messagesQueue), $batchSize);
                        $this->log("Messages divided into " . count($batches) . " batches.");
                        $commands = [];
                        foreach ($batches as $index => $batch) {
                            $encodedBatch = base64_encode(json_encode($batch));
                            if (!is_string($encodedBatch)) {
                                throw new \Exception("Expected base64_encode to return a string");
                            }
                            $command = "php " . escapeshellarg($this->processMailScript) . " " . escapeshellarg($encodedBatch) . " " . escapeshellarg($action);
                            $this->log("Prepared command for batch #$index: $command");
                            $commands[] = $command;
                        }
                        $this->executeCommandsSimultaneously($commands);
                    }
  
                } catch (\Exception $exception) {
                    $log = new Mimepart(['mime' => 'text/plain']);
                    $log->content = $exception->getMessage();
                    if (isset($item['process']) && isset($item['process']['id'])) {
                        $this->log("Init Queue Exception message: ". $log->content .' - '. \App::$now->format('c'));
                        $this->log("Init Queue Exception log readPostResult start - ". \App::$now->format('c'));
                        \App::$http->readPostResult('/log/process/'. $item['process']['id'] .'/', $log, ['error' => 1]);
                        $this->log("Init Queue Exception log readPostResult finished - ". \App::$now->format('c'));
                    }
                    //\App::$log->error($log->content);
                }
            }
        } else {
            $resultList[] = array(
                'errorInfo' => 'No mail entry found in Database...'
            );
        }
        return $resultList;
    }

    public function sendQueueItem($action, $item)
    {
        $result = [];
        $entity = new \BO\Zmsentities\Mail($item);
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
            }
        } else {
            // @codeCoverageIgnoreStart
            $result = array(
                'errorInfo' => $result->ErrorInfo
            );
            // @codeCoverageIgnoreEnd
        }
        return $result;
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

    /**
     * @SuppressWarnings("CyclomaticComplexity")
     * @SuppressWarnings("NPathComplexity")
     */
    protected function readMailer(\BO\Zmsentities\Mail $entity)
    {
        $this->log("Build Mailer: testEntity() - ". \App::$now->format('c'));
        $this->testEntity($entity);
        $encoding = 'base64';
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

        $this->log("Build Mailer: new PHPMailer() - ". \App::$now->format('c'));
        $mailer = new PHPMailer(true);
        $mailer->CharSet = 'UTF-8';
        $mailer->SMTPDebug = \App::$smtp_debug;
        $mailer->SetLanguage("de");
        $mailer->Encoding = $encoding;
        $mailer->IsHTML(true);
        $mailer->XMailer = \App::IDENTIFIER;
        $mailer->Subject = $entity['subject'];
        $mailer->AltBody = (isset($textPart)) ? $textPart : '';
        $mailer->Body = (isset($htmlPart)) ? $htmlPart : '';
        $mailer->SetFrom($entity['department']['email'], $entity['department']['name']);
        $this->log("Build Mailer: addAddress() - ". \App::$now->format('c') . " arguments: "
            . $entity->getRecipient() . ' - ' . $entity->client['familyName']);
        $mailer->AddAddress($entity->getRecipient(), $entity->client['familyName']);

        if (null !== $entity->getIcsPart()) {
            $this->log("Build Mailer: AddStringAttachment() - ". \App::$now->format('c'));
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

}