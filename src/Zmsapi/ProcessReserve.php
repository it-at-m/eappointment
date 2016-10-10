<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process as Query;

/**
  * Handle requests concerning services
  */
class ProcessReserve extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();
        $process = new \BO\Zmsentities\Process($input);

        if ($process->hasId()) {
            throw new Exception\Process\ProcessFailedReservation();
        } else {
            $process->status = 'reserved';
            $message->data = (new Query())->updateEntity($process);
        }
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
