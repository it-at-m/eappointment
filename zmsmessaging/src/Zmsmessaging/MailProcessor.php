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
    $mailIds = explode(',', $argv[1]);
    $action = filter_var($argv[2], FILTER_VALIDATE_BOOLEAN);
    $processor = new MailProcessor();
    foreach ($mailIds as $mailId) {
        $processor->sendAndDeleteEmail($mailId, $action);
    }
}
