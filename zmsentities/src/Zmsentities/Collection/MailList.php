<?php

namespace BO\Zmsentities\Collection;

/**
 * @extends Base<\BO\Zmsentities\Mail>
 */
class MailList extends Base
{
    public const string ENTITY_CLASS = '\BO\Zmsentities\Mail';

    public function withProcess(mixed $processId): self
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
