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

class MailProcessor extends BaseController
{
    public function __construct($verbose = false, $maxRunTime = 50)
    {
        parent::__construct($verbose, $maxRunTime);
    }
}

if ($argc > 1) {
    $mailDataJson = $argv[1];
    $mailDataArray = json_decode($mailDataJson, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error decoding JSON: " . json_last_error_msg());
        exit(1);
    }

    $processor = new MailProcessor();
    foreach ($mailDataArray as $mailData) {
        error_log($mailData);
        $processor->sendAndDeleteEmail($mailData['id'], $mailData);
    }
}
