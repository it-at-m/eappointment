<?php

namespace BO\Zmsdb\Tests;

use \BO\Zmsdb\Calendar as Query;
use \BO\Zmsentities\Calendar as Entity;

class CalendarTest extends Base
{
    public $fullProviderIdList = [
        122210,122217,122219,122227,122231,122238,122243,122252,122260,122262,
        122254,122271,122273,122277,122280,122282,122284,122291,122285,122286,
        122296,150230,122301,122297,122294,122312,122314,122304,122311,122309,
        317869,324433,325341,324434,122281,324414,122283,122279,122246,122251,
        122257,122208,122226
    ];

    public function testBasic()
    {
        $input = new Entity(array(
            "firstDay" => [
                "year" => date('Y'),
                "month" => date('m'),
                "day" => date('d')
                ],
            "lastDay" => [
                "year" => date('Y', time() + 60 * 60 *24 * 32),
                "month" => date('m', time() + 60 * 60 *24 * 32),
                "day" => date('d', time() + 60 * 60 *24 * 32)
                ],
            "requests" => [
                [
                    "source" => "dldb",
                    "id" => "120703",
                    ],
                ],
        ));
        foreach ($this->fullProviderIdList as $providerId) {
            $input->addProvider('dldb', $providerId);
        }
        $entity = (new Query())->readResolvedEntity($input);
        //var_dump(json_encode($entity, JSON_PRETTY_PRINT));
        //$array = json_decode(json_encode($entity), 1);
        //var_dump($array['days']);
        $this->assertEntity("\\BO\\Zmsentities\\Calendar", $entity);
        //var_dump(\BO\Zmsdb\Connection\Select::getReadConnection()->getProfiler()->getProfiles());
    }
}
