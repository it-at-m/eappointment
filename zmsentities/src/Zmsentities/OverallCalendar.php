<?php
// phpcs:disable PSR12.Files.FileDeclaration.MultipleClasses
namespace BO\Zmsentities;

/**
 * Flache Modell-Struktur für den Overall-Calendar-Endpunkt.
 * Genügt fürs Serialisieren und für Unit-Tests – ohne ORM-Magie.
 */
class OverallCalendar implements \JsonSerializable
{
    public $days = [];
    public function addDay(OverallCalendarDay $day): void
    {
        $this->days[] = $day;
    }

    public function jsonSerialize(): array
    {
        return ['days' => $this->days];
    }
}

class OverallCalendarDay implements \JsonSerializable
{
    public $date;
    public $scopes = [];
    public function __construct(int $timestamp)
    {
        $this->date = $timestamp;
    }

    public function addScope(OverallCalendarScope $scope): void
    {
        $this->scopes[] = $scope;
    }

    public function jsonSerialize(): array
    {
        return [
            'date'   => $this->date,
            'scopes' => $this->scopes,
        ];
    }
}

class OverallCalendarScope implements \JsonSerializable
{
    public $id;
    public $name = '';
    public $maxSeats = 1;
    public $times = [];
    public function __construct(int $id, string $name = '', int $maxSeats = 1)
    {
        $this->id       = $id;
        $this->name     = $name;
        $this->maxSeats = $maxSeats;
    }

    public function addTime(OverallCalendarTime $time): void
    {
        $this->times[] = $time;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'maxSeats' => $this->maxSeats,
            'times'    => $this->times,
        ];
    }
}

class OverallCalendarTime implements \JsonSerializable
{
    public $name;
    public $seats = [];
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function addSeat(OverallCalendarSeat $seat): void
    {
        $this->seats[] = $seat;
    }

    public function jsonSerialize(): array
    {
        return [
            'name'  => $this->name,
            'seats' => $this->seats,
        ];
    }
}

class OverallCalendarSeat implements \JsonSerializable
{
    public $status;
    public $processId = null;
    public $slots     = null;
    public function __construct(string $status, ?int $processId = null, ?int $slots = null)
    {
        $this->status    = $status;
        $this->processId = $processId;
        $this->slots     = $slots;
    }

    public function jsonSerialize(): array
    {
        $data = ['status' => $this->status];
        if ($this->processId !== null) {
            $data['processId'] = $this->processId;
        }
        if ($this->slots     !== null) {
            $data['slots']     = $this->slots;
        }
        return $data;
    }
}
// phpcs:disable PSR12.Files.FileDeclaration.MultipleClasses
