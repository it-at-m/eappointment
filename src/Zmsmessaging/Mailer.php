<?php
/**
 *
* @package Zmsmessaging
*
*/
namespace BO\Zmsmessaging;

use \PHPMailer\PHPMailer\PHPMailer;

class Mailer extends PHPMailer
{
    const MAIL_MAX_LINE_LENGTH = 250;
}
