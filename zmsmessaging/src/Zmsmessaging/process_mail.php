<?php

use BO\Zmsmessaging\BaseController;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../bootstrap.php';

class MailProcessor extends BaseController
{
    public function __construct($verbose = false, $maxRunTime = 50)
    {
        parent::__construct($verbose, $maxRunTime);
    }

    protected function getMailById($itemId)
    {
        $endpoint = '/mails/' . $itemId . '/';
        try {
            $response = \App::$http->readGetResult($endpoint);
            return $response->getEntity();
        } catch (\Exception $e) {
            $this->log("Error fetching mail data: " . $e->getMessage() . "\n\n");
            return null;
        }
    }

    protected function sendAndDeleteEmail($itemId)
    {
        $this->log("Fetching mail data for ID: $itemId\n\n");
        echo "Fetching mail data for ID: $itemId\n\n";


        $mailData = $this->getMailById($itemId);

        if (empty($mailData)) {
            $this->log("No mail data for mail ID: $itemId\n\n");
            echo "No mail data for mail ID: $itemId\n\n";
            return;
        }

        if ($mailData) {
            $entity = new \BO\Zmsentities\Mail($mailData);
            $this->testEntity($entity);
            $encoding = 'base64';

            $htmlPart = '';
            $textPart = '';
            foreach ($entity->multipart as $part) {
                if ($part['mime'] == 'text/html') {
                    $htmlPart = $part['content'];
                } elseif ($part['mime'] == 'text/plain') {
                    $textPart = $part['content'];
                }
            }

            try {
                $mailer = new PHPMailer(true);
                $mailer->CharSet = 'UTF-8';
                $mailer->SetLanguage("de");
                $mailer->Encoding = $encoding;
                $mailer->IsHTML(true);
                $mailer->XMailer = \App::IDENTIFIER;
                $mailer->Subject = $entity['subject'];
                $mailer->AltBody = (isset($textPart)) ? $textPart : '';
                $mailer->Body = (isset($htmlPart)) ? $htmlPart : '';
                $mailer->SetFrom($entity['department']['email'], $entity['department']['name']);
                $mailer->AddAddress($entity->getRecipient(), $entity->client['familyName']);

                if (null !== $entity->getIcsPart()) {
                    $mailer->AddStringAttachment(
                        $icsPart,
                        "Termin.ics",
                        $encoding,
                        "text/calendar; charset=utf-8; method=REQUEST"
                    );
                }

                if (\App::$smtp_enabled) {
                    $mailer->IsSMTP();
                    $mailer->SMTPAuth = \App::$smtp_auth_enabled;
                    $mailer->SMTPSecure = \App::$smtp_auth_method;
                    $mailer->Port = \App::$smtp_port;
                    $mailer->Host = \App::$smtp_host;
                    $mailer->Username = \App::$smtp_username;
                    $mailer->Password = \App::$smtp_password;
                    if (\App::$smtp_skip_tls_verify) {
                        $mailer->SMTPOptions['ssl'] = [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true,
                        ];
                    }
                }

                $result = $this->sendMailer($entity, $mailer, true);

                if ($result instanceof PHPMailer) {
                    $result = array(
                        'id' => ($result->getLastMessageID()) ? $result->getLastMessageID() : $entity->id,
                        'recipients' => $result->getAllRecipientAddresses(),
                        'mime' => $result->getMailMIME(),
                        'attachments' => $result->getAttachments(),
                        'customHeaders' => $result->getCustomHeaders(),
                    );
                    $this->deleteEntityFromQueue($entity);
                    $this->log("Mail sent and deleted successfully for ID: $itemId" . "\n\n");
                } else {
                    $result = array(
                        'errorInfo' => $result->ErrorInfo
                    );
                    $this->log("Mail could not be sent. PHPMailer Error: {$result['errorInfo']}\n\n");
                }

            } catch (PHPMailerException $e) {
                $this->log("Mail could not be sent. PHPMailer Error: {$e->getMessage()}\n\n");
            } catch (Exception $e) {
                $this->log("Mail could not be sent. General Error: {$e->getMessage()}\n\n");
            }

        } else {
            $this->log("Mail data not found for ID: $itemId\n\n");
        }
    }    
}

if ($argc > 1) {
    $mailIds = explode(',', $argv[1]);
    $processor = new MailProcessor();
    foreach ($mailIds as $mailId) {
        $processor->log("Processing mail ID: $mailId");
        echo "Processing mail ID: $mailId";
        $processor->sendAndDeleteEmail($mailId);
    }
} else {
    error_log("No mail IDs provided to process_mail.php");
    echo "No mail IDs provided to process_mail.php";
}
