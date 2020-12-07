<?php
/**
 *
* @package Zmsmessaging
* @copyright BerlinOnline Stadtportal GmbH & Co. KG
*
*/
namespace BO\Zmsmessaging;

use \BO\Zmsentities\Ics;
use \BO\Zmsentities\Mimepart;
use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\Exception as PHPMailerException;

class Notification extends BaseController
{
    protected $messagesQueue = null;

    public function __construct($maxRunTime = 50)
    {
        parent::__construct($maxRunTime);
        $queueList = \App::$http->readGetResult('/notification/')->getCollection();
        if (null !== $queueList) {
            $this->messagesQueue = $queueList->sortByCustomKey('createTimestamp');
        }
    }

    public function initQueueTransmission($action = false)
    {
        $resultList = [];
        if ($this->messagesQueue && count($this->messagesQueue)) {
            foreach ($this->messagesQueue as $item) {
                if ($this->maxRunTime < $this->getSpendTime()) {
                    break;
                }
                try {
                    $resultList[] = $this->sendQueueItem($action, $item);
                } catch (\Exception $exception) {
                    $log = new Mimepart(['mime' => 'text/plain']);
                    $log->content = $exception->getMessage();
                    if (isset($item['process']) && isset($item['process']['id'])) {
                        \App::$http->readPostResult('/log/process/'. $item['process']['id'] .'/', $log, ['error' => 1]);
                    }
                    \App::$log->error($log->content);
                }
            }
        } else {
            $resultList[] = array(
                'errorInfo' => 'No notification entry found in Database...'
            );
        }
        return $resultList;
    }

    public function sendQueueItem($action, $item)
    {
        $result = [];
        $entity = new \BO\Zmsentities\Notification($item);
        $mailer = $this->getValidMailer($entity);
        if (! $mailer) {
            throw new \Exception("No valid mailer");
        }
        $result = $this->sendMailer($entity, $mailer, $action);
        if ($result instanceof PHPMailer) {
            $result = array(
                'id' => ($result->getLastMessageID()) ? $result->getLastMessageID() : $entity->id,
                'recipients' => $result->getAllRecipientAddresses(),
                'mime' => $result->getMailMIME(),
                'customHeaders' => $result->getCustomHeaders(),
                'subject' => mb_decode_mimeheader($result->Subject)
            );
            if ($action) {
                $this->deleteEntityFromQueue($entity);
            }
        } else {
            // @codeCoverageIgnoreStart
            $result = array(
                'errorInfo' => $result->ErrorInfo
            );
            // @codeCoverageIgnoreEnd
        }
        return $result;
    }

    protected function getValidMailer(\BO\Zmsentities\Notification $entity)
    {
        $message = '';
        $messageId = $entity['id'];
        try {
            $mailer = $this->readMailer($entity);
        // @codeCoverageIgnoreStart
        } catch (PHPMailerException $exception) {
            $message = "Message #$messageId PHPMailer Failure: ". $exception->getMessage();
            \App::$log->warning($message, []);
        } catch (\Exception $exception) {
            $message = "Message #$messageId Failure: ". $exception->getMessage();
            \App::$log->warning($message, []);
        }
        if ($message) {
            $this->removeEntityOlderThanOneHour($entity);
            $log = new Mimepart(['mime' => 'text/plain']);
            $log->content = $message;
            \App::$http->readPostResult(
                '/log/process/'. $entity->process['id'] .'/',
                $log,
                ['error' => 1]
            );
            return false;
        }
        return $mailer;
    }

    protected function readMailer(\BO\Zmsentities\Notification $entity)
    {
        $this->testEntity($entity);
        $message = $entity->getMessage();
        $sender = $entity->getIdentification();
        $from = $sender ? $sender : $entity['department']['email'];
        
        $mailer = new PHPMailer();
        $mailer->CharSet = 'UTF-8';
        $mailer->Encoding = 'base64';
        $mailer->SetLanguage('de');
        $mailer->SetFrom($from);
        $mailer->AddAddress($entity->getRecipient());
        
        $mailer->Subject = $message;
        $mailer->Body = '';
        $mailer->AllowEmpty = true;
        
        $mailer->FromName = $sender;
        $mailer->XMailer = \App::IDENTIFIER;

        #to test via gmail
        /*
        $mailer->IsSMTP();
        $mailer->SMTPDebug  = 1;
        $mailer->SMTPAuth   = true;
        $mailer->SMTPSecure = "tls";
        $mailer->Port       = 587;
        $mailer->Host       = "smtp.gmail.com";
        $mailer->Username   = "";
        $mailer->Password   = "";
        $mailer->AddAddress("", "");
        $mailer->FromName   = "";
        */
        /*
        #to test via kasserver
        $mailer->IsSMTP();
        $mailer->SMTPDebug  = 1;
        $mailer->SMTPAuth   = true;
        $mailer->SMTPSecure = "tls";
        $mailer->Port       = 587;
        $mailer->Host       = "w00b3688.kasserver.com";
        $mailer->Username   = "";
        $mailer->Password   = "";
        $mailer->AddAddress("", "");
        $mailer->FromName   = "";
        */
        return $mailer;
    }
}
