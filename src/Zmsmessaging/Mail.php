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

class Mail
{
    protected $messagesQueue = null;

    public function __construct()
    {
        $queueList = \App::$http->readGetResult('/mails/')->getCollection();
        if (null !== $queueList) {
            $this->messagesQueue = $queueList->sortByCustomKey('createTimestamp');
        }
    }

    public function initQueueTransmission($action = false)
    {
        if (count($this->messagesQueue)) {
            foreach ($this->messagesQueue as $item) {
                $entity = new \BO\Zmsentities\Mail($item);
                $mailer = $this->getValidMailer($entity);
                $mailer->AddAddress($entity->client['email'], $entity->client['familyName']);
                $result = Transmission::sendMailer($mailer, $action);
                if ($result instanceof \PHPMailer) {
                    $resultList[] = array(
                        'id' => ($result->getLastMessageID()) ? $result->getLastMessageID() : $entity->id,
                        'recipients' => $result->getAllRecipientAddresses(),
                        'mime' => $result->getMailMIME(),
                        'attachments' => $result->getAttachments(),
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
                'errorInfo' => 'No mail entry found in Database...'
            );
        }
        return $resultList;
    }

    protected function getValidMailer(\BO\Zmsentities\Mail $entity)
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

    protected function readMailer(\BO\Zmsentities\Mail $entity)
    {
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

        $mailer = new PHPMailer(true);
        $mailer->CharSet = 'UTF-8';
        $mailer->SetLanguage("de");
        $mailer->Encoding = $encoding;
        $mailer->addCustomHeader('Content-Transfer-Encoding', $encoding);
        $mailer->IsHTML(true);
        $mailer->Subject = $entity['subject'];
        $mailer->AltBody = (isset($textPart)) ? $textPart : '';
        $mailer->Body = (isset($htmlPart)) ? $htmlPart : '';
        $mailer->SetFrom($entity['department']['email']);
        $mailer->FromName = $entity['department']['name'];
        if (null !== $entity->getIcsPart()) {
            $mailer->AddStringAttachment(
                $icsPart,
                "Termin.ics",
                $encoding,
                "text/calendar; charset=utf-8; method=REQUEST"
            );
        }
        return $mailer;
    }
}
