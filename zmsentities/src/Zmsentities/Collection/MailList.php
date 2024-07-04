<?php
namespace BO\Zmsentities\Collection;

class MailList extends Base implements \IteratorAggregate
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

    public function toArray()
    {
        $array = [];
        foreach ($this as $mail) {
            $array[] = $mail;
        }
        return $array;
    }

    // Implementing IteratorAggregate to make MailList iterable
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
}
