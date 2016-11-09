<?php
/**
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Mail as Query;
use \BO\Zmsdb\Config;
use \BO\Zmsdb\Process;

/**
  *
  * @SuppressWarnings(CouplingBetweenObjects)
  *
  * Handle requests concerning services
  * @SuppressWarnings(Coupling)
  */
class ProcessConfirmationMail extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $process = new \BO\Zmsentities\Process($input);
        $authCheck = (new Process())->readAuthKeyByProcessId($process->id);
        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authCheck['authKey'] != $process->authKey && $authCheck['authName'] != $process->authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        } elseif ($process->toProperty()->scope->preferences->client->emailRequired->get()
            && !$process->getFirstClient()->hasEmail()
        ) {
            throw new Exception\Process\EmailRequired();
        } elseif ($process->getFirstClient()->hasEmail()) {
            $config = (new Config())->readEntity();
            $mail = (new \BO\Zmsentities\Mail())->toResolvedEntity($process, $config);
            $mail = (new Query())->writeInQueue($mail);
            $message->data = $mail;
            $message->error = false;
            $message->message = '';
            \App::$log->info("Send mail", [$mail]);
        } else {
            // Create message for possible ICS attachment used in frontend
            $config = (new Config())->readEntity();
            $mail = (new \BO\Zmsentities\Mail())->toResolvedEntity($process, $config);
            $message->data = $mail;
            $message->error = false;
            $message->message = '';
        }

        Render::lastModified(time(), '0');
        // Always return a 200, even if no mail is send
        Render::json($message, 200);
    }
}
