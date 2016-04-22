<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Mail as Entity;

class Mail extends Base
{
    /**
     * Fetch status from db
     *
     * @return \BO\Zmsentities\Mail
     */
    public function readEntity($itemId, $processId, $resolveReferences = 1)
    {
        $query = new Query\MailQueue(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionItemId($itemId)
            ->addConditionProcessId($processId);
        return $this->fetchOne($query, new Entity());
    }

    public function readList($resolveReferences = 0)
    {
        $query = new Query\MailQueue(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences);
        return $this->fetchList($query, new Entity());
    }

    public function writeInMailQueue(Entity $mail)
    {
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
            $mail = $this->readEntity($queueId, $mail->process['id']);
            $status = true;
        } else {
            return false;
        }
        return ($status) ? $mail : null;
    }

    public function writeInMailPart($queueId, $data)
    {
        $query = new Query\MailPart(Query\Base::INSERT);
        $query->addValues(array(
            'queueId' => $queueId,
            'mime' => $data['mime'],
            'content' => $data['content'],
            'base64' => $data['base64'],
        ));
        $result = $this->writeItem($query);
        if ($result) {
            return true;
        }
        return false;
    }


    public function deleteFromQueue($itemId, $processId)
    {
        $query = Query\Session::QUERY_DELETE;
        $statement = $this->getWriter()->prepare($query);
        $statement->execute(array(
            $itemId,
            $processId
        ));
    }
}
