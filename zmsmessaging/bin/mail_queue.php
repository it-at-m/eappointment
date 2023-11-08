<?php
// @codingStandardsIgnoreFile

// initialize the static \App singleton
include(realpath(__DIR__) .'/../bootstrap.php');

$usage = <<<EOS

Usage: {$argv[0]} [--verbose] --send
        ATTENTION! Sends emails from queue. USE WITH CAUTION!
        --send          no dry run, sends emails from queue
        --verbose       only shows what would be send

EOS;

$send = preg_grep('#--?s(end)?#', $argv);
$verbose = (preg_grep('#^--?v(erbose)?$#', $argv)) ? true : false;

\App::$messaging = new \BO\Zmsmessaging\Mail($verbose);

$now = new \DateTimeImmutable();
if (class_exists('\App') && isset(\App::$now)) {
    $now = \App::$now;
}
$resultList = \App::$messaging->initQueueTransmission($send);
if (! $send) {
    error_log("Use with --send to send emails.");
}

if ($verbose) {
    foreach ($resultList as $mail) {
        if (isset($mail['errorInfo'])) {
            echo "\033[01;31mERROR OCCURED: ". $mail['errorInfo'] ."\033[0m \n";
        } else {
            //print_r($mail->Body);
            echo "\033[01;32mTest mail with ID ". $mail['id'] ." successfully \033[0m \n";
            //echo "MIME: ". trim($mail['mime']) ."\n";
            echo "RECIPIENTS: ". json_encode($mail['recipients']) ."\n";
            echo "CUSTOM HEADERS: ". json_encode($mail['customHeaders']) ."\n";
            //echo "\033[01;31mDELETE NOTICE: Items will not be deleted in verbose mode \033[0m \n\n";
        }
    }
}
