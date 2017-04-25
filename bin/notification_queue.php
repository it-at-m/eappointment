<?php
// @codingStandardsIgnoreFile

// initialize the static \App singleton
include(realpath(__DIR__) .'/../bootstrap.php');

\App::$messaging = new \BO\Zmsmessaging\Notification();

$isValid = false;
if (preg_grep('#--?s(end)?#', $argv)) {
    $isValid = true;
}
$resultList = \App::$messaging->initQueueTransmission($isValid);
if (preg_grep('#--?v(erbose)?#', $argv)) {
    foreach ($resultList as $notification) {
        if (isset($notification['errorInfo'])) {
            echo "ERROR OCCURED: ". $notification['errorInfo'] ."\n";
        } elseif (!array_key_exists('viaGateway', $notification)) {
            echo "Sent message successfully \n";
            echo "Details:\n";
            echo "ID: ". $notification['id'] ."\n";
            echo "MIME: ". trim($notification['mime']) ."\n";
            echo "RECIPIENTS: ". print_r($notification['recipients'], 1) ."\n";
            echo "CUSTOM HEADERS: ". print_r($notification['customHeaders'], 1) ."\n\n";
        } else {
            $item = new \BO\Zmsentities\Notification($notification['item']);
            $preferences = (new \BO\Zmsentities\Config())->getNotificationPreferences();
            $url = $preferences['gatewayUrl'] .
                urlencode($item->getMessage()) .
                '&sender='. urlencode($item->getIdentification()) .
                '&recipient=' .
                urlencode($item->client['telephone'])
            ;
            echo "\033[01;32mSent message successfully via Gateway URL\033[0m:";
            echo $url ."\n\n";
        }
    }
}

if (!preg_grep('#--?v(erbose)?#', $argv) && !preg_grep('#--?s(end)?#', $argv)) {
    echo "\nUsage:\n notification_queue.php [--send, --verbose] \n";
}
