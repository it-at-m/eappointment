<?php
// @codingStandardsIgnoreFile

// initialize the static \App singleton
include(realpath(__DIR__) .'/../bootstrap.php');

$usage = <<<EOS

Usage: {$argv[0]} [--verbose] --send
        ATTENTION! Sends notifications from queue. USE WITH CAUTION!
        --send          no dry run, send notifications from queue
        --verbose       only shows what would be send

EOS;

$send = preg_grep('#--?s(end)?#', $argv);
$verbose = preg_grep('#^--?v(erbose)?$#', $argv);

\App::$messaging = new \BO\Zmsmessaging\Notification($verbose);

$now = new \DateTimeImmutable();
if (class_exists('\App') && isset(\App::$now)) {
    $now = \App::$now;
}
$resultList = \App::$messaging->initQueueTransmission($send);
if (! $send) {
    error_log("Use with --send to send notifications.");
}

if ($verbose) {
    foreach ($resultList as $notification) {
        if (isset($notification['errorInfo'])) {
            echo "\033[01;31mERROR OCCURED: ". $notification['errorInfo'] . "\033[0m \n";
        } elseif (!array_key_exists('viaGateway', $notification)) {
            $url = $preferences['gatewayUrl'] . $notification['subject'] .
                '&sender='. urlencode($notification['identification']) .
                '&recipient='. urlencode(array_keys($notification['recipients'])[0])
            ;
            echo "\033[01;32mTest notification with ID ". $notification['id'] ." successfully \033[0m \n";
            echo "RECIPIENTS: ". json_encode($notification['recipients']) ."\n";
            echo "MIME: ". trim($notification['mime']) ."\n";
            echo "Subject: ". $notification['subject'] ."\n\n";
            //echo "Gateway-URL: ". $url;
            //echo "\033[01;31mDELETE NOTICE: Items will not be deleted in verbose mode \033[0m \n\n";
        } else {
            $preferences = (new \BO\Zmsentities\Config())->getNotificationPreferences();
            $url = $preferences['gatewayUrl'] . $notification['subject'] .
                '&sender='. urlencode($notification['identification']) .
                '&recipient='. urlencode(array_keys($notification['recipients'])[0])
            ;
            echo "\033[01;32mSent message successfully via Gateway URL\033[0m:";
            echo "Subject: ". $notification['subject'] ."\n\n";
            //echo $url ."\n\n";
        }
    }
}
