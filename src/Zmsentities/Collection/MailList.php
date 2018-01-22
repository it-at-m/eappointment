<?php
namespace BO\Zmsentities\Collection;

class MailList extends Base
{
    const ENTITY_CLASS = '\BO\Zmsentities\Mail';

    public function withProcess($processId)
    {
        $list = new self();
        foreach ($this as $mail) {
            if ($mail->getProcessId() == $processId) {
                $list[] = clone $mail;
            }
        }
        return $list;
    }
}
