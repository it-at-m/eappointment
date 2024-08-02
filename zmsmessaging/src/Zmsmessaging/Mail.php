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

    public function __construct($verbose = false, $maxRunTime = 100, $processMailScript = __DIR__ . '/MailProcessor.php')
    {
        parent::__construct($verbose, $maxRunTime);
        $this->processMailScript = $this->findProcessMailScript($processMailScript);
        //$this->cpuLimit = $this->getCpuLimit();
        $this->ramLimit = $this->getMemoryLimit();
        //$this->log("MailProcessor.php path: " . $this->processMailScript);
        $this->log("Read Mail QueueList start with limit ". \App::$mails_per_minute ." - ". \App::$now->format('c'));
        $queueList = \App::$http->readGetResult('/mails/', [
            'resolveReferences' => 2,
            'limit' => \App::$mails_per_minute,
            'onlyIds' => true
        ])->getCollection();
        error_log($queueList);
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
            if (count($this->messagesQueue) <= 20) {
                $this->log("Messages queue has less than or 10 items, sending immediately...");
                foreach ($this->messagesQueue as $item) {
                    if ($this->maxRunTime < $this->getSpendTime()) {
                        $this->log("Max Runtime exceeded - ". \App::$now->format('c'));
                        break;
                    }
                    try {
                        $this->sendQueueItem($action, $item['id']);
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
                $batchSize = min(count($this->messagesQueue), max(1, ceil(count($this->messagesQueue) / 12)));
                $this->log("Messages queue " . count($this->messagesQueue) . " items has more than 10 items, processing in batches of $batchSize...");
                $batches = array_chunk(iterator_to_array($this->messagesQueue), $batchSize);
                $this->log("Messages divided into " . count($batches) . " batches.");
                $processHandles = [];
                foreach ($batches as $index => $batch) {
                    $ids = array_map(function ($message) {
                        return $message['id'];
                    }, $batch);
                    $encodedIds = base64_encode(json_encode($ids));
                    $actionStr = is_array($action) ? json_encode($action) : ($action === false ? 'false' : ($action === true ? 'true' : (string)$action));

                    $idsStr = implode(', ', $ids);
                    $command = "php " . escapeshellarg($this->processMailScript) . " " . escapeshellarg($encodedIds) . " " . escapeshellarg($actionStr);
                    $processHandles[] = $this->startProcess($command, $index, $idsStr);
                }
                $this->monitorProcesses($processHandles);
            }
        } else {
            $resultList[] = array(
                'errorInfo' => 'No mail entry found in Database...'
            );
        }
        return $resultList;
    }

    public function sendQueueItem($action, $itemId)
    {
        $item = $this->getMailById($itemId);
        if (!$item) {
            $this->log("Failed to fetch mail data for ID: $itemId");
            return ['errorInfo' => 'Failed to fetch mail data'];
        }

        $result = [];
        $entity = new \BO\Zmsentities\Mail($item);
        $mailer = $this->getValidMailer($entity);
        if (! $mailer) {
            throw new \Exception("No valid mailer");
        }
    
        try {
            $result = $this->sendMailer($entity, $mailer, $action);
            if ($result instanceof PHPMailer) {
                $result = array(
                    'id' => ($result->getLastMessageID()) ? $result->getLastMessageID() : $entity->id,
                    'recipients' => $result->getAllRecipientAddresses(),
                    'mime' => $result->getMailMIME(),
                    'attachments' => $result->getAttachments(),
                    'customHeaders' => $result->getCustomHeaders(),
                );
                $this->log("Mail sent successfully with ID: " . $result['id']);
                if ($action) {
                    $this->deleteEntityFromQueue($entity);
                    $this->log("Mail deleted from queue with ID: " . $result['id']);
                }
            } else {
                $result = array(
                    'errorInfo' => $result->ErrorInfo
                );
                $this->log("Mail send failed with error: " . $result['errorInfo']);
            }
        } catch (\Exception $e) {
            $this->log("Exception while sending mail: " . $e->getMessage());
            $result = array(
                'errorInfo' => $e->getMessage()
            );
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
        //$this->log("Searching for MailProcessor.php at $path");
        if (file_exists($path)) {
            //$this->log("MailProcessor.php found at $path");
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

    private function executeCommandsSimultaneously($commandsWithIds)
    {
        //$this->log("Executing commands simultaneously...");
        $processHandles = [];
    
        foreach ($commandsWithIds as $index => $commandWithIds) {
            $command = $commandWithIds['command'];
            $ids = $commandWithIds['ids'];
            $processHandles[] = $this->startProcess($command, $index, $ids);
        }
    
        $this->monitorProcesses($processHandles);
    }
    
    private function startProcess($command, $batchIndex, $ids)
    {
        //$this->log("Starting process batch #$batchIndex with IDs: $ids");
        $descriptorSpec = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"]  // stderr
        ];
    
        $process = proc_open($command, $descriptorSpec, $pipes);
        if (is_resource($process)) {
            //$this->log("Process batch #$batchIndex started successfully for IDs: $ids");
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
}
