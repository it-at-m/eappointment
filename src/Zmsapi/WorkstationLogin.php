<?php
/**
 * @package
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Workstation as Query;

/**
  * Handle requests concerning services
  */
class WorkstationLogin extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $query = new Query();
        $useraccount = Validator::input()->isJson()->assertValid()->getValue();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $loginName = $useraccount['id'];
        $logInHash = $query->readLoggedInHashByName($loginName);
        $workstation = $query
            ->writeEntityLoginByName($loginName, $useraccount['password'], \App::getNow(), $resolveReferences);
        $workstation->testValid();

        if ($logInHash) {
            \BO\Zmsdb\Connection\Select::writeCommit();
            $exception = new \BO\Zmsapi\Exception\Useraccount\UserAlreadyLoggedIn();
            $exception->data = $workstation;
            throw $exception;
        }

        $message = Response\Message::create(Render::$request);
        $message->data = $workstation;

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
