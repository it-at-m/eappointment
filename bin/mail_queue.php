<?php
// @codingStandardsIgnoreFile

// initialize the static \App singleton
require(APP_PATH . '/bootstrap.php');
\App::$messaging = new \BO\Zmsmessaging\Mail();

if (is_numeric($argv[1])) {
   $queueId = $argv[1];
} else {
    error_log("No valid queueId, use: \n" . $argv[0] . " 12345 [--send,--verbose]");
    exit (1);
}

if ('verbose' == $argv[0])
{
    echo "Teste Message fuer queueId $queueId\n";
    if (true === \App::$messaging->sendTest($queueId)) {
        echo "Test Message erfolgreiche versandt.\n";
    } else {
        echo "Konnte Test Message nicht erfolgreich versenden.\n";
        exit(1);
    }
}
else
{
    echo "Verschicke Message fuer queueId $queueId\n";
    if (true === \App::$messaging->sendLive($queueId)) {
        echo "Message erfolgreiche versandt.\n";
    } else {
        echo "Konnte Message nicht erfolgreich versenden.\n";
        exit(1);
    }
}



