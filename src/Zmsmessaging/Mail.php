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

class Mail extends BaseController
{
    protected $messagesQueue = null;

    public function __construct($maxRunTime = 50)
    {
        parent::__construct($maxRunTime);
        $queueList = \App::$http->readGetResult('/mails/')->getCollection();
        if (null !== $queueList) {
            $this->messagesQueue = $queueList->sortByCustomKey('createTimestamp');
        }
    }

    public function initQueueTransmission($action = false)
    {
        $resultList = [];
        if ($this->messagesQueue && count($this->messagesQueue)) {
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
        if ($result instanceof \PHPMailer) {
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
            $mailer->AddAddress($entity->getRecipient(), $entity->client['familyName']);
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
            \App::$http->readPostResult('/log/process/'. $entity->process['id'] .'/', $log, ['error' => 1]);
            return false;
        }

        // @codeCoverageIgnoreEnd
        return $mailer;
    }

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
        $mailer->SetLanguage("de");
        $mailer->Encoding = $encoding;
        $mailer->addCustomHeader('Content-Transfer-Encoding', $encoding);
        $mailer->IsHTML(true);
        $mailer->XMailer = \App::IDENTIFIER;
        $mailer->Subject = $entity['subject'];
        $mailer->AltBody = (isset($textPart)) ? $textPart : '';
        $mailer->Body = (isset($htmlPart)) ? $htmlPart : '';
        $mailer->SetFrom($entity['department']['email'], $entity['department']['name']);
        if (null !== $entity->getIcsPart()) {
            $mailer->AddStringAttachment(
                $icsPart,
                "Termin.ics",
                $encoding,
                "text/calendar; charset=utf-8; method=REQUEST"
            );
        }
        return $mailer;
    }
}
