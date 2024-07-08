<?php

namespace BO\Zmsmessaging;

use \PHPMailer\PHPMailer\PHPMailer;

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
        curl_setopt($ch, CURLOPT_URL, $mailer->getSMTPHost());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $mailer->getMessageBody());
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
