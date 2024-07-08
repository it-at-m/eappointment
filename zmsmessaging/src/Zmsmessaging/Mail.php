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

    public function __construct($verbose = false, $maxRunTime = 50)
    {
        parent::__construct($verbose, $maxRunTime);
        $this->log("Read Mail QueueList start with limit ". \App::$mails_per_minute ." - ". \App::$now->format('c'));
        $queueList = \App::$http->readGetResult('/mails/', [
            'resolveReferences' => 2,
            'limit' => \App::$mails_per_minute
        ])->getCollection();
        if (null !== $queueList) {
            $this->messagesQueue = $queueList->sortByCustomKey('createTimestamp');
            $this->log("QueueList sorted by createTimestamp - ". \App::$now->format('c'));
        }
    }

    public function initQueueTransmission($action = false)
    {
        $resultList = [];
        if ($this->messagesQueue && count($this->messagesQueue)) {
            $batchSize = 5;
            $batches = array_chunk($this->messagesQueue, $batchSize);
            $processHandles = [];

            foreach ($batches as $batch) {
                $mailIds = array_map(fn($item) => $item['id'], $batch);
                $encodedMailIds = implode(',', $mailIds);
                $processHandles[] = exec("php process_mail.php " . escapeshellarg($encodedMailIds) . " > /dev/null 2>&1 &");
            }

            $this->waitForAllProcesses($processHandles);
        } else {
            $resultList[] = array(
                'errorInfo' => 'No mail entry found in Database...'
            );
        }
        return $resultList;
    }

    private function waitForAllProcesses($processHandles)
    {
        foreach ($processHandles as $handle) {
            while (true) {
                $status = proc_get_status($handle);
                if (!$status['running']) {
                    proc_close($handle);
                    break;
                }
                sleep(1);
            }
        }
    }
}
