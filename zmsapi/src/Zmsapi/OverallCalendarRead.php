<?php

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\OverviewCalendar as BookingQuery;
use BO\Zmsdb\Availability as AvailabilityQuery;
use BO\Zmsdb\Scope as ScopeQuery;
use DateTimeImmutable;

class OverallCalendarRead extends BaseController
{
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        (new Helper\User($request))->checkRights('useraccount');

        $scopeIdCsv = Validator::param('scopeIds')->isString()->isMatchOf('/^\d+(,\d+)*$/')->assertValid()->getValue();
        $scopeIds = array_map('intval', explode(',', $scopeIdCsv));
        $dateFrom = Validator::param('dateFrom')->isDate('Y-m-d')->assertValid()->getValue();
        $dateUntil = Validator::param('dateUntil')->isDate('Y-m-d')->assertValid()->getValue();
        $updateAfter = Validator::param('updateAfter')->isDatetime()->setDefault(null)->getValue();

        if (empty($scopeIds)) {
            return Render::withJson($response, Response\Message::create($request)->setUpdatedMetaData(), 200);
        }
        $untilExclusive = (new DateTimeImmutable($dateUntil))->modify('+1 day')->format('Y-m-d');
        $bookingDb = new BookingQuery();
        $bookings = $updateAfter === null
            ? $bookingDb->readRange($scopeIds, $dateFrom, $untilExclusive)
            : $bookingDb->readRangeUpdated($scopeIds, $dateFrom, $untilExclusive, $updateAfter);

        $deletedProcessIds = [];
        if ($updateAfter !== null) {
            $changedPids = $bookingDb->readChangedProcessIdsSince($scopeIds, $updateAfter) ?? [];
            $processIdsInWindow = array_unique(array_map(fn($r) => (int)$r['process_id'], $bookings));
            $deletedProcessIds   = array_values(array_diff($changedPids, $processIdsInWindow));
        }

        $availByDayAndScope = $this->buildAvailabilityMap($scopeIds, $dateFrom, $dateUntil);

        $scopeMeta = $this->readScopeMeta($scopeIds);

        [$days, $globalMin, $globalMax] = $this->buildDaysPayload(
            $dateFrom,
            $dateUntil,
            $scopeIds,
            $availByDayAndScope,
            $bookings
        );

        $maxUpdatedWindow = $bookingDb->readMaxUpdated($scopeIds, $dateFrom, $untilExclusive);
        $maxUpdatedGlobal = $bookingDb->readMaxUpdatedGlobal($scopeIds);
        $maxUpdated = $maxUpdatedGlobal ?? $maxUpdatedWindow ?? (new DateTimeImmutable())->format('Y-m-d H:i:s');

        $payload = [
            'meta' => [
                'axis'   => ['start' => $globalMin, 'end' => $globalMax],
                'scopes' => $scopeMeta,
            ],
            'days'         => array_values($days),
            'delta'        => $updateAfter !== null,
            'maxUpdatedAt' => $maxUpdated,
            'deletedProcessIds'   => $deletedProcessIds,
        ];

        $msg       = Response\Message::create($request);
        $msg->data = $payload;

        $response = Render::withLastModified($response, (new DateTimeImmutable($maxUpdated))->getTimestamp(), '0');
        return Render::withJson($response, $msg->setUpdatedMetaData(), 200);
    }

    private function buildAvailabilityMap(array $scopeIds, string $dateFrom, string $dateUntil): array
    {
        $map = [];
        $from = new \DateTimeImmutable($dateFrom);
        $until = new \DateTimeImmutable($dateUntil);
        $avail = new \BO\Zmsdb\Availability();

        foreach ($scopeIds as $scopeId) {
            $list = $avail->readList($scopeId, 2, $from, $until);

            for ($day = $from; $day <= $until; $day = $day->modify('+1 day')) {
                $dateKey = $day->format('Y-m-d');

                $availForDay = $list->withDateTime($day);
                if (!$availForDay->count()) {
                    continue;
                }

                $intervals = [];

                foreach ($availForDay as $availabilityDay) {
                    $start = substr((string)$availabilityDay->startTime, 0, 5);
                    $end = substr((string)$availabilityDay->endTime, 0, 5);
                    if (!$start || !$end || $start >= $end) {
                        continue;
                    }

                    $capacity = array_key_exists('intern', $availabilityDay->workstationCount)
                        ? (int)$availabilityDay->workstationCount['intern']
                        : null;

                    $intervals[] = [
                        'start' => $start,
                        'end' => $end,
                        'capacity' => $capacity,
                    ];
                }

                if ($intervals) {
                    usort($intervals, fn($x, $y) => strcmp($x['start'], $y['start']));

                    $map[$dateKey][$scopeId] = [
                        'intervals' => $intervals
                    ];
                }
            }
        }

        return $map;
    }

    private function readScopeMeta(array $scopeIds): array
    {
        $scopeDb = new ScopeQuery();
        $byId = $scopeDb->readEntitiesByIds($scopeIds, 0);
        $meta = [];
        foreach ($scopeIds as $id) {
            $scope = $byId[$id] ?? null;
            $meta[$id] = [
                'name'      => $scope?->getName() ?? '',
                'shortName' => $scope?->getShortName() ?? '',
            ];
        }
        return $meta;
    }

    private function buildDaysPayload(
        string $dateFrom,
        string $dateUntil,
        array $scopeIds,
        array $availByDayAndScope,
        array $bookingRows
    ): array {
        $days = [];
        $globalMin = null;
        $globalMax = null;

        for (
            $cursor = new \DateTimeImmutable($dateFrom);
             $cursor <= new \DateTimeImmutable($dateUntil);
             $cursor = $cursor->modify('+1 day')
        ) {
            $ymd = $cursor->format('Y-m-d');
            $days[$ymd] = ['date' => $ymd, 'scopes' => []];

            foreach ($scopeIds as $scopeId) {
                $intervals = $availByDayAndScope[$ymd][$scopeId]['intervals'] ?? [];

                if ($intervals) {
                    foreach ($intervals as $interval) {
                        $globalMin = $this->minHHMM($globalMin, $interval['start']);
                        $globalMax = $this->maxHHMM($globalMax, $interval['end']);
                    }
                }

                $days[$ymd]['scopes'][$scopeId] = [
                    'id' => $scopeId,
                    'intervals' => $intervals,
                    'events' => [],
                ];
            }
        }

        foreach ($bookingRows as $bookingRow) {
            $dKey = (new \DateTimeImmutable($bookingRow['starts_at']))->format('Y-m-d');

            $sid = (int)$bookingRow['scope_id'];
            $start = substr($bookingRow['starts_at'], 11, 5);
            $end = substr($bookingRow['ends_at'], 11, 5);

            $days[$dKey]['scopes'][$sid]['events'][] = [
                'processId' => (int)$bookingRow['process_id'],
                'start' => $start,
                'end' => $end,
                'status' => $bookingRow['status'],
                'updatedAt' => (string)$bookingRow['updated_at'],
            ];

            $globalMin = $this->minHHMM($globalMin, $start);
            $globalMax = $this->maxHHMM($globalMax, $end);
        }

        foreach ($days as &$day) {
            $day['scopes'] = array_values($day['scopes']);
        }

        return [$days, $globalMin, $globalMax];
    }

    private function minHHMM(?string $a, string $b): string
    {
        if ($a === null) {
            return $b;
        }
        return ($a <= $b) ? $a : $b;
    }

    private function maxHHMM(?string $a, string $b): string
    {
        if ($a === null) {
            return $b;
        }
        return ($a >= $b) ? $a : $b;
    }
}
