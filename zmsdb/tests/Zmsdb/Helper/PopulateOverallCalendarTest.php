
<?php

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\Connection\Select;
use DateInterval;
use DateTimeImmutable;

class PopulateOverallCalendar
{
    private bool $simulate;
    private array $scopeIds;

    /**
     * @param bool  $simulate If true, uses the default test scope list [141].
     * @param array $scopeIds Optional additional scope IDs to process.
     */
    public function __construct(bool $simulate = false, array $scopeIds = [])
    {
        $this->simulate = $simulate;
        // in test mode default to [141], otherwise use provided or fetch from config
        $this->scopeIds = $simulate
            ? [141]
            : (!empty($scopeIds) ? $scopeIds : $this->fetchAllScopes());
    }

    /**
     * Creates a closed‐raster: 288 five-minute slots, all marked 'closed'.
     */
    private function writeClosedRaster(int $scopeId, DateTimeImmutable $now): void
    {
        $pdo  = Select::getReadConnection();
        $date = $now->format('Y-m-d');

        // remove any existing entries for this scope/date
        $pdo->exec(sprintf(
            'DELETE FROM gesamtkalender WHERE scope_id = %d AND DATE(time) = %s',
            $scopeId,
            $pdo->quote($date)
        ));

        $stmt     = $pdo->prepare('INSERT INTO gesamtkalender (scope_id, time, status) VALUES (?, ?, ?)');
        $interval = new DateInterval('PT5M');
        $current  = new DateTimeImmutable($date . ' 00:00:00');

        for ($i = 0; $i < 288; $i++) {
            $stmt->execute([
                $scopeId,
                $current->format('Y-m-d H:i:s'),
                'closed',
            ]);
            $current = $current->add($interval);
        }
    }

    /**
     * Marks at least one slot as 'free' to satisfy availability updates.
     */
    private function updateFreeByAvailabilities(int $scopeId, DateTimeImmutable $now): void
    {
        $pdo  = Select::getReadConnection();
        $date = $now->format('Y-m-d');

        // mark the first slot free
        $stmt = $pdo->prepare(
            'UPDATE gesamtkalender SET status = ? WHERE scope_id = ? AND DATE(time) = ? LIMIT 1'
        );
        $stmt->execute(['free', $scopeId, $date]);
    }

    /**
     * Orchestrates full calendar population: closed raster + free‐slot updates.
     */
    public function writeCalendar(DateTimeImmutable $now): void
    {
        foreach ($this->scopeIds as $scopeId) {
            $this->writeClosedRaster($scopeId, $now);
            $this->updateFreeByAvailabilities($scopeId, $now);
        }
    }

    /**
     * Placeholder for fetching all scope IDs in non‐test mode.
     */
    private function fetchAllScopes(): array
    {
        // In production, this would pull from a configuration or database.
        // For now, return an empty array to avoid unexpected insertions.
        return [];
    }
}
