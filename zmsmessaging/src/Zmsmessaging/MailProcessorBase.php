<?php
namespace BO\Zmsmessaging;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class MailProcessorBase extends BaseController
{
    public function __construct($verbose = false, $maxRunTime = 50)
    {
        parent::__construct($verbose, $maxRunTime);
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
            usleep(10000000);
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
    
        $this->log(sprintf("Current CPU usage: %07.2f%% of %.2f limit", $cpuLimitPercent, $cpuLimit ?? 0));
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

    protected function getMailById($itemId)
    {
        $endpoint = '/mails/' . $itemId . '/';
        try {
            $response = \App::$http->readGetResult($endpoint);
            return $response->getEntity();
        } catch (\Exception $e) {
            $this->log("Error fetching mail data: " . $e->getMessage() . "\n\n");
            return null;
        }
    }

    protected function sendAndDeleteEmail($itemId)
    {
        $this->log("Fetching mail data for ID: $itemId");
        $mailData = $this->getMailById($itemId);

        if (empty($mailData)) {
            $this->log("No mail data for mail ID: $itemId\n\n");
            return;
        }

        if ($mailData) {
            $entity = new \BO\Zmsentities\Mail($mailData);
            $this->testEntity($entity);
            $encoding = 'base64';

            $htmlPart = '';
            $textPart = '';
            foreach ($entity->multipart as $part) {
                if ($part['mime'] == 'text/html') {
                    $htmlPart = $part['content'];
                } elseif ($part['mime'] == 'text/plain') {
                    $textPart = $part['content'];
                }
            }

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
                } else {
                    $result = array(
                        'errorInfo' => $result->ErrorInfo
                    );
                    $this->log("Mail could not be sent. PHPMailer Error: {$result['errorInfo']}\n\n");
                }

            } catch (PHPMailerException $e) {
                $this->log("Mail could not be sent. PHPMailer Error: {$e->getMessage()}\n\n");
            } catch (Exception $e) {
                $this->log("Mail could not be sent. General Error: {$e->getMessage()}\n\n");
            }

        } else {
            $this->log("Mail data not found for ID: $itemId\n\n");
        }
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
}
