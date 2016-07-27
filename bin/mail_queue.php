<?php
// @codingStandardsIgnoreFile

// initialize the static \App singleton
include(realpath(__DIR__) .'/../bootstrap.php');

if (preg_grep('#--?v(erbose)?#', $argv)) {
    \App::$messaging = new \BO\Zmsmessaging\SendQueue();
    $resultList = \App::$messaging->startMailTransmission();
    foreach ($resultList as $mail) {
        if (isset($mail['errorInfo'])) {
            echo "ERROR OCCURED: ". $mail['errorInfo'] ."\n";
        } else {
            echo "Sent message successfully \n";
            echo "Details:\n";
            echo "ID: ". $mail['id'] ."\n";
            echo "MIME: ". trim($mail['mime']) ."\n";
            echo "RECIPIENTS: ". print_r($mail['recipients'],1) ."\n";
            echo "CUSTOM HEADERS: ". print_r($mail['customHeaders'],1) ."\n\n";
        }
    }
} else if (preg_grep('#--?s(end)?#', $argv)) {
    \App::$messaging = new \BO\Zmsmessaging\SendQueue();
    $resultList = \App::$messaging->startMailTransmission();
} else {
    echo "\nUsage:\n mail_queue.php [--send, --verbose] \n";
}