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
