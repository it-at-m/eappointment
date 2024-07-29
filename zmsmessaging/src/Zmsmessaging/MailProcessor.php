<?php
/**
 *
* @package Zmsmessaging
*
*/
namespace BO\Zmsmessaging;

use BO\Zmsmessaging\BaseController;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../bootstrap.php';

class MailProcessor extends Mail
{
    public function __construct($verbose = false, $maxRunTime = 50)
    {
        parent::__construct($verbose, $maxRunTime);
    }
}

if ($argc > 2) {
    $encodedBatch = $argv[1];
    $action = $argv[2];
    $decodedAction = json_decode($action, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $action = $decodedAction;
    } else {
        $action = $action === 'false' ? false : ($action === 'true' ? true : $action);
    }
    $processor = new MailProcessor();
    $batch = json_decode(base64_decode($encodedBatch), true);
    foreach ($batch as $item) {
        try {
            $processor->sendQueueItem($action, $item);
        } catch (\Exception $exception) {
            $log = new Mimepart(['mime' => 'text/plain']);
            $log->content = $exception->getMessage();
            if (isset($item['process']) && isset($item['process']['id'])) {
                $processor->log("Init Queue Exception message: ". $log->content .' - '. \App::$now->format('c'));
                $processor->log("Init Queue Exception log readPostResult start - ". \App::$now->format('c'));
                \App::$http->readPostResult('/log/process/'. $item['process']['id'] .'/', $log, ['error' => 1]);
                $processor->log("Init Queue Exception log readPostResult finished - ". \App::$now->format('c'));
            }
            //\App::$log->error($log->content);
        }
    }
}

