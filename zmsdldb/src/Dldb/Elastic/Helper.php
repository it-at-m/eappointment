<?php

namespace BO\Dldb\Elastic;

class Helper
{
    public static function boolFilteredQuery()
    {
        $boolQuery = new \Elastica\Query\BoolQuery();
        $boolFilter = new \Elastica\Filter\BoolFilter();
        $query = new \Elastica\Query\Filtered($boolQuery, $boolFilter);
        // $matchAllQuery = new \Elastica\Query\MatchAll();
        // $boolQuery->addMust($matchAllQuery);
        return $query;
    }

    public static function localeFilter($locale)
    {
        $localeFilter = new \Elastica\Filter\Term(array(
            'meta.locale' => $locale
        ));
        return $localeFilter;
    }

    public static function idsFilter($ids)
    {
        $filter = new \Elastica\Filter\Ids();
        $filter->setIds($ids);
        return $filter;
    }
}
