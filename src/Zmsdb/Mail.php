<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Mail as Entity;
use \BO\Zmsentities\MailPart;
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
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionItemId($itemId);
        $mail = $this->fetchOne($query, new Entity());
        if ($mail && $mail->hasId()) {
            $multiPart = $this->readMultiPartByQueueId($itemId);
            $mail->addMultiPart($multiPart);
            $mail = $this->readResolvedReferences($mail, $resolveReferences);
        }
        return $mail;
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
                $entity = new Entity($item);
                $entity = $this->readResolvedReferences($entity, $resolveReferences);
                $multiPart = $this->readMultiPartByQueueId($entity->id);
                $entity->addMultiPart($multiPart);
                if ($entity instanceof Entity) {
                    $mailList->addEntity($entity);
                }
            }
        }
        return $mailList;
    }

    protected function readResolvedReferences($mail, $resolveReferences)
    {
        if (1 <= $resolveReferences) {
            $processQuery = new \BO\Zmsdb\Process();
            $mail->process = $processQuery
                ->readEntity(
                    $mail->process['id'],
                    $processQuery->readAuthKeyByProcessId($mail->process['id'])['authKey'],
                    $resolveReferences - 1
                );
            $mail->department = (new \BO\Zmsdb\Department())
                ->readEntity($mail->department['id'], $resolveReferences - 1);
        }
        return $mail;
    }

    public function writeInQueue(Entity $mail)
    {
        $result = false;
        $query = new Query\MailQueue(Query\Base::INSERT);
        $process = new \BO\Zmsentities\Process($mail->process);
        $client = $process->getFirstClient();
        $department = (new Department())->readByScopeId($process->getScopeId(), 0);
        $query->addValues(
            array (
                'processID' => $mail->process['id'],
                'departmentID' => $department->toProperty()->id->get(),
                'createIP' => $mail->createIP,
                'createTimestamp' => time(),
                'subject' => $mail->subject,
                'clientFamilyName' => $client->familyName,
                'clientEmail' => $client->email
            )
        );
        if ($client->hasEmail()) {
            $result = $this->writeItem($query);
        }
        if (! $result) {
            throw new Exception\MailWriteInQueueFailed("Failed to write mail in queue (maybe email not given)");
        }
        $queueId = $this->getWriter()->lastInsertId();
        $this->writeMailParts($queueId, $mail->multipart);
        if ('deleted' != $process->status) {
            $client->emailSendCount += 1;
            (new Process())->updateEntity($process);
        }
        return $this->readEntity($queueId);
    }

    protected function writeMailParts($queueId, $multipart)
    {
        $success = true;
        foreach ($multipart as $part) {
            $query = new Query\MailPart(Query\Base::INSERT);
            $query->addValues(
                array (
                    'queueId' => $queueId,
                    'mime' => $part['mime'],
                    'content' => $part['content'],
                    'base64' => $part['base64'] ? 1 : 0
                )
            );
            if (! isset($part['content']) || ! isset($part['mime']) || ! $success) {
                $this->deleteEntity($queueId);
                throw new Exception\MailWritePartFailed(
                    'Failed to write part (' . $part['mime'] . ') of mail with id ' . $queueId
                );
            }
            $success = $this->writeItem($query);
        }
        return true;
    }

    public function deleteEntity($itemId)
    {
        $query = Query\MailQueue::QUERY_DELETE;
        $statement = $this->getWriter()->prepare($query);
        return $statement->execute(array (
            $itemId
        ));
    }

    public function deleteEntityByProcess($processId)
    {
        $query = Query\MailQueue::QUERY_DELETE_BY_PROCESS;
        $statement = $this->getWriter()->prepare($query);
        return $statement->execute(array (
            $processId
        ));
    }

    protected function readMultiPartByQueueId($queueId)
    {
        $query = new Query\MailPart(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addConditionQueueId($queueId);
        return $this->fetchList($query, new MailPart());
    }
}
