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
        ob_implicit_flush(true); // Enable implicit flushing
        ob_end_flush();          // Flush output buffer
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
    try {
        $results = $processor->sendQueueItems($action, $ids);
        foreach ($results as $result) {
            if (isset($result['errorInfo'])) {
                $processor->log("Error processing mail item: " . $result['errorInfo']);
            }
        }
    } catch (\Exception $exception) {
        $processor->log("Error processing batch: " . $exception->getMessage());
    }
}
