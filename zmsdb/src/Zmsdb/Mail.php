<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Mail as Entity;
use \BO\Zmsentities\Mimepart;
use \BO\Zmsentities\Process as ProcessEntity;
use \BO\Zmsentities\Collection\MailList as Collection;

/**
 *
 * @SuppressWarnings(CouplingBetweenObjects)
 */
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
            $mail = $this->readResolvedReferences($mail, $resolveReferences);
        }
        return $mail;
    }

    public function readEntities(array $itemIds, $resolveReferences = 1, $onlyIds = false)
    {
        $mailList = new Collection();
        $query = Query\MailQueue::QUERY_MULTI_READ;
        
        $inQuery = implode(',', array_fill(0, count($itemIds), '?'));
        $query = str_replace('?', $inQuery, $query);
    
        $result = $this->fetchResults($query, $itemIds);
        
        if ($result === false) {
            error_log("Query failed: " . $query);
            return $mailList;
        }
    
        if ($onlyIds) {
            // Return only the ID and createTimestamp for each result
            return array_map(function ($item) {
                return [
                    '$schema' => "https://schema.berlin.de/queuemanagement/mail.json",
                    'id' => $item['id'],
                    'createTimestamp' => $item['createTimestamp']
                ];
            }, $result);
        }
        
        if (is_array($result) && count($result)) {
            foreach ($result as $item) {
                $entity = new Entity($item);
                $entity = $this->readResolvedReferences($entity, $resolveReferences);
                if ($entity instanceof Entity) {
                    $mailList->addEntity($entity);
                }
            }
        }
        return $mailList;
    }    

    public function readList($resolveReferences = 1, $limit = 300, $order = 'ASC', $onlyIds = false)
    {
        $mailList = new Collection();
        $query = new Query\MailQueue(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addOrderBy('createTimestamp', $order)
            ->addLimit($limit);
    
        $result = $this->fetchList($query, new Entity());
    
        if ($onlyIds) {
            return array_map(function ($item) {
                return [
                    '$schema' => "https://schema.berlin.de/queuemanagement/mail.json", // Include the schema here
                    'id' => $item['id'],
                    'createTimestamp' => $item['createTimestamp']
                ];
            }, $result);
        }
    
        if (count($result)) {
            foreach ($result as $item) {
                $entity = new Entity($item);
                $entity = $this->readResolvedReferences($entity, $resolveReferences);
                if ($entity instanceof Entity) {
                    $mailList->addEntity($entity);
                }
            }
        }
        return $mailList;
    }

    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $mail, $resolveReferences)
    {
        $multiPart = $this->readMultiPartByQueueId($mail->id);
        $mail->addMultiPart($multiPart);
        if (1 <= $resolveReferences) {
            $processQuery = new \BO\Zmsdb\Process();
            $authData = $processQuery->readAuthKeyByProcessId($mail['processID']);
            $mail->process = $processQuery
                ->readEntity(
                    $mail['processID'],
                    is_array($authData) ? $authData['authKey'] : null,
                    $resolveReferences - 1
                );
            $mail->department = (new \BO\Zmsdb\Department())
                ->readEntity($mail['departmentID'], $resolveReferences - 1);
        }
        return $mail;
    }

    public function writeInQueueWithAdmin(Entity $mail)
    {
        $query = new Query\MailQueue(Query\Base::INSERT);
        $process = new \BO\Zmsentities\Process($mail->process);
        $department = (new Department())->readByScopeId($process->getScopeId(), 0);
        $query->addValues(
            array(
                'processID' => $mail->process['id'],
                'departmentID' => $department->toProperty()->id->get(),
                'createIP' => $mail->createIP,
                'createTimestamp' => time(),
                'subject' => $mail->subject,
                'clientFamilyName' => $process->scope['contact']['name'],
                'clientEmail' => $process->scope['contact']['email']
            )
        );
        $this->writeItem($query);
        $queueId = $this->getWriter()->lastInsertId();
        $this->writeMimeparts($queueId, $mail->multipart);
        return $this->readEntity($queueId);
    }

    public function writeInQueue(Entity $mail, \DateTimeInterface $dateTime, $count = true)
    {
        $query = new Query\MailQueue(Query\Base::INSERT);
        $process = new \BO\Zmsentities\Process($mail->process);
        $client = $mail->getFirstClient();
        if (! $client->hasEmail()) {
            throw new Exception\Mail\ClientWithoutEmail();
        }
        $department = ($mail->department && $mail->department->hasId()) ?
            $mail->department :
            (new Department())->readByScopeId($process->getScopeId(), 0);
        $query->addValues(
            array(
                'processID' => $mail->process['id'],
                'departmentID' => $department->getId(),
                'createIP' => $mail->createIP,
                'createTimestamp' => ($dateTime) ? $dateTime->format('U') : time(),
                'subject' => $mail->subject,
                'clientFamilyName' => $client->familyName,
                'clientEmail' => $client->email
            )
        );
        $success = $this->writeItem($query);
        $queueId = $this->getWriter()->lastInsertId();
        if ($count && $success) {
            $client->emailSendCount += 1;
            (new Process())->updateEntity($process, $dateTime);
        }
        $this->writeMimeparts($queueId, $mail->multipart);
        return $this->readEntity($queueId);
    }

    public function writeInQueueWithDailyProcessList(
        \BO\Zmsentities\Scope $scope,
        Entity $mail
    ) {
        $query = new Query\MailQueue(Query\Base::INSERT);
        $department = (new Department())->readByScopeId($scope->getId(), 0);
        $query->addValues(
            array(
                'departmentID' => $department->toProperty()->id->get(),
                'createTimestamp' => time(),
                'createIP' => $mail->createIP,
                'subject' => $mail->subject,
                'clientFamilyName' => $mail->client->familyName,
                'clientEmail' => $mail->client->email
            )
        );
        $this->writeItem($query);
        $queueId = $this->getWriter()->lastInsertId();
        $this->writeMimeparts($queueId, $mail->multipart);
        return $this->readEntity($queueId);
    }

    protected function writeMimeparts($queueId, $multipart)
    {
        $success = true;
        foreach ($multipart as $part) {
            $query = new Query\Mimepart(Query\Base::INSERT);
            $query->addValues(
                array(
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
        $status = $this->perform($query, [$itemId]);
        return $status;
    }

    public function deleteEntities(array $itemIds)
    {
        $query = Query\MailQueue::QUERY_MULTI_DELETE;
        $placeholders = implode(',', array_fill(0, count($itemIds), '?'));
        $query = str_replace('?', $placeholders, $query);

        return $this->perform($query, $itemIds);
    }

    public function readReminderLastRun($now)
    {
        $lastRun = (new \BO\Zmsdb\Config)->readProperty('status__mailReminderLastRun', false);
        $lastRunDateTime = ($lastRun) ? new \DateTimeImmutable($lastRun) : $now;
        return $lastRunDateTime;
    }

    public function writeReminderLastRun($now)
    {
        (new \BO\Zmsdb\Config())->replaceProperty('status__mailReminderLastRun', $now->format('Y-m-d H:i:s'));
    }

    protected function readMultiPartByQueueId($queueId)
    {
        $query = new Query\Mimepart(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addConditionQueueId($queueId);
        return $this->fetchList($query, new Mimepart());
    }
}
