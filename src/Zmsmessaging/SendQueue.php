<?php
/**
 *
* @package Zmsmessaging
* @copyright BerlinOnline Stadtportal GmbH & Co. KG
*
*/
namespace BO\Zmsmessaging;

use \BO\Zmsentities\Ics as Ics;
use \PHPMailer as PHPMailer;
use \phpmailerException as phpmailerException;

class SendQueue extends BaseController
{
    protected $messagesQueue = null;

    public function __construct($type = "mails")
    {
        $this->messagesQueue = \App::$http->readGetResult('/'. $type .'/')
            ->getCollection()
            ->sortByCustomKey('createTimestamp');
    }

    public function startMailTransmission()
    {
        foreach ($this->messagesQueue as $item) {
            $mail = new \BO\Zmsentities\Mail($item);
            $result = $this->startTransmission($mail);
            if (true !== $result) {
                return false;
            }
        }
        return $result;
    }

    public function startNotificationTransmission()
    {
        foreach ($this->messagesQueue as $item) {
            $notification = new \BO\Zmsentities\Notification($item);
            $result = $this->startTransmission($notification);
            if (true !== $result) {
                return false;
            }
        }
        return $result;
    }

    public function testMail()
    {
        foreach ($this->messagesQueue as $item) {
            $message = new \BO\Zmsentities\Mail($item);
            echo "Empfaenger: ". $item->client['familyName'] ."\n";
            echo "E-Mail: ". $item->client['email'] ."\n";
            echo "Absender: ". $message->department['email'] ."\n";
            echo "Betreff: $message->subject\n";
            echo "Content HTML: ". $message->getHtmlPart() ."\n";
            echo "Content Text: ". $message->getPlainPart() ."\n";
            echo "Ics: ". $message->getIcsPart() ."\n\n";
        }
        return true;
    }

    public function testNotification()
    {
        $preferences = (new \BO\Zmsentities\Config())->getNotificationPreferences();
        foreach ($this->messagesQueue as $item) {
            $message = new \BO\Zmsentities\Notification($item);
            $sender = $message->getIdentification();
            echo "Absender: ". $sender ."\n";
            echo "Empfaenger: SMS=". preg_replace(
                '/^0049/',
                '+49',
                $message->client['telephone']
            ) .'@example.com' ."\n"
            ;
            echo "Message: ". $message->getMessage() ."\n";

            $url = $preferences['gatewayUrl'] .
                urlencode($message->getMessage()) .
                '&sender='. urlencode($sender) .
                '&recipient=' .
                urlencode($message->client['telephone'])
            ;
            echo "URL Gateway: " . $url ."\n\n";
        }
        return true;
    }

    protected function startTransmission($message)
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

        if (!$mailer->Send()) {
            \App::$log->debug('Zmsmessaging Failed', [$mailer->ErrorInfo]);
            return $message;
        } else {
            $this->deleteFromQueue($message);
            return true;
        }
    }

    protected function createMailer($message)
    {
        $encoding = 'base64';
        $mailer = new PHPMailer(true);
        $mailer->IsHTML(true);
        $mailer->Subject = $message['subject'];
        $mailer->AltBody = $message->getPlainPart();
        $mailer->MsgHTML($message->getHtmlPart());
        $mailer->AddAddress($message->client['email'], $message->client['familyName']);
        $mailer->SetFrom($message['department']['email']);
        $mailer->FromName = $message['department']['name'];
        if (null !== $message->getIcsPart()) {
            //$mailer->Ical = $message->getIcsPart();
            $mailer->AddStringAttachment(
                $message->getIcsPart(),
                "Termin.ics",
                $encoding,
                "text/calendar; charset=utf-8; method=REQUEST"
            );
        }
        $mailer->CharSet = 'UTF-8';
        $mailer->SetLanguage("de");
        $mailer->Encoding = $encoding;
        return $mailer;
    }

    protected function createNotificationer($message)
    {
        $preferences = (new \BO\Zmsentities\Config())->getNotificationPreferences();
        $sender = $message->getIdentification();
        if ('mail' == $preferences['gateway']) {
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
        } else {
            $url = $preferences['gatewayUrl'] .
                urlencode($message->getMessage()) .
                '&sender='. urlencode($sender) .
                '&recipient=' .
                urlencode($message->client['telephone'])
            ;
            $request = fopen($url, 'r');
            fclose($request);
        }
        return $mailer;
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
