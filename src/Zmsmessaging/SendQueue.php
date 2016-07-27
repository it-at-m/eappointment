<?php
/**
 *
* @package Zmsmessaging
* @copyright BerlinOnline Stadtportal GmbH & Co. KG
*
*/
namespace BO\Zmsmessaging;

use \BO\Zmsentities\Ics;
use \BO\Zmsentities\MailPart;
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
                        'id' => $result->getLastMessageID(),
                        'recipients' => $result->getAllRecipientAddresses(),
                        'mime' => $result->getMailMIME(),
                        'customHeaders' => $result->getCustomHeaders(),
                    );
                } else {
                    $resultList[] = array(
                        'errorInfo' => $result->ErrorInfo
                    );
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
                        'id' => $result->getLastMessageID(),
                        'recipients' => $result->getAllRecipientAddresses(),
                        'mime' => $result->getMailMIME(),
                        'customHeaders' => $result->getCustomHeaders(),
                    );
                } elseif ('viaGateway' == $result) {
                    $resultList[] = array('viaGateway' => true, 'item' => $item);
                } else {
                    $resultList[] = array(
                        'errorInfo' => $result->ErrorInfo
                    );
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
            \App::$log->debug('Zmsmessaging PHPMailer', [$exception]);
            return $exception->getMessage();
        } catch (Exception $exception) {
            \App::$log->debug('Zmsmessaging', [$exception]);
            return $exception->getMessage();
        }
        if (in_array('--send', $action)) {
            if (null !== $mailer && 'viaGateway' != $mailer) {
                if (!$mailer->Send()) {
                    \App::$log->debug('Zmsmessaging Failed', [$mailer->ErrorInfo]);
                }
            }
            $this->deleteFromQueue($message);
        }
        return $mailer;
    }

    protected function createMailer($message)
    {
        $encoding = 'base64';
        foreach ($message->multipart as $part) {
            $entity = new MailPart($part);
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
        $mailer->AltBody = $textPart;
        $mailer->Body = $htmlPart;
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
        $preferences = (new \BO\Zmsentities\Config())->getNotificationPreferences();
        $sender = $message->getIdentification();
        if ('mail' == $preferences['gateway']) {
            $url = $preferences['gatewayUrl'] .
            urlencode($message->getMessage()) .
            '&sender='. urlencode($sender) .
            '&recipient=' .
            urlencode($message->client['telephone'])
            ;
            $request = fopen($url, 'r');
            fclose($request);
            return 'viaGateway';
        } else {
            $mailer = new PHPMailer(true);
            $mailer->Subject = $message->getMessage();
            $mailer->Body = '&nbsp;';
            $telephone = preg_replace('[^0-9]', '', $message->client['telephone']);
            $recipient = 'SMS='.preg_replace('/^0049/', '+49', $telephone).'@example.com';
            $mailer->AddAddress($recipient);
            $mailer->SetFrom($message['department']['email']);
            $mailer->FromName = $sender;
            $mailer->CharSet = 'UTF-8';
            $mailer->SetLanguage("de");
            return $mailer;
        }
    }


    protected function deleteFromQueue($message)
    {
        $type = ($message instanceof \BO\Zmsentities\Mail) ? 'mails' : 'notification';
        try {
            return \App::$http->readDeleteResult('/'. $type .'/'. $message->id .'/');
        } catch (Exception $exception) {
            \App::$log->debug('Zmsmessaging Delete From Queue', [$exception]);
            return $exception->getMessage();
        }
    }
}
