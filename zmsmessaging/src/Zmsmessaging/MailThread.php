<?php

namespace BO\Zmsmessaging;

class MailThread extends \Thread
{
    private $mail;
    private $action;
    private $entity;

    public function __construct($mail, $action, $entity)
    {
        $this->mail = $mail;
        $this->action = $action;
        $this->entity = $entity;
    }

    public function run()
    {
        $this->mail->processQueueItem($this->action, $this->entity);
    }
}
