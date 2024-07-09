<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use BO\Zmsmessaging\BaseController;

require 'vendor/autoload.php';

class MailProcessor extends BaseController
{
    public function __construct($verbose = false, $maxRunTime = 50)
    {
        parent::__construct($verbose, $maxRunTime);
    }

    public function sendAndDeleteEmail($itemId)
    {
        $this->log("Fetching mail data for ID: $itemId");
        $mailData = $this->getMailById($itemId);

        if ($mailData) {
            $entity = new \BO\Zmsentities\Mail($mailData);
            $mailer = new PHPMailer(true);

            try {
                $this->log("Setting up PHPMailer for ID: $itemId");
                $mailer->isSMTP();
                $mailer->Host = \App::$smtp_host;
                $mailer->SMTPAuth = \App::$smtp_auth_enabled;
                $mailer->Username = \App::$smtp_username;
                $mailer->Password = \App::$smtp_password;
                $mailer->SMTPSecure = \App::$smtp_auth_method;
                $mailer->Port = \App::$smtp_port;

                $mailer->setFrom($entity['department']['email'], $entity['department']['name']);
                $mailer->addAddress($entity->getRecipient(), $entity->client['familyName']);
                $mailer->isHTML(true);
                $mailer->Subject = $entity['subject'];
                $mailer->Body = $entity->htmlPart;
                $mailer->AltBody = $entity->textPart;

                if (null !== $entity->getIcsPart()) {
                    $mailer->addStringAttachment(
                        $entity->getIcsPart(),
                        "Termin.ics",
                        'base64',
                        "text/calendar; charset=utf-8; method=REQUEST"
                    );
                }

                $this->log("Sending email for ID: $itemId");
                $mailer->send();
                $this->log("Email sent successfully for ID: $itemId");
                $this->deleteEntityFromQueue($entity);
                $this->log("Email deleted from queue for ID: $itemId");

                echo "Mail sent and deleted successfully for ID: $itemId\n";
            } catch (PHPMailerException $e) {
                $this->log("PHPMailer Error for ID $itemId: {$mailer->ErrorInfo}");
                echo "Mail could not be sent. PHPMailer Error: {$mailer->ErrorInfo}\n";
            } catch (Exception $e) {
                $this->log("General Error for ID $itemId: {$e->getMessage()}");
                echo "Mail could not be sent. General Error: {$e->getMessage()}\n";
            }
        } else {
            $this->log("Mail data not found for ID: $itemId");
            echo "Mail data not found for ID: $itemId\n";
        }
    }

    private function getMailById($itemId)
    {
        $this->log("Fetching mail data from API for ID: $itemId");
        $response = \App::$http->readGetResult('/mails/'.$itemId.'/')->getEntity();
        $this->log("Fetched mail data: " . print_r($response, true));
        return $response;
    }

    public function log($message)
    {
        if (is_array($message)) {
            $message = print_r($message, true);
        }

        $time = $this->getSpendTime();
        $memory = memory_get_usage()/(1024*1024);
        $text = sprintf("[Process Mail log %07.3fs %07.1fmb] %s", $time, $memory, $message);
        error_log($text);
        return $this;
    }
}

if ($argc > 1) {
    $mailIds = explode(',', $argv[1]);
    $processor = new MailProcessor(true);
    foreach ($mailIds as $mailId) {
        $processor->sendAndDeleteEmail($mailId);
    }
}
