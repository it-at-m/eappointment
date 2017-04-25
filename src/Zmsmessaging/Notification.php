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

class Notification
{
    protected $messagesQueue = null;

    public function __construct()
    {
        $queueList = \App::$http->readGetResult('/notification/')->getCollection();
        if (null !== $queueList) {
            $this->messagesQueue = $queueList->sortByCustomKey('createTimestamp');
        }
    }

    public function initQueueTransmission($action = false)
    {
        if (count($this->messagesQueue)) {
            foreach ($this->messagesQueue as $item) {
                $entity = new \BO\Zmsentities\Notification($item);
                $mailer = $this->getValidMailer($entity);
                $mailer->AddAddress($this->getRecipientFromEntity($entity));
                $result = Transmission::sendMailer($mailer, $action);
                if ($result instanceof \PHPMailer) {
                    $resultList[] = array(
                        'id' => ($result->getLastMessageID()) ? $result->getLastMessageID() : $entity->id,
                        'recipients' => $result->getAllRecipientAddresses(),
                        'mime' => $result->getMailMIME(),
                        'customHeaders' => $result->getCustomHeaders(),
                    );
                    Transmission::deleteEntityFromQueue($entity);
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
        try {
            $mailer = $this->readMailer($entity);
        } catch (phpmailerException $exception) {
            // @codeCoverageIgnoreStart
            \App::$log->debug('Zmsmessaging PHPMailer', [$exception]);
            return $exception->getMessage();
        } catch (Exception $exception) {
            \App::$log->debug('Zmsmessaging', [$exception]);
            return $exception->getMessage();
        }
        return $mailer;
    }

    protected function getRecipientFromEntity(\BO\Zmsentities\Notification $entity)
    {
        $telephone = preg_replace('[^0-9]', '', $entity->client['telephone']);
        $recipient = 'SMS='.preg_replace('/^0049/', '+49', $telephone).'@example.com';
        return $recipient;
    }

    public function readMailer(\BO\Zmsentities\Notification $entity)
    {
        $sender = $entity->getIdentification();
        $mailer = new PHPMailer(true);
        $mailer->Encoding = 'base64';
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
