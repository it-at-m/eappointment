<?php
namespace BO\Zmsentities\Collection;

class MailList extends Base
{

    public function addMail($mail)
    {
        if ($mail instanceof \BO\Zmsentities\Mail) {
            $this[] = clone $mail;
        }

        return $this;
    }

    public function hasMail($itemId)
    {
        foreach ($this as $mail) {
            if ($mail->id == $itemId) {
                return true;
            }
        }
        return false;
    }
}
