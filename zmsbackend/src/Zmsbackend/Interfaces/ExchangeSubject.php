<?php

namespace BO\Zmsbackend\Interfaces;

interface ExchangeSubject
{
    public function readEntity(
        string|int $subjectid,
        \DateTimeInterface $datestart,
        \DateTimeInterface $dateend,
        string $period = 'day'
    );
    public function readSubjectList();
    public function readPeriodList(string|int $subjectid, string $period = 'day');
}
