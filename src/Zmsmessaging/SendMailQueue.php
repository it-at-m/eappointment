<?php
/**
 *
* @package Zmsmessaging
* @copyright BerlinOnline Stadtportal GmbH & Co. KG
*
*/
namespace BO\Zmsmessaging;

use \BO\Zmsentities\Mail as Entity;
use \BO\Zmsentities\Process;
use \PHPMailer as PHPMailer;
use \phpmailerException as phpmailerException;

class SendMailQueue extends BaseController
{
    protected $mailQueue = null;

    public function __construct()
    {
        $this->mailQueue = \App::$http->readGetResult('/mails/')->getData();
    }

    public function init()
    {
        foreach ($this->mailQueue as $item) {
            $mail = new Entity($item);
            $process = new Process($item->process);
            $client = $process->getFirstClient();
            $ics = $this->getIcs($mail['subject'], $process);

            $result = $this->send($client, $mail, $ics);
            if (true !== $result) {
                return false;
            }
        }
        return $result;
    }

    public function test()
    {
        foreach ($this->mailQueue as $item) {
            $mail = new Entity($item);
            $process = new Process($item->process);
            $client = $process->getFirstClient();
            $ics = $this->getIcs($mail['subject'], $process);
            echo "Empfaenger: ". $client['familyName'] ."\n";
            echo "E-Mail: ". $client['email'] ."\n";
            echo "Absender: ". $mail->department['email'] ."\n";
            echo "Betreff: $mail->subject\n";
            echo "Content HTML: ". $mail['multipart'][0]['content'] ."\n";
            echo "Content Text: ". $mail['multipart'][1]['content'] ."\n";
            echo "Ics: $ics\n\n";
        }
        return true;
    }

    private function getIcs($subject, $process)
    {
        if ('Terminbestaetigung' == $subject) {
            $ics = \App::$http->readGetResult('/process/'. $process->id .'/'. $process->authKey .'/ics/')->getEntity();
            return $ics->content;
        }
        return null;
    }

    private function send($client, $mail, $ics = null)
    {
        $encoding = ($mail['multipart'][0]['base64']) ? 'base64' : 'quoted-printable';
        $mailer = new PHPMailer(true);
        $mailer->IsHTML(true);
        $mailer->CharSet = 'UTF-8';
        $mailer->SetLanguage("de");
        $mailer->IsSMTP(); // telling the class to use SMTP
        $mailer->Host = "localhost"; // SMTP-Server
        $mailer->Port = 25; // set the SMTP port for the GMAIL server
        $mailer->SMTPAuth = false;
        $mailer->Encoding = $encoding;

        try {
            $mailer->AddAddress($client['email'], $client['email']);
            $mailer->SetFrom($mail['department']['email']);
            $mailer->FromName = $mail['department']['name'];
            $mailer->Subject = $mail['subject'];
            $mailer->AltBody = $mail['multipart'][1]['content'];
            $mailer->MsgHTML($mail['multipart'][0]['content']);
            if (null !== $ics) {
                $mailer->AddStringAttachment(
                    $ics,
                    "Termin.ics",
                    $encoding,
                    "text/calendar; charset=utf-8; method=REQUEST"
                );
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
            return true;
        }
    }
}
