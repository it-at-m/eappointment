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
use \PHPMailer as PHPMailer;
use \phpmailerException as phpmailerException;

class Notification extends BaseController
{
    protected $messagesQueue = null;

    public function __construct()
    {
        parent::__construct();
        $queueList = \App::$http->readGetResult('/notification/')->getCollection();
        if (null !== $queueList) {
            $this->messagesQueue = $queueList->sortByCustomKey('createTimestamp');
        }
    }

    public function initQueueTransmission($action = false)
    {
        $resultList = [];
        if (count($this->messagesQueue)) {
            foreach ($this->messagesQueue as $item) {
                $entity = new \BO\Zmsentities\Notification($item);
                $mailer = $this->getValidMailer($entity);
                if (! $mailer) {
                    continue;
                }
                $result = $this->sendMailer($entity, $mailer, $action);
                if ($result instanceof \PHPMailer) {
                    $resultList[] = array(
                        'id' => ($result->getLastMessageID()) ? $result->getLastMessageID() : $entity->id,
                        'recipients' => $result->getAllRecipientAddresses(),
                        'mime' => $result->getMailMIME(),
                        'customHeaders' => $result->getCustomHeaders(),
                    );
                    if ($action) {
                        $this->deleteEntityFromQueue($entity);
                    }
                } else {
                    // @codeCoverageIgnoreStart
                    $resultList[] = array(
                        'errorInfo' => $result->ErrorInfo
                    );
                    // @codeCoverageIgnoreEnd
                }
            }
        } else {
            $resultList[] = array(
                'errorInfo' => 'No notification entry found in Database...'
            );
        }
        return $resultList;
    }

    protected function getValidMailer(\BO\Zmsentities\Notification $entity)
    {
        $message = '';
        try {
            $mailer = $this->readMailer($entity);
            $mailer->AddAddress($entity->getRecipient());
        // @codeCoverageIgnoreStart
        } catch (phpmailerException $exception) {
            $message = 'Zmsmessaging PHPMailer Failure: '. $exception->getMessage();
            \App::$log->warning($message, [$exception]);
        } catch (\Exception $exception) {
            $message = 'Zmsmessaging Failure: '. $exception->getMessage();
            \App::$log->warning($message, [$exception]);
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
        $sender = $entity->getIdentification();
        $mailer = new PHPMailer(true);
        $mailer->Encoding = 'base64';
        $mailer->SetLanguage("de");
        // Without base64, encoding leads to additional spaces
        $mailer->Subject = "=?UTF-8?B?".$mailer->base64EncodeWrapMB(trim($entity->getMessage()))."?=";
        $mailer->Body = '';
        $mailer->AllowEmpty = true;
        $mailer->SetFrom($entity['department']['email']);
        $mailer->FromName = $sender;
        $mailer->CharSet = 'UTF-8';
        $mailer->SetLanguage("de");
        return $mailer;
    }
}
