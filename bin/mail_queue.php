<?php
// @codingStandardsIgnoreFile

// initialize the static \App singleton
include(realpath(__DIR__) .'/../bootstrap.php');

\App::$messaging = new \BO\Zmsmessaging\SendQueue();
$resultList = \App::$messaging->startMailTransmission(preg_grep('#--?s(end)?#', $argv));

if (preg_grep('#--?v(erbose)?#', $argv)) {
    foreach ($resultList as $mail) {
        if (isset($mail['errorInfo'])) {
            echo "ERROR OCCURED: ". $mail['errorInfo'] ."\n";
        } else {
            print_r($mail);
            echo "Sent message successfully \n";
            echo "Details:\n";
            echo "ID: ". $mail['id'] ."\n";
            echo "MIME: ". trim($mail['mime']) ."\n";
            echo "RECIPIENTS: ". print_r($mail['recipients'],1) ."\n";
            echo "CUSTOM HEADERS: ". print_r($mail['customHeaders'],1) ."\n\n";
        }
    }
}

if (!preg_grep('#--?v(erbose)?#', $argv) && !preg_grep('#--?s(end)?#', $argv)) {
    echo "\nUsage:\n mail_queue.php [--send, --verbose] \n";
}