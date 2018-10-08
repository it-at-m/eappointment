<?php
/**
 *
* @package Zmsmessaging
* @copyright BerlinOnline Stadtportal GmbH & Co. KG
*
*/
namespace BO\Zmsmessaging;

use \BO\Zmsentities\Ics;
use \BO\Zmsentities\Mimepart;
use \PHPMailer as PHPMailer;
use \phpmailerException as phpmailerException;

class Notification extends BaseController
{
    protected $messagesQueue = null;

    public function __construct($maxRunTime = 50)
    {
        parent::__construct($maxRunTime);
        $queueList = \App::$http->readGetResult('/notification/')->getCollection();
        if (null !== $queueList) {
            $this->messagesQueue = $queueList->sortByCustomKey('createTimestamp');
        }
    }

    public function initQueueTransmission($action = false)
    {
        $resultList = [];
        if (count($this->messagesQueue)) {
            foreach ($this->messagesQueue as $item) {
                if ($this->maxRunTime < $this->getSpendTime()) {
                    break;
                }
                try {
                    $resultList[] = $this->sendQueueItem($action, $item);
                } catch (\Exception $exception) {
                    $log = new Mimepart(['mime' => 'text/plain']);
                    $log->content = $exception->getMessage();
                    if (isset($item['process']) && isset($item['process']['id'])) {
                        \App::$http->readPostResult('/log/process/'. $item['process']['id'] .'/', $log, ['error' => 1]);
                    }
                    \App::$log->error($log->content);
                }
            }
        } else {
            $resultList[] = array(
                'errorInfo' => 'No notification entry found in Database...'
            );
        }
        return $resultList;
    }

    public function sendQueueItem($action, $item)
    {
        $result = [];
        $entity = new \BO\Zmsentities\Notification($item);
        $mailer = $this->getValidMailer($entity);
        if (! $mailer) {
            throw new \Exception("No valid mailer");
        }
        $result = $this->sendMailer($entity, $mailer, $action);
        if ($result instanceof \PHPMailer) {
            $result = array(
                'id' => ($result->getLastMessageID()) ? $result->getLastMessageID() : $entity->id,
                'recipients' => $result->getAllRecipientAddresses(),
                'mime' => $result->getMailMIME(),
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

    protected function getValidMailer(\BO\Zmsentities\Notification $entity)
    {
        $message = '';
        $messageId = $entity['id'];
        try {
            $mailer = $this->readMailer($entity);
            $mailer->AddAddress($entity->getRecipient());
        // @codeCoverageIgnoreStart
        } catch (phpmailerException $exception) {
            $message = "Message #$messageId PHPMailer Failure: ". $exception->getMessage();
            \App::$log->warning($message, []);
        } catch (\Exception $exception) {
            $message = "Message #$messageId Failure: ". $exception->getMessage();
            \App::$log->warning($message, []);
        }
        if ($message) {
            $this->removeEntityOlderThanOneHour($entity);
            $log = new Mimepart(['mime' => 'text/plain']);
            $log->content = $message;
            \App::$http->readPostResult(
                '/log/process/'. $entity->process['id'] .'/',
                $log,
                ['error' => 1]
            );
            return false;
        }
        return $mailer;
    }

    protected function readMailer(\BO\Zmsentities\Notification $entity)
    {
        $this->testEntity($entity);
        $sender = $entity->getIdentification();
        $mailer = new PHPMailer(true);
        $mailer->Encoding = 'base64';
        $mailer->SetLanguage("de");
        // Without base64, encoding leads to additional spaces
        $mailer->Subject = "=?UTF-8?B?".$mailer->base64EncodeWrapMB(trim($entity->getMessage()))."?=";
        $mailer->Body = '';
        $mailer->AllowEmpty = true;
        $from = isset($entity['preferences']['notifications']['identification']) ?
            $entity['preferences']['notifications']['identification'] : null;
        $from = $from ? $from : $entity['department']['email'];
        $mailer->SetFrom($from);
        $mailer->FromName = $sender;
        $mailer->CharSet = 'UTF-8';
        $mailer->SetLanguage("de");
        return $mailer;
    }
}
