<?php

namespace BO\Zmsdldb\Elastic;

class Helper
{
    public static function boolFilteredQuery(): \Elastica\Query\Filtered
    {
        $boolQuery = new \Elastica\Query\BoolQuery();
        $boolFilter = new \Elastica\Filter\BoolFilter();
        $query = new \Elastica\Query\Filtered($boolQuery, $boolFilter);
        // $matchAllQuery = new \Elastica\Query\MatchAll();
        // $boolQuery->addMust($matchAllQuery);
        return $query;
    }

    public static function localeFilter(string $locale): \Elastica\Filter\Term
    {
        $localeFilter = new \Elastica\Filter\Term(array(
            'meta.locale' => $locale
        ));
        return $localeFilter;
    }

    public static function idsFilter(string $ids): \Elastica\Filter\Ids
    {
        $filter = new \Elastica\Filter\Ids();
        $filter->setIds($ids);
        return $filter;
    }
}
