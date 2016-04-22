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
            $ics = $this->getIcs($subject, $process);

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
            var_export($ics);
            echo "Empfaenger: ". $client['familyName'] ."\n";
            echo "E-Mail: ". $client['email'] ."\n";
            echo "Absender: ". $mail->department['email'] ."\n";
            echo "Betreff: $mail->subject\n";
            echo "Content HTML: ". $mail['multipart'][0]['content'] ."\n";
            echo "Content Text: ". $mail['multipart'][1]['content'] ."\n";
            echo "Ics: $ics\n\n";
        }
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
        $encoding = ($mail->multipart->base64) ? 'base64' : 'quoted-printable';
        $mail = new PHPMailer(true);
        $mail->IsHTML(true);
        $mail->CharSet = 'text/html; charset=UTF-8;';
        $mail->SetLanguage("de");
        $mail->IsSMTP(); // telling the class to use SMTP
        $mail->Host = "localhost"; // SMTP-Server
        $mail->Port = 25; // set the SMTP port for the GMAIL server
        $mail->SMTPAuth = false;
        $mail->Encoding = $encoding;

        try {
            $mail->AddAddress($client->familyName, $client->email);
            $mail->SetFrom($mail->department->email);
            $mail->FromName = $mail->department->name;
            $mail->Subject = $mail->subject;
            $mail->AltBody = \strip_tags($mail['multipart']['content']);
            $mail->MsgHTML($mail['multipart']['content']);
            if (null !== $ics) {
                $mail->AddStringAttachment(
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

        if (!$mail->Send()) {
            \App::$log->debug('Zmsmessaging Failed', [$mail->ErrorInfo]);
            return $message;
        } else {
            return true;
        }
    }
}
