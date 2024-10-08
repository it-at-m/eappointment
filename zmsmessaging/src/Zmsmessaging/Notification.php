<?php
/**
 *
* @package Zmsmessaging
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

    public function __construct($verbose = false, $maxRunTime = 50)
    {
        parent::__construct($verbose, $maxRunTime);
        $this->log(
            "Read Notification QueueList start with limit ". \App::$mails_per_minute ." - ". \App::$now->format('c')
        );
        $queueList = \App::$http->readGetResult('/notification/')->getCollection();
        if (null !== $queueList) {
            $this->messagesQueue = $queueList->sortByCustomKey('createTimestamp');
            $this->log("QueueList sorted by createTimestamp - ". \App::$now->format('c'));
        }
    }

    public function initQueueTransmission($action = false)
    {
        $resultList = [];
        if ($this->messagesQueue && count($this->messagesQueue)) {
            foreach ($this->messagesQueue as $item) {
                if ($this->maxRunTime < $this->getSpendTime()) {
                    $this->log("Max Runtime exceeded - ". \App::$now->format('c'));
                    break;
                }
                try {
                    $resultList[] = $this->sendQueueItem($action, $item);
                } catch (\Exception $exception) {
                    $log = new Mimepart(['mime' => 'text/plain']);
                    $log->content = $exception->getMessage();
                    if (isset($item['process']) && isset($item['process']['id'])) {
                        $this->log("Init Queue Exception message: ". $log->content .' - '. \App::$now->format('c'));
                        $this->log("Init Queue Exception log readPostResult start - ". \App::$now->format('c'));
                        \App::$http->readPostResult('/log/process/'. $item['process']['id'] .'/', $log, ['error' => 1]);
                        $this->log("Init Queue Exception log readPostResult finished - ". \App::$now->format('c'));
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
                'identification' => $result->FromName,
                'subject' => $result->Subject
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
            $this->log("Build Mailer Exception log message: ". $message);
            $this->log("Build Mailer Exception log readPostResult start - ". \App::$now->format('c'));
            \App::$http->readPostResult('/log/process/'. $entity->process['id'] .'/', $log, ['error' => 1]);
            $this->log("Build Mailer Exception log readPostResult finished - ". \App::$now->format('c'));
            return false;
        }
        return $mailer;
    }

    protected function readMailer(\BO\Zmsentities\Notification $entity)
    {
        $config = \App::$http->readGetResult('/config/')->getEntity();
        $this->testEntity($entity);
        $sender = $entity->getIdentification();
        $from = $sender ? $sender : $entity['department']['email'];
        $mailer = new Mailer(true);
        $message = trim($entity->getMessage());
        $mailer->CharSet = 'UTF-8';
        $mailer->Encoding = 'base64';
        $mailer->SetLanguage('de');
        $mailer->SetFrom($from, $sender);
        $mailer->AddAddress($entity->getRecipient($config->getPreference('notifications', 'smsGatewayUrl')));
        $mailer->Subject = $message;
        $mailer->Body = '';
        $mailer->AllowEmpty = true;
        $mailer->XMailer = \App::IDENTIFIER;

        if (\App::$smtp_enabled) {
            $mailer->IsSMTP();
            $mailer->SMTPAuth = \App::$smtp_auth_enabled;
            $mailer->SMTPSecure = \App::$smtp_auth_method;
            $mailer->Port = \App::$smtp_port;
            $mailer->Host = \App::$smtp_host;
            $mailer->Username = \App::$smtp_username;
            $mailer->Password = \App::$smtp_password;
            if (\App::$smtp_skip_tls_verify) {
                $mailer->SMTPOptions['ssl'] = [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ];
            }
        }

        return $mailer;
    }
}
