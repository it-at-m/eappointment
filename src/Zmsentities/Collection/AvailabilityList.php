<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsentities\Collection;

class AvailabilityList extends Base
{
    public function getMaxWorkstationCount()
    {
        $max = 0;
        foreach ($this as $availability) {
            if ($availability['workstationCount']['intern'] >  $max) {
                $max = $availability['workstationCount']['intern'];
            }
        }
        return $max;
    }

    public function withCalculatedSlots()
    {
        $list = clone $this;
        foreach ($list as $key => $availability) {
            $list[$key] = $availability->withCalculatedSlots();
        }
        return $list;
    }

    public function withDateTime(\DateTimeImmutable $dateTime)
    {
        $list = new static();
        foreach ($this as $availability) {
            if ($availability->isOpened($dateTime, 'appointment')
                || $availability->isOpened($dateTime, 'openinghours')
            ) {
                $list[] = $availability;
            }
        }
        return $list;
    }

    public function isOpenendByDate($dateString, $type = 'openinghours')
    {
        $dateTime = \BO\Zmsentities\Helper\DateTime::create($dateString);
        return $this->isOpened($dateTime, $type);
    }

    public function isOpened(\DateTimeImmutable $dateTime, $type = "openinghours")
    {
        foreach ($this as $availability) {
            if ($availability->isOpened($dateTime, $type)) {
                return true;
            }
        }
        return false;
    }

    public function getSlotList()
    {
        $slotList = new SlotList();
        foreach ($this as $availability) {
            foreach ($availability->getSlotList() as $slot) {
                $slotList->addEntity($slot);
            }
        }
        return $slotList;
    }
}
