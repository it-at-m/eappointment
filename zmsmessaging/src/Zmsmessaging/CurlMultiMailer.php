<?php

namespace BO\Zmsmessaging;

use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\Exception as PHPMailerException;

class CurlMultiMailer
{
    private $handlers = [];
    private $multiHandle;

    public function __construct()
    {
        $this->multiHandle = curl_multi_init();
    }

    public function addEmail(PHPMailer $mailer)
    {
        $ch = curl_init();

        $toAddresses = $mailer->getToAddresses();
        $to = [];
        foreach ($toAddresses as $address) {
            $to[] = $address[0]; // Extracting the email address part
        }
        $to = implode(',', $to);

        // Set the cURL options for SMTP
        $smtpHost = $mailer->Host;
        $smtpPort = $mailer->Port;
        $smtpUsername = $mailer->Username;
        $smtpPassword = $mailer->Password;
        $from = $mailer->From;
        $subject = $mailer->Subject;
        $body = $mailer->Body;

        $smtpUrl = "smtp://$smtpHost:$smtpPort";
        
        curl_setopt($ch, CURLOPT_URL, $smtpUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'username' => $smtpUsername,
            'password' => $smtpPassword,
            'from' => $from,
            'to' => $to,
            'subject' => $subject,
            'body' => $body
        ]));

        $this->handlers[] = $ch;
        curl_multi_add_handle($this->multiHandle, $ch);
    }

    public function sendAll()
    {
        $active = null;
        do {
            $mrc = curl_multi_exec($this->multiHandle, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($this->multiHandle) != -1) {
                do {
                    $mrc = curl_multi_exec($this->multiHandle, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        foreach ($this->handlers as $ch) {
            curl_multi_remove_handle($this->multiHandle, $ch);
            curl_close($ch);
        }

        curl_multi_close($this->multiHandle);
    }
}
