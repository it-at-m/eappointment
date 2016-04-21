<?php

namespace BO\Zmsdb\Query;

class MailPart extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'mailpart';

    public function getEntityMapping()
    {
        return [
            'mime' => 'mailpart.mime',
            'content' => 'mailpart.content',
            'base64' => 'mailpart.base64'
        ];
    }

    public function addConditionItemId($itemId)
    {
        $this->query->where('mailpart.id', '=', $itemId);
        return $this;
    }
}
