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
        $log = new Mimepart(['mime' => 'text/plain']);
        $log->content = ($entity instanceof Mail) ? $entity->subject : $entity->message;
        \App::$http->readPostResult('/log/process/'. $entity->process['id'] .'/', $log);
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
        $running = true; 
        while ($running) {
            $running = false;
            foreach ($processHandles as &$handle) {
                if (is_resource($handle['process'])) {
                    $status = proc_get_status($handle['process']);
                    if ($status['running']) {
                        $running = true;
                    } else {
                        // Directly log the combined item IDs and process IDs
                        $this->log("Processing finished for IDs: " . $handle['ids']);
                        proc_close($handle['process']);
                        $handle['process'] = null;
                    }
                }
            }
            usleep(500000); // Sleep for half a second
        }
        $this->log("All processes have finished");
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

}
