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

class SendQueue
{
    protected $messagesQueue = null;

    public function __construct($type = "mails")
    {
        $queueList = \App::$http->readGetResult('/'. $type .'/')->getCollection();
        if (null !== $queueList) {
            $this->messagesQueue = $queueList->sortByCustomKey('createTimestamp');
        }
    }

    public function startMailTransmission($action = false)
    {
        $resultList = array();
        if (count($this->messagesQueue)) {
            foreach ($this->messagesQueue as $item) {
                $mail = new \BO\Zmsentities\Mail($item);
                $result = $this->startTransmission($mail, $action);
                if ($result instanceof \PHPMailer) {
                    $resultList[] = array(
                        'id' => ($result->getLastMessageID()) ? $result->getLastMessageID() : $mail->id,
                        'recipients' => $result->getAllRecipientAddresses(),
                        'mime' => $result->getMailMIME(),
                        'attachments' => $result->getAttachments(),
                        'customHeaders' => $result->getCustomHeaders(),
                    );
                } else {
                    // @codeCoverageIgnoreStart
                    $resultList[] = array(
                        'errorInfo' => $result->ErrorInfo
                    );
                    // @codeCoverageIgnoreEnd
                }
            }
        } else {
            $resultList[] = array(
                'errorInfo' => 'No mail entry found in Database...'
            );
        }
        return $resultList;
    }

    public function startNotificationTransmission($action = false)
    {
        $resultList = array();
        if (count($this->messagesQueue)) {
            foreach ($this->messagesQueue as $item) {
                $notification = new \BO\Zmsentities\Notification($item);
                $result = $this->startTransmission($notification, $action);
                if ($result instanceof \PHPMailer) {
                    $resultList[] = array(
                        'id' => ($result->getLastMessageID()) ? $result->getLastMessageID() : $notification->id,
                        'recipients' => $result->getAllRecipientAddresses(),
                        'mime' => $result->getMailMIME(),
                        'customHeaders' => $result->getCustomHeaders(),
                    );
                } else {
                    // @codeCoverageIgnoreStart
                    $resultList[] = array(
                        'errorInfo' => $result->ErrorInfo
                    );
                    // @codeCoverageIgnoreEnd
                }
            }
        } else {
            $resultList[] = array(
                'errorInfo' => 'No notification entry found in Database...'
            );
        }
        return $resultList;
    }

    protected function startTransmission($message, $action)
    {
        try {
            if ($message instanceof \BO\Zmsentities\Mail) {
                $mailer = $this->createMailer($message);
            } else {
                $mailer = $this->createNotificationer($message);
            }
        } catch (phpmailerException $exception) {
            // @codeCoverageIgnoreStart
            \App::$log->debug('Zmsmessaging PHPMailer', [$exception]);
            return $exception->getMessage();
        } catch (Exception $exception) {
            \App::$log->debug('Zmsmessaging', [$exception]);
            return $exception->getMessage();
        }
        if (false !== $action) {
            if (null !== $mailer) {
                if (!$mailer->Send()) {
                    \App::$log->debug('Zmsmessaging Failed', [$mailer->ErrorInfo]);
                }
            }
            $this->deleteFromQueue($message);
        }
        // @codeCoverageIgnoreEnd
        return $mailer;
    }

    protected function createMailer($message)
    {
        $encoding = 'base64';
        foreach ($message->multipart as $part) {
            $entity = new Mimepart($part);
            if ($entity->isText()) {
                $textPart = $entity->getContent();
            }
            if ($entity->isHtml()) {
                $htmlPart = $entity->getContent();
            }
            if ($entity->isIcs()) {
                $icsPart = $entity->getContent();
            }
        }

        $mailer = new PHPMailer(true);

        $mailer->CharSet = 'UTF-8';
        $mailer->SetLanguage("de");
        $mailer->Encoding = $encoding;
        $mailer->addCustomHeader('Content-Transfer-Encoding', $encoding);
        $mailer->IsHTML(true);
        $mailer->Subject = $message['subject'];
        $mailer->AltBody = (isset($textPart)) ? $textPart : '';
        $mailer->Body = (isset($htmlPart)) ? $htmlPart : '';
        $mailer->AddAddress($message->client['email'], $message->client['familyName']);
        $mailer->SetFrom($message['department']['email']);
        $mailer->FromName = $message['department']['name'];
        if (null !== $message->getIcsPart()) {
            $mailer->AddStringAttachment(
                $icsPart,
                "Termin.ics",
                $encoding,
                "text/calendar; charset=utf-8; method=REQUEST"
            );
        }
        return $mailer;
    }

    protected function createNotificationer($message)
    {
        $sender = $message->getIdentification();
        $mailer = new PHPMailer(true);
        $mailer->Encoding = 'base64';
        // Without base64, encoding leads to additional spaces
        $mailer->Subject = "=?UTF-8?B?".$mailer->base64EncodeWrapMB(trim($message->getMessage()))."?=";
        $mailer->Body = '';
        $mailer->AllowEmpty = true;
        $telephone = preg_replace('[^0-9]', '', $message->client['telephone']);
        $telephone = preg_replace('/\s+/', '', $telephone);
        $recipient = 'SMS='.preg_replace('/^0049/', '+49', $telephone).'@example.com';
        $mailer->AddAddress($recipient);
        $mailer->SetFrom($message['department']['email']);
        $mailer->FromName = $sender;
        $mailer->CharSet = 'UTF-8';
        $mailer->SetLanguage("de");
        return $mailer;
    }


    public function deleteFromQueue($message)
    {
        $type = ($message instanceof \BO\Zmsentities\Mail) ? 'mails' : 'notification';
        try {
            $mail = \App::$http->readDeleteResult('/'. $type .'/'. $message->id .'/')->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            throw $exception;
        }
        return ($mail) ? true : false;
    }
}
