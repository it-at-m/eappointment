<?php
/**
 *
* @package Zmsmessaging
*
*/
namespace BO\Zmsmessaging;

use \BO\Zmsentities\Mimepart;
use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\Exception as PHPMailerException;

class Mail extends BaseController
{
    protected $messagesQueue = null;

    public function __construct($verbose = false, $maxRunTime = 50)
    {
        parent::__construct($verbose, $maxRunTime);
        $this->log("Read Mail QueueList start with limit ". \App::$mails_per_minute ." - ". \App::$now->format('c'));
        $queueList = \App::$http->readGetResult('/mails/', [
            'resolveReferences' => 2,
            'limit' => \App::$mails_per_minute
        ])->getCollection();
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
                    //\App::$log->error($log->content);
                }
            }
        } else {
            $resultList[] = array(
                'errorInfo' => 'No mail entry found in Database...'
            );
        }
        return $resultList;
    }

    public function sendQueueItem($action, $item)
    {
        $result = [];
        $entity = new \BO\Zmsentities\Mail($item);
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
                'attachments' => $result->getAttachments(),
                'customHeaders' => $result->getCustomHeaders(),
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

    protected function getValidMailer(\BO\Zmsentities\Mail $entity)
    {
        $message = '';
        $messageId = $entity['id'];
        try {
            $mailer = $this->readMailer($entity);
        // @codeCoverageIgnoreStart
        } catch (PHPMailerException $exception) {
            $message = "Message #$messageId PHPMailer Failure: ". $exception->getMessage();
            $code = $exception->getCode();
            \App::$log->warning($message, []);
        } catch (\Exception $exception) {
            $message = "Message #$messageId Exception Failure: ". $exception->getMessage();
            $code = $exception->getCode();
            \App::$log->warning($message, []);
        }
        if ($message) {
            if (428 == $code || 422 == $code) {
                $this->log("Build Mailer Failure ". $code .": deleteEntityFromQueue() - ". \App::$now->format('c'));
                $this->deleteEntityFromQueue($entity);
            } else {
                $this->log(
                    "Build Mailer Failure ". $code .": removeEntityOlderThanOneHour() - ". \App::$now->format('c')
                );
                $this->removeEntityOlderThanOneHour($entity);
            }
           
            $log = new Mimepart(['mime' => 'text/plain']);
            $log->content = $message;
            $this->log("Build Mailer Exception log message: ". $message);
            $this->log("Build Mailer Exception log readPostResult start - ". \App::$now->format('c'));
            \App::$http->readPostResult('/log/process/'. $entity->process['id'] .'/', $log, ['error' => 1]);
            $this->log("Build Mailer Exception log readPostResult finished - ". \App::$now->format('c'));
            return false;
        }

        // @codeCoverageIgnoreEnd
        return $mailer;
    }

    /**
     * @SuppressWarnings("CyclomaticComplexity")
     * @SuppressWarnings("NPathComplexity")
     */
    protected function readMailer(\BO\Zmsentities\Mail $entity)
    {
        $this->log("Build Mailer: testEntity() - ". \App::$now->format('c'));
        $this->testEntity($entity);
        $encoding = 'base64';
        foreach ($entity->multipart as $part) {
            $mimepart = new Mimepart($part);
            if ($mimepart->isText()) {
                $textPart = $mimepart->getContent();
            }
            if ($mimepart->isHtml()) {
                $htmlPart = $mimepart->getContent();
            }
            if ($mimepart->isIcs()) {
                $icsPart = $mimepart->getContent();
            }
        }

        $this->log("Build Mailer: new PHPMailer() - ". \App::$now->format('c'));
        $mailer = new PHPMailer(true);
        $mailer->CharSet = 'UTF-8';
        $mailer->SMTPDebug = \App::$smtp_debug;
        $mailer->SetLanguage("de");
        $mailer->Encoding = $encoding;
        $mailer->IsHTML(true);
        $mailer->XMailer = \App::IDENTIFIER;
        $mailer->Subject = $entity['subject'];
        $mailer->AltBody = (isset($textPart)) ? $textPart : '';
        $mailer->Body = (isset($htmlPart)) ? $htmlPart : '';
        $mailer->SetFrom($entity['department']['email'], $entity['department']['name']);
        $this->log("Build Mailer: addAddress() - ". \App::$now->format('c') . " arguments: "
            . $entity->getRecipient() . ' - ' . $entity->client['familyName']);
        $mailer->AddAddress($entity->getRecipient(), $entity->client['familyName']);

        if (null !== $entity->getIcsPart()) {
            $this->log("Build Mailer: AddStringAttachment() - ". \App::$now->format('c'));
            $mailer->AddStringAttachment(
                $icsPart,
                "Termin.ics",
                $encoding,
                "text/calendar; charset=utf-8; method=REQUEST"
            );
        }

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
