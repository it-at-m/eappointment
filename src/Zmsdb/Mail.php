<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Mail as Entity;
use \BO\Zmsentities\Collection\MailList as Collection;

class Mail extends Base
{
    /**
     * Fetch status from db
     *
     * @return \BO\Zmsentities\Mail
     */
    public function readEntity($itemId, $resolveReferences = 1)
    {
        $query = new Query\MailQueue(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionItemId($itemId);
        return $this->fetchOne($query, new Entity());
    }

    public function readList($resolveReferences = 1)
    {
        $mailList = new Collection();
        $query = new Query\MailQueue(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $item) {
                $multiPart = $this->readMultiPartByQueueId($item['id']);
                $mail = $this->readEntity($item['id'], $resolveReferences);
                $mail->addMultiPart($multiPart);
                $mailList->addMail($mail);
            }
        }
        return $mailList;
    }

    public function writeInQueue(Entity $mail)
    {
        //write mail in queue
        $process = new \BO\Zmsentities\Process($mail->process);
        $scope =  new \BO\Zmsentities\Scope($mail->process['scope']);
        $client = $process->getFirstClient();
        if (!$client->hasEmail() || !$scope->hasNotificationEnabled()) {
            return false;
        }

        $query = new Query\MailQueue(Query\Base::INSERT);
        $query->addValues(array(
            'processID' => $mail->process['id'],
            'departmentID' => $mail->department['id'],
            'createIP' => $mail->createIP,
            'createTimestamp' => time(),
            'subject' => $mail->subject,
        ));
        $result = $this->writeItem($query);
        if ($result) {
            $queueId = $this->getWriter()->lastInsertId();
            foreach ($mail->multipart as $part) {
                $this->writeInMailPart($queueId, $part);
            }
            $this->updateProcessClient($process, $client);
        } else {
            return false;
        }
        return true;
    }

    public function writeInMailPart($queueId, $data)
    {
        $query = new Query\MailPart(Query\Base::INSERT);
        $query->addValues(array(
            'queueId' => $queueId,
            'mime' => $data['mime'],
            'content' => $data['content'],
            'base64' => ($data['base64']) ? 1 : 0,
        ));
        $result = $this->writeItem($query);
        if ($result) {
            return true;
        }
        return false;
    }

    public function deleteEntity($itemId)
    {
        $query = Query\MailQueue::QUERY_DELETE;
        $statement = $this->getWriter()->prepare($query);
        $statement->execute(array(
            $itemId
        ));
    }

    protected function readMultiPartByQueueId($queueId)
    {
        $query = new Query\MailPart(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionQueueId($queueId);
        return $this->fetchList($query, new Entity());
    }

    private function updateProcessClient(
        \BO\Zmsentities\Process $process,
        \BO\Zmsentities\Client $client
    ) {
        $query = new Process();
        $client->emailSendCount += 1;
        //update process
        $process->updateClients($client);
        $query->updateEntity($process);
    }
}
