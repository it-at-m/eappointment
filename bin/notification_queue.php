<?php
// @codingStandardsIgnoreFile

// initialize the static \App singleton
include(realpath(__DIR__) .'/../bootstrap.php');

if (preg_grep('#--?v(erbose)?#', $argv)) {
    \App::$messaging = new \BO\Zmsmessaging\SendQueue('notification');
    $resultList = \App::$messaging->startNotificationTransmission();
    foreach ($resultList as $notification) {
        if (isset($notification['errorInfo'])) {
            echo "ERROR OCCURED: ". $notification['errorInfo'] ."\n";
        } else if (!array_key_exists('viaGateway', $notification)) {
            echo "Sent message successfully \n";
            echo "Details:\n";
            echo "ID: ". $notification['id'] ."\n";
            echo "MIME: ". trim($notification['mime']) ."\n";
            echo "RECIPIENTS: ". print_r($notification['recipients'],1) ."\n";
            echo "CUSTOM HEADERS: ". print_r($notification['customHeaders'],1) ."\n\n";
        } else {
            $item = new \BO\Zmsentities\Notification($notification['item']);
            $preferences = (new \BO\Zmsentities\Config())->getNotificationPreferences();
            $url = $preferences['gatewayUrl'] .
                urlencode($item->getMessage()) .
                '&sender='. urlencode($item->getIdentification()) .
                '&recipient=' .
                urlencode($item->client['telephone'])
            ;
            echo "Sent message successfully via Gateway URL:";
            echo $url ."\n\n";
        }
    }
} else if (preg_grep('#--?s(end)?#', $argv)) {
    \App::$messaging = new \BO\Zmsmessaging\SendQueue('notification');
    $resultList = \App::$messaging->startNotificationTransmission();
} else {
    echo "\nUsage:\n notification_queue.php [--send, --verbose] \n";
}
