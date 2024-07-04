// process_chunk.php
<?php

require 'vendor/autoload.php';

use BO\Zmsmessaging\Mail;

// Assuming you get the chunk of emails as a JSON string in the first argument
$chunk = json_decode($argv[1], true);

$mail = new Mail();

foreach ($chunk as $item) {
    try {
        $mail->sendQueueItem(false, $item);
    } catch (\Exception $exception) {
        $log = new \BO\Zmsentities\Mimepart(['mime' => 'text/plain']);
        $log->content = $exception->getMessage();
        if (isset($item['process']) && isset($item['process']['id'])) {
            \App::$http->readPostResult('/log/process/' . $item['process']['id'] . '/', $log, ['error' => 1]);
        }
    }
}
