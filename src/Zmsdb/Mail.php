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
        error_log(print_r($query->getSql(), 1));
        return $this->fetchOne($query, new Entity());
    }
    
    public function writeInMailQueue(Entity $mail)
    {
        foreach ($mail->multipart as $part) {
            $query = new Query\MailQueue(Query\Base::INSERT);
            $query->addValues(array(
                'processID' => $mail->process['id'],
                'departmentID' => $mail->department['id'],
                'multipartID' => $this->writeInMailPart($part),
                'createIP' => $mail->createIP,
                'createTimestamp' => time(),
                'subject' => $mail->subject,
            ));
            $result = $this->writeItem($query);
            if ($result) {
                $lastInsertId = $this->getWriter()->lastInsertId();
                $mail = $this->readEntity($lastInsertId, $mail->process['id']);
                return $mail;
            }
        }
        return array();
    }
    
    public function writeInMailPart($data)
    {
        $query = new Query\MailPart(Query\Base::INSERT);
        $query->addValues(array(
            'mime' => $data['mime'],
            'content' => $data['content'],
            'base64' => $data['base64'],
        ));
        $result = $this->writeItem($query);
        if ($result) {
            $lastInsertId = $this->getWriter()->lastInsertId();
        }
        return $lastInsertId;
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
