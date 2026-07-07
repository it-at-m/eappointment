<?php

namespace BO\Zmsapi\Helper;

final class SearchPagination
{
    public const DEFAULT_RESULTS_PER_PAGE = 100;

    public const MIN_RESULTS_PER_PAGE = 1;

    public const MAX_RESULTS_PER_PAGE = 1000;

    public static function normalizePage(int $requestedPage): int
    {
        return max(self::MIN_RESULTS_PER_PAGE, $requestedPage);
    }

    public static function normalizeResultsPerPage(int $requestedResultsPerPage): int
    {
        return min(
            self::MAX_RESULTS_PER_PAGE,
            max(self::MIN_RESULTS_PER_PAGE, $requestedResultsPerPage)
        );
    }

    public static function offset(int $page, int $resultsPerPage): int
    {
        return (self::normalizePage($page) - 1) * $resultsPerPage;
    }
}
