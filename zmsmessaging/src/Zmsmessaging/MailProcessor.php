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
    public function __construct($verbose = false, $maxRunTime = 100)
    {
        parent::__construct($verbose, $maxRunTime);
    }
}

if ($argc > 2) {
    $encodedIds = $argv[1];
    $action = $argv[2];
    $decodedAction = json_decode($action, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $action = $decodedAction;
    } else {
        $action = $action === 'false' ? false : ($action === 'true' ? true : $action);
    }
    $processor = new MailProcessor();
    $ids = json_decode(base64_decode($encodedIds), true);
    foreach ($ids as $id) {
        try {
            $processor->sendQueueItem($action, $id);
        } catch (\Exception $exception) {
            $log = new Mimepart(['mime' => 'text/plain']);
            $log->content = $exception->getMessage();
            if (isset($id['process']) && isset($id['process']['id'])) {
                $processor->log("Init Queue Exception message: ". $log->content .' - '. \App::$now->format('c'));
                $processor->log("Init Queue Exception log readPostResult start - ". \App::$now->format('c'));
                \App::$http->readPostResult('/log/process/'. $id['process']['id'] .'/', $log, ['error' => 1]);
                $processor->log("Init Queue Exception log readPostResult finished - ". \App::$now->format('c'));
            }
            //\App::$log->error($log->content);
        }
    }
}
