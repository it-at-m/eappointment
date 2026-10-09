<?php

namespace BO\Zmsbackend\Statistic\Service;

use DateTimeImmutable;

class StatisticAvailability extends \BO\Zmsbackend\Base
{
    private const array MONITORED_STATISTICS = [
        'waitingscope',
        'clientscope',
        'requestscope',
        'capacityscope'
    ];

    public function check(DateTimeImmutable $date): array
    {
        $result = [
            'status' => 'ok',
            'checkedDate' => $date->format('Y-m-d'),
            'checkedAt' => (new DateTimeImmutable())->format(DATE_ATOM),
            'statisticsChecked' => 0,
            'statisticsMissing' => 0,
            'missing' => [],
        ];
        foreach (self::MONITORED_STATISTICS as $statistic) {
            $this->checkStatistic($statistic, $date, $result);
        }
        if ($result['statisticsMissing'] > 0) {
            $result['status'] = 'error';
        }
        return $result;
    }
    private function checkStatistic(string $statistic, DateTimeImmutable $date, array &$result): void
    {


        $exchange = match ($statistic) {
            'waitingscope' => new \BO\Zmsbackend\Exchange\Service\ExchangeWaitingscope(),
            'clientscope' => new \BO\Zmsbackend\Exchange\Service\ExchangeClientscope(),
            default => null,
        };

        if ($exchange === null) {
            return;
        }
        $subjectList = $exchange->readSubjectList();

        foreach ($subjectList->data as $subject) {
            $scopeId = (string) ($subject[0] ?? '');

            if ($scopeId === '') {
                continue;
            }

            if (!$this->isDateWithinSubjectPeriod($subject, $date)) {
                continue;
            }

            $result['statisticsChecked']++;

            try {
                $report = $exchange->readEntity($scopeId, $date, $date, 'day');
                if (empty($report->data)) {
                    $this->addMissing($result, $statistic, $scopeId, (string) ($subject[3] ?? ''));
                }
            } catch (\Throwable $exception) {
                $this->addMissing($result, $statistic, $scopeId, (string) ($subject[3] ?? ''));
            }
        }
    }
    private function addMissing(array &$result, string $statistic, string $scopeId, string $scopeName): void
    {
        $result['statisticsMissing']++;
        $result['missing'][] = [
            'statistic' => $statistic,
            'scopeId' => $scopeId,
            'scopeName' => $scopeName
        ];
    }

    private function isDateWithinSubjectPeriod(array $subject, DateTimeImmutable $date): bool
    {
        $periodStart = $subject[1] ?? null;
        $periodEnd = $subject[2] ?? null;

        if (!$periodStart || !$periodEnd) {
            return true;
        }

        $start = new DateTimeImmutable((string) $periodStart);
        $end = new DateTimeImmutable((string) $periodEnd);

        return $date >= $start && $date <= $end;
    }
}
