<?php
/**
 *
 * @package Zmsmessaging
 *
 */
namespace BO\Zmsmessaging;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

require 'vendor/autoload.php';

function sendAndDeleteEmail($mailId)
{
    // Fetch the email data from the API based on the mail ID
    $mailData = getMailById($mailId); // Implement this function to fetch email data

    if ($mailData) {
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

            // Delete the email from the queue after sending
            deleteMailById($mailId); // Implement this function to delete email data

            echo "Mail sent and deleted successfully for ID: $mailId\n";
        } catch (PHPMailerException $e) {
            echo "Mail could not be sent. PHPMailer Error: {$mailer->ErrorInfo}\n";
        } catch (Exception $e) {
            echo "Mail could not be sent. General Error: {$e->getMessage()}\n";
        }
    } else {
        echo "Mail data not found for ID: $mailId\n";
    }
}

if ($argc > 1) {
    $mailIds = explode(',', $argv[1]);
    foreach ($mailIds as $mailId) {
        sendAndDeleteEmail($mailId);
    }
}
