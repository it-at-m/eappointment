<?php

use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\Exception as PHPMailerException;

class MailTask extends Stackable
{
    private $item;
    private $action;
    private $mail;

    public function __construct($item, $action, $mail)
    {
        $this->item = $item;
        $this->action = $action;
        $this->mail = $mail;
    }

    public function run()
    {
        try {
            $result = $this->mail->sendQueueItem($this->action, $this->item);
            printf("Thread %lu sent mail: %s\n", $this->worker->getThreadId(), json_encode($result));
        } catch (\Exception $exception) {
            $log = new Mimepart(['mime' => 'text/plain']);
            $log->content = $exception->getMessage();
            if (isset($this->item['process']) && isset($this->item['process']['id'])) {
                $this->mail->log("Queue Exception message: " . $log->content);
                $this->mail->log("Queue Exception log readPostResult start");
                \App::$http->readPostResult('/log/process/' . $this->item['process']['id'] . '/', $log, ['error' => 1]);
                $this->mail->log("Queue Exception log readPostResult finished");
            }
        }
    }
}
