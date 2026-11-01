<?php

namespace BO\Zmsbackend\Interfaces;

interface ExchangeSubject
{
    public function readEntity(
        $subjectid,
        \DateTimeInterface $datestart,
        \DateTimeInterface $dateend,
        string $period = 'day'
    );
    public function readSubjectList();
    public function readPeriodList($subjectid, string $period = 'day');
}
