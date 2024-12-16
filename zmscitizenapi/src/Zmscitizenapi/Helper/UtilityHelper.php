<?php

namespace BO\Zmscitizenapi\Helper;

use \BO\Zmscitizenapi\Models\Appointment;
use \BO\Zmsentities\Process;

class UtilityHelper
{

    private static function formatDateArray(\DateTime $date): array
    {
        return [
            'day' => (int) $date->format('d'),
            'month' => (int) $date->format('m'),
            'year' => (int) $date->format('Y'),
        ];
    }

    public static function getInternalDateFromISO($dateString): array
    {
        try {
            if (!is_string($dateString)) {
                throw new \InvalidArgumentException('Date string must be a string');
            }
            $date = new \DateTime($dateString);
            return self::formatDateArray($date);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid ISO date format: ' . $e->getMessage());
        }
    }

    public static function getInternalDateFromTimestamp(int $timestamp): array
    {
        try {
            $date = (new \DateTime())->setTimestamp($timestamp);
            return self::formatDateArray($date);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid timestamp: ' . $e->getMessage());
        }
    }

    public static function uniqueElementsFilter($value, $index, $self): bool
    {
        return array_search($value, $self) === $index;
    }

    public static function getThinnedProcessData(Process $myProcess): array
    {
        if (!$myProcess || !isset($myProcess->id)) {
            return [];
        }

        $subRequestCounts = [];
        $mainServiceId = null;
        $mainServiceCount = 0;

        $requests = $myProcess->requests ?? [];
        if ($requests) {
            $requests = is_array($requests) ? $requests : iterator_to_array($requests);
            if (count($requests) > 0) {
                $mainServiceId = $requests[0]->id;
                foreach ($requests as $request) {
                    if ($request->id === $mainServiceId) {
                        $mainServiceCount++;
                    } else {
                        if (!isset($subRequestCounts[$request->id])) {
                            $subRequestCounts[$request->id] = [
                                'id' => $request->id,
                                'count' => 0,
                            ];
                        }
                        $subRequestCounts[$request->id]['count']++;
                    }
                }
            }
        }

        return [
            'processId' => $myProcess->id,
            'timestamp' => isset($myProcess->appointments[0]) ? $myProcess->appointments[0]->date : null,
            'authKey' => $myProcess->authKey ?? null,
            'familyName' => isset($myProcess->clients[0]) ? $myProcess->clients[0]->familyName : null,
            'customTextfield' => $myProcess->customTextfield ?? null,
            'email' => isset($myProcess->clients[0]) ? $myProcess->clients[0]->email : null,
            'telephone' => isset($myProcess->clients[0]) ? $myProcess->clients[0]->telephone : null,
            'officeName' => $myProcess->scope->contact->name ?? null,
            'officeId' => $myProcess->scope->provider->id ?? null,
            'scope' => $myProcess->scope ?? null,
            'subRequestCounts' => array_values($subRequestCounts),
            'serviceId' => $mainServiceId,
            'serviceCount' => $mainServiceCount,
        ];
    }

}
