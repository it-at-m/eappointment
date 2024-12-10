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
    protected $startTime;
    private $cpuLimit;
    private $ramLimit;

    public function __construct($verbose = false, $maxRunTime = 50)
    {
        parent::__construct($verbose, $maxRunTime);
        $this->log("Read Mail QueueList start with limit ". \App::$mails_per_minute ." - ". \App::$now->format('c'));
        $queueList = \App::$http->readGetResult('/mails/', [
            'resolveReferences' => 0,
            'limit' => \App::$mails_per_minute,
            'onlyIds' => true
        ])->getCollection();
        if (null !== $queueList) {
            $this->messagesQueue = $queueList->sortByCustomKey('createTimestamp');
        } else {
            $this->log("QueueList is null - " . \App::$now->format('c'));
        }
    }
    
    public function initQueueTransmission($action = false)
    {
        $resultList = [];
        if ($this->messagesQueue && count($this->messagesQueue)) {
            if ($this->maxRunTime < $this->getSpendTime()) {
                $this->log("Max Runtime exceeded before processing started - " . \App::$now->format('c'));
                return $resultList;
            }
            $this->log("Messages queue count - " . count($this->messagesQueue));
            if (count($this->messagesQueue) <= 50) {
                $this->log("Less than or equal to 50 items, sending immediately.");
    
                $itemIds = [];
                foreach ($this->messagesQueue as $item) {
                    if ($this->maxRunTime < $this->getSpendTime()) {
                        $this->log("Max Runtime exceeded during message loop - " . \App::$now->format('c'));
                        break;
                    }
                    $itemIds[] = $item['id'];
                }
    
                if (!empty($itemIds)) {
                    try {
                        $results = $this->sendQueueItems($action, $itemIds);
                        foreach ($results as $result) {
                            if (isset($result['errorInfo'])) {
                                $this->log("Error processing mail item: " . $result['errorInfo']);
                            }
                        }
                    } catch (\Exception $exception) {
                        $this->handleProcessingException($exception);
                    }
                }
            } else {
                $batchSize = min(count($this->messagesQueue), max(1, ceil(count($this->messagesQueue) / 12)));
                $this->log("More than 50 items, processing in batches of $batchSize.");
                $batches = array_chunk(iterator_to_array($this->messagesQueue), $batchSize);
                $this->log("Messages divided into " . count($batches) . " batches.");
    
                $processHandles = [];
                foreach ($batches as $index => $batch) {
                    if ($this->maxRunTime < $this->getSpendTime()) {
                        $this->log("Max Runtime exceeded during batch processing - " . \App::$now->format('c'));
                        break;
                    }
    
                    $ids = array_map(function ($message) {
                        return $message['id'];
                    }, $batch);
                    $encodedIds = base64_encode(json_encode($ids));
                    $actionStr = is_array($action) ? json_encode($action) : ($action === false ? 'false' : ($action === true ? 'true' : (string)$action));
    
                    $idsStr = implode(', ', $ids);
                    $command = "php " . escapeshellarg(__DIR__ . '/MailProcessor.php') . " " . escapeshellarg($encodedIds) . " " . escapeshellarg($actionStr);
                    $processHandles[] = $this->startProcess($command, $index, $idsStr);
                }
    
                if ($this->maxRunTime >= $this->getSpendTime()) {
                    $this->monitorProcesses($processHandles);
                } else {
                    $this->log("Max Runtime exceeded before process monitoring started - " . \App::$now->format('c'));
                }
            }
        } else {
            $resultList[] = array(
                'errorInfo' => 'No mail entry found in Database...'
            );
        }
    
        return $resultList;
    }
    
    public function sendQueueItems($action, array $itemIds)
    {
        $endpoint = '/mails/';
        $params = [
            'resolveReferences' => 2,
            'ids' => implode(',', $itemIds)
        ];
    
        try {
            $response = \App::$http->readGetResult($endpoint, $params);
            $mailItems = $response->getCollection();
        } catch (\Exception $e) {
            $this->log("Error fetching mail data: " . $e->getMessage() . "\n\n");
            return ['errorInfo' => 'Failed to fetch mail data'];
        }
    
        if (empty($mailItems)) {
            $this->log("No mail items found for the provided IDs.");
            return ['errorInfo' => 'No mail items found'];
        }
    
        $results = [];
        $processedItems = [];
        $successfullySentIds = [];
    
        foreach ($mailItems as $item) {
            $entity = new \BO\Zmsentities\Mail($item);
            $mailer = $this->getValidMailer($entity);
            if (!$mailer) {
                $this->log("No valid mailer for mail ID: " . $entity->id);
                continue;
            }
    
            try {
                $result = $this->sendMailer($entity, $mailer, $action);
                if ($result instanceof PHPMailer) {
                    $results[] = [
                        'id' => ($result->getLastMessageID()) ? $result->getLastMessageID() : $entity->id,
                        'recipients' => $result->getAllRecipientAddresses(),
                        'mime' => $result->getMailMIME(),
                        'attachments' => $result->getAttachments(),
                        'customHeaders' => $result->getCustomHeaders(),
                    ];
                    $successfullySentIds[] = $entity->id;
                } else {
                    $results[] = [
                        'errorInfo' => $result->ErrorInfo
                    ];
                    $this->log("Mail send failed with error: " . $result['errorInfo']);
                }
            } catch (\Exception $e) {
                $this->log("Exception while sending mail ID " . $entity->id . ": " . $e->getMessage());
                $results[] = ['errorInfo' => $e->getMessage()];
            }
    
            $processedItems[] = '[' . $entity->id . ', ' . $entity['process']['id'] . ', ' . $entity->createTimestamp . ']';
        }
    
        if ($action && !empty($successfullySentIds)) {
            try {
                $this->deleteEntitiesFromQueue($successfullySentIds);
            } catch (\Exception $e) {
                $this->log("Error deleting processed mails: " . $e->getMessage());
            }
        }
    
        $this->log("Processing finished for IDs [emailId, processId, createdTimestamp)]: " . implode(', ', $processedItems));
    
        return $results;
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
            \App::$http->readPostResult('/log/process/'. $entity->process['id'] .'/', $log, ['error' => 1]);
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
    
    private function startProcess($command, $batchIndex, $ids)
    {
        $descriptorSpec = [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"]  // stderr
        ];
    
        $process = proc_open($command . ' 2>&1', $descriptorSpec, $pipes); // Redirect stderr to stdout
        if (is_resource($process)) {
            return [
                'process' => $process,
                'pipes' => $pipes,
                'ids' => $ids
            ];
        } else {
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


    private function deleteEntitiesFromQueue(array $itemIds)
    {
        $endpoint = '/mails/';
        $params = [
            'ids' => implode(',', $itemIds)
        ];
    
        try {
            $response = \App::$http->readDeleteResult($endpoint, $params);
            return $response;
        } catch (\Exception $e) {
            $this->log("Error deleting mail data: " . $e->getMessage() . "\n\n");
            throw new \Exception("Failed to delete mail data");
        }
    }
    
}
