<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Mail as Entity;

class Mail extends Base
{
    /**
     * Fetch status from db
     *
     * @return \BO\Zmsentities\Session
     */
    public function readEntity($itemId, $processId)
    {
        $query = new Query\Session(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionItemId($itemId)
            ->addConditionProcessId($processId);
        return $this->fetchOne($query, new Entity());
    }
    
    public function writeInMailQueue(Entity $mail)
    {
        $result = false;
        foreach ($mail->multipart as $part) {
            $query = new Query\Mail(Query\Base::INSERT);
            $query->addValues(array(
                'processID' => $mail->process['id'],
                'departmentID' => $mail->department['id'],
                'multipartID' => $this->writeInMailPart($part),
                'createIP' => $mail->createIP,
                'createTimestamp' => time(),
                'subject' => $mail->subject,
            ));
            error_log(print_r($query->getParameters(), 1));
            $result = $this->writeItem($query);
        }
        return $result;
    }
    
    public function writeInMailPart($data)
    {
        $query = new Query\MailPart(Query\Base::INSERT);
        $query->addValues(array(
            'mime' => $data['mime'],
            'content' => $data['content'],
            'base64' => $data['base64'],
        ));
        error_log(print_r($query->getSql(), 1));
        $this->writeItem($query);
        $lastInsertId = $this->getWriter()->lastInsertId();
        return $lastInsertId;
    }
    

    public function deleteEntity($itemId, $processId)
    {
        $query = Query\Session::QUERY_DELETE;
        $statement = $this->getWriter()->prepare($query);
        $statement->execute(
            array(
            $itemId,
            $processId
            )
        );
    }
}
