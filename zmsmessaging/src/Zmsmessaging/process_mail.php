<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use BO\Zmsmessaging\BaseController;

// Ensure autoloader is included
// Ensure autoloader and App initialization is included
require __DIR__ . '/../../vendor/autoload.php'; // Adjust path as necessary
require __DIR__ . '/../../bootstrap.php'; // Adjust path as necessary

class MailProcessor extends BaseController
{
    public function __construct($verbose = false, $maxRunTime = 50)
    {
        parent::__construct($verbose, $maxRunTime);
    }

    public function sendAndDeleteEmail($itemId)
    {
        $this->log("Fetching mail data for ID: $itemId");

        // Fetch the email data from the API based on the mail ID
        $mailData = $this->getMailById($itemId);

        if ($mailData) {
            $this->log("Mail data found for ID: $itemId");
            $entity = new \BO\Zmsentities\Mail($mailData);
            $mailer = new PHPMailer(true);

            try {
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

                $mailer->send();
                $this->deleteEntityFromQueue($entity);
                $this->log("Mail sent and deleted successfully for ID: $itemId");

                echo "Mail sent and deleted successfully for ID: $itemId\n";
            } catch (PHPMailerException $e) {
                $this->log("Mail could not be sent. PHPMailer Error: {$mailer->ErrorInfo}");
                echo "Mail could not be sent. PHPMailer Error: {$mailer->ErrorInfo}\n";
            } catch (Exception $e) {
                $this->log("Mail could not be sent. General Error: {$e->getMessage()}");
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

        // Implement the function to fetch email data by ID
        $response = \App::$http->readGetResult('/mails/'.$itemId.'/')->getEntity();
        return $response;
    }
}

if ($argc > 1) {
    $mailIds = explode(',', $argv[1]);
    $processor = new MailProcessor();
    foreach ($mailIds as $mailId) {
        $processor->sendAndDeleteEmail($mailId);
    }
}
