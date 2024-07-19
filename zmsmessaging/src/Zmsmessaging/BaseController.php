<?php
/**
 *
* @package Zmsmessaging
*
*/
namespace BO\Zmsmessaging;

use \BO\Zmsentities\Mail;
use \BO\Zmsentities\Notification;
use \BO\Zmsentities\Mimepart;
use \BO\Mellon\Validator;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class BaseController
{
    protected $verbose = false;
    protected static $logList = [];
    protected $workstation = null;
    protected $startTime;
    protected $maxRunTime = 50;

    public function __construct($verbose = false, $maxRunTime = 50)
    {
        $this->verbose = $verbose;
        $this->startTime = microtime(true);
        $this->maxRunTime = $maxRunTime;
    }

    public static function getLogList()
    {
        return static::$logList;
    }

    public static function clearLogList()
    {
        static::$logList = [];
    }

    protected function getSpendTime()
    {
        $time = round(microtime(true) - $this->startTime, 3);
        return $time;
    }

    protected function sendMailer(\BO\Zmsentities\Schema\Entity $entity, $mailer = null, $action = false)
    {
        // @codeCoverageIgnoreStart
        $hasSendSuccess = ($action) ? $mailer->Send() : $action;
        if (false !== $action && null !== $mailer && ! $hasSendSuccess) {
            $this->log("Exception: SendingFailed  - ". \App::$now->format('c'));
            throw new Exception\SendingFailed();
        }
        // @codeCoverageIgnoreEnd
        $this->log("Send Mailer: sending succeeded - ". \App::$now->format('c'));
        $log = new Mimepart(['mime' => 'text/plain']);
        $log->content = ($entity instanceof Mail) ? $entity->subject : $entity->message;
        $this->log("Send Mailer: log readPostResult start - ". \App::$now->format('c'));
        \App::$http->readPostResult('/log/process/'. $entity->process['id'] .'/', $log);
        $this->log("Send Mailer: log readPostResult finished - ". \App::$now->format('c'));
        return $mailer;
    }

    protected function removeEntityOlderThanOneHour($entity)
    {
        if (3600 < \App::$now->getTimestamp() - $entity->createTimestamp) {
            $this->log("Delete Entity: removeEntityOlderThanOneHour start - ". \App::$now->format('c'));
            $this->deleteEntityFromQueue($entity);
            $this->log("Delete Entity: removeEntityOlderThanOneHour finished - ". \App::$now->format('c'));
            $log = new Mimepart(['mime' => 'text/plain']);
            $log->content = 'Zmsmessaging Failure: Queue entry older than 1 hour has been removed';
            $this->log("Delete Entity: log readPostResult start - ". \App::$now->format('c'));
            \App::$http->readPostResult('/log/process/'. $entity->process['id'] .'/', $log, ['error' => 1]);
            \App::$log->warning($log->content);
            $this->log("Delete Entity: log readPostResult finished - ". \App::$now->format('c'));
            return false;
        }
    }

    public function deleteEntityFromQueue($entity)
    {
        $type = ($entity instanceof \BO\Zmsentities\Mail) ? 'mails' : 'notification';
        try {
            $this->log("Delete Entity: readDeleteResult start - ". \App::$now->format('c'));
            $entity = \App::$http->readDeleteResult('/'. $type .'/'. $entity->id .'/')->getEntity();
            $this->log("Delete Entity: readDeleteResult finished - ". \App::$now->format('c'));
        } catch (\BO\Zmsclient\Exception $exception) {
            throw $exception;
        }
        return ($entity) ? true : false;
    }

    public function testEntity($entity)
    {
        if (!isset($entity['department'])) {
            throw new \Exception("Could not resolve department for message ".$entity['id']);
        }
        if (!isset($entity['department']['email'])) {
            throw new \Exception(
                "No mail address for department "
                .$entity['department']['name']
                ." (departmentID="
                .$entity['department']['id']
                ." Vorgang="
                .$entity['process']['id']
                .") "
                .$entity['id']
            );
        }
        if (! $entity->hasContent()) {
            throw new \BO\Zmsmessaging\Exception\MailWithoutContent();
        }

        if ($entity instanceof Mail) {
            $isMail = Validator::value($entity->getRecipient())->isMail()->getValue();
            if (!$isMail) {
                throw new \BO\Zmsmessaging\Exception\InvalidMailAddress();
            }
            if (\App::$verify_dns_enabled) {
                $hasDns = Validator::value($entity->getRecipient())->isMail()->hasDNS()->getValue();
                if (!$hasDns) {
                    throw new \BO\Zmsmessaging\Exception\InvalidMailAddress();
                }
            }
        }
    }

    protected function monitorProcesses($processHandles)
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
            usleep(50000000);
        }
        $this->log("All processes have finished");
        $this->logTotalExecutionTime();
    }

    protected function logResourceUsage()
    {
        $cpuUsage = $this->getCpuUsage();
        $cpuLimit = $this->getCpuLimit();
        $memoryUsage = $this->getMemoryUsage();
        $memoryLimit = $this->getMemoryLimit();
    
        if ($cpuLimit !== null) {
            $cpuLimitPercent = ($cpuUsage / $cpuLimit) * 100;
        } else {
            $cpuLimitPercent = 0;
        }
    
        if ($memoryLimit !== null) {
            $memoryLimitPercent = ($memoryUsage / $memoryLimit) * 100;
        } else {
            $memoryLimitPercent = 0;
        }
    
        //$this->log(sprintf("Current CPU usage: %07.2f%% of %.2f limit", $cpuLimitPercent, $cpuLimit ?? 0));
        $this->log(sprintf("Current Memory usage: %07.2f%% of %dMB limit", $memoryLimitPercent, $memoryLimit ?? 0));
    }

    protected function getCpuLimit()
    {
        $quotaFile = '/sys/fs/cgroup/cpu/cpu.cfs_quota_us';
        $periodFile = '/sys/fs/cgroup/cpu/cpu.cfs_period_us';

        if (file_exists($quotaFile) && file_exists($periodFile)) {
            $quota = intval(file_get_contents($quotaFile));
            $period = intval(file_get_contents($periodFile));

            if ($quota > 0 && $period > 0) {
                return $quota / 1000; // Convert microseconds to milliseconds
            }
        }

        return null;
    }

    protected function getCpuUsage()
    {
        $usageFile = '/sys/fs/cgroup/cpu/cpuacct.usage';
        if (file_exists($usageFile)) {
            $usage = intval(file_get_contents($usageFile));
            return $usage / 1e6; // Convert nanoseconds to milliseconds
        }
        return 0;
    }

    protected function getMemoryLimit()
    {
        $memLimitFile = '/sys/fs/cgroup/memory/memory.limit_in_bytes';
        if (file_exists($memLimitFile)) {
            $memLimit = intval(file_get_contents($memLimitFile)) / (1024 * 1024); // Convert to MB
            return $memLimit > 0 ? $memLimit : null;
        }
        return null;
    }

    protected function getMemoryUsage()
    {
        $memUsageFile = '/sys/fs/cgroup/memory/memory.usage_in_bytes';
        if (file_exists($memUsageFile)) {
            $memUsage = intval(file_get_contents($memUsageFile)) / (1024 * 1024); // Convert to MB
            return $memUsage;
        }
        return null;
    }

    protected function logTotalExecutionTime()
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

    protected function convertCollectionToArray($collection)
    {
        $this->log("Converting collection to array");
        $array = [];
        foreach ($collection as $item) {
            $array[] = $item;
        }
        $this->log("Conversion complete, array size: " . count($array));
        return $array;
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

    public function sendAndDeleteEmail($itemId)
    {
        $this->log("Fetching mail data for ID: $itemId");
        echo "\nFetching mail data for ID: $itemId\n";

        $mailData = $this->getMailById($itemId);

        if (empty($mailData)) {
            $this->log("No mail data for mail ID: $itemId\n\n");
            echo "No mail data for mail ID: $itemId\n\n";
            return;
        }

        //$this->log("Mail data: " . print_r($mailData, true));
        //echo "Mail data: " . print_r($mailData, true) . "\n\n";

        if ($mailData) {
            //$this->log("Mail data found for ID: $itemId\n\n");
            //echo "Mail data found for ID: $itemId\n\n";
            $entity = new \BO\Zmsentities\Mail($mailData);

            //$this->log("Build Mailer: testEntity() - " . \App::$now->format('c'));
            //echo "Build Mailer: testEntity() - " . \App::$now->format('c') . "\n\n";
            $this->testEntity($entity);
            $encoding = 'base64';

            $htmlPart = '';
            $textPart = '';
            /*foreach ($entity->multipart as $part) {
                if ($part['mime'] == 'text/html') {
                    $htmlPart = $part['content'];
                } elseif ($part['mime'] == 'text/plain') {
                    $textPart = $part['content'];
                }
            }*/

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

            //$this->log("Build Mailer: new PHPMailer() - " . \App::$now->format('c'));
            //echo "Build Mailer: new PHPMailer() - " . \App::$now->format('c') . "\n\n";

            try {
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
                //$this->log("Build Mailer: addAddress() - " . \App::$now->format('c'));
                //echo "Build Mailer: addAddress() - " . \App::$now->format('c') . "\n\n";
                $mailer->AddAddress($entity->getRecipient(), $entity->client['familyName']);

                if (null !== $entity->getIcsPart()) {
                    //$this->log("Build Mailer: AddStringAttachment() - " . \App::$now->format('c'));
                    //echo "Build Mailer: AddStringAttachment() - " . \App::$now->format('c') . "\n\n";
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

                $result = $this->sendMailer($entity, $mailer, true);

                if ($result instanceof PHPMailer) {
                    $result = array(
                        'id' => ($result->getLastMessageID()) ? $result->getLastMessageID() : $entity->id,
                        'recipients' => $result->getAllRecipientAddresses(),
                        'mime' => $result->getMailMIME(),
                        'attachments' => $result->getAttachments(),
                        'customHeaders' => $result->getCustomHeaders(),
                    );
                    $this->deleteEntityFromQueue($entity);
                    $this->log("Mail sent and deleted successfully for ID: $itemId" . "\n\n");
                    echo "Mail sent and deleted successfully for ID: $itemId\n\n";
                } else {
                    $result = array(
                        'errorInfo' => $result->ErrorInfo
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

        } else {
            $this->log("Mail data not found for ID: $itemId\n\n");
            echo "Mail data not found for ID: $itemId\n\n";
        }
    }
}
