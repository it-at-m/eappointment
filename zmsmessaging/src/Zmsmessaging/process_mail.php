<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use BO\Zmsmessaging\BaseController;

// Ensure autoloader is included
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

        if (empty($mailData)) {
            $this->log("No mail data for mail ID: $itemId");
            echo "No mail data for mail ID: $itemId\n";
            //return;
        } else {
            $this->log("Mail data for mail ID: $mailData");
            echo "Mail data for mail ID: $mailData\n";
        }

        if ($mailData) {
            $this->log("Mail data found for ID: $itemId");
            $entity = new \BO\Zmsentities\Mail($mailData);
            $mailer = new PHPMailer(true);

            try {
                $this->log("Build Mailer: new PHPMailer() - ". \App::$now->format('c'));
                $mailer->CharSet = 'UTF-8';
                $mailer->SetLanguage("de");
                $mailer->Encoding = 'base64';
                $mailer->IsHTML(true);
                $mailer->XMailer = \App::IDENTIFIER;

                $mailer->Subject = $entity['subject'];
                $mailer->AltBody = (isset($entity->textPart)) ? $entity->textPart : '';
                $mailer->Body = (isset($entity->htmlPart)) ? $entity->htmlPart : '';

                if (empty($mailer->Body) && empty($mailer->AltBody)) {
                    $this->log("Both HTML and Text parts are missing for mail ID: $itemId");
                    echo "Both HTML and Text parts are missing for mail ID: $itemId\n";
                    //return;
                }

                $mailer->SetFrom($entity['department']['email'], $entity['department']['name']);
                $this->log("Build Mailer: addAddress() - ". \App::$now->format('c'));
                $mailer->AddAddress($entity->getRecipient(), $entity->client['familyName']);

                if (null !== $entity->getIcsPart()) {
                    $this->log("Build Mailer: AddStringAttachment() - ". \App::$now->format('c'));
                    $mailer->AddStringAttachment(
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

        try {
            $response = \App::$http->readGetResult('/mails/' . $itemId . '/');
            $this->log("API Response: " . print_r($response, true));
            return $response->getEntity();
        } catch (\Exception $e) {
            $this->log("Error fetching mail data: " . $e->getMessage());
            return null;
        }
    }
}

if ($argc > 1) {
    $mailIds = explode(',', $argv[1]);
    $processor = new MailProcessor();
    foreach ($mailIds as $mailId) {
        $processor->sendAndDeleteEmail($mailId);
    }
}
