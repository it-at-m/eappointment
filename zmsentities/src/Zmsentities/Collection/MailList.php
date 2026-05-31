<?php

namespace BO\Zmsentities\Collection;

class MailList extends Base
{
    public const ENTITY_CLASS = '\BO\Zmsentities\Mail';

    public function withProcess($processId): self
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
