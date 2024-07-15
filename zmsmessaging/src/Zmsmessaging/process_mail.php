<?php

use BO\Zmsmessaging\MailProcessorBase;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../bootstrap.php';

class MailProcessor extends MailProcessorBase
{
    public function __construct($verbose = false, $maxRunTime = 50)
    {
        parent::__construct($verbose, $maxRunTime);
    }
}

if ($argc > 1) {
    $mailIds = explode(',', $argv[1]);
    $processor = new MailProcessor();
    foreach ($mailIds as $mailId) {
        //$processor->sendAndDeleteEmail($mailId);
    }
}
