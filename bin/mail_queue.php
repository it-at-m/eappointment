<?php
// @codingStandardsIgnoreFile

// initialize the static \App singleton
include(realpath(__DIR__) .'/../bootstrap.php');

\App::$messaging = new \BO\Zmsmessaging\Mail();

$isValid = false;
if (preg_grep('#--?s(end)?#', $argv)) {
    $isValid = true;
}
$resultList = \App::$messaging->initQueueTransmission($isValid);
if (preg_grep('#--?v(erbose)?#', $argv)) {
    foreach ($resultList as $mail) {
        if (isset($mail['errorInfo'])) {
            echo "\033[01;31mERROR OCCURED: ". $mail['errorInfo'] ."\033[0m \n";
        } else {
            print_r($mail->Body);
            echo "\033[01;32mTest mail with ID ". $mail['id'] ." successfully \033[0m \n";
            //echo "MIME: ". trim($mail['mime']) ."\n";
            echo "RECIPIENTS: ". json_encode($mail['recipients']) ."\n";
            echo "CUSTOM HEADERS: ". json_encode($mail['customHeaders']) ."\n";
            echo "\033[01;31mDELETE NOTICE: Items will not be deleted in verbose mode \033[0m \n\n";
        }
    }
}

if (!preg_grep('#--?v(erbose)?#', $argv) && !preg_grep('#--?s(end)?#', $argv)) {
    echo "\nUsage:\n mail_queue.php [--send, --verbose] \n";
}
