<?php

namespace BO\Zmsadmin\Helper;

use BO\Zmsadmin\BaseController;
use BO\Zmsentities\Collection\AvailabilityList;

class AvailabilityCheckDayOff extends BaseController
{
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $input = $validator->getInput()->isJson()->assertValid()->getValue();

        $collection = (new AvailabilityList())->addData($input['availabilityList']);

        $overridesDayOff = false;

        foreach ($collection as $availability) {
            if ($availability->getDuration() >= 2) {
                continue;
            }

            $start = $availability->getStartDateTime()->modify('00:00:00');
            $end   = $availability->getEndDateTime()->modify('23:59:59');

            $current = clone $start;

            while ($current <= $end) {
                try {
                    if ($availability->hasDayOff($current)) {
                        $overridesDayOff = true;
                        break 2;
                    }
                } catch (\Exception $e) {
                }

                $current = $current->modify('+1 day');
            }
        }

        return \BO\Slim\Render::withJson($response, [
            'overridesDayOff' => $overridesDayOff
        ]);
    }
}
