<?php
namespace BO\Zmsentities;

/**
 * @SuppressWarnings(Complexity)
 * @SuppressWarnings(Coupling)
 * @SuppressWarnings(Public)
 *
 */
class Archive extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "archive.json";

    public function getDefaults()
    {
        return [
            'date' => time(),
            'createTimestamp' => time(),
            'clientsCount' => 1,
            'id' => 0,
            'missed' => 0,
            'scope' => new Scope(),
            'waitingTime' => null,
            'withAppointment' => 0
        ];
    }

    public function __toString()
    {
        $date = (new \DateTimeImmutable)->setTimestamp($this->date)->format('c');
        $string = "archive#";
        $string .= $this->id;
        $string .= " " . $date;
        $string .= "*" . $this->clientsCount."clients";
        $string .= " scope." . $this['scope']['id'];
        return $string;
    }
}
