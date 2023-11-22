<?php

namespace BO\Zmsadmin\Helper;

class GraphDefaults
{
    protected static function defaultFormat($string)
    {
        return preg_replace('#\s+#m', ' ', trim($string));
    }

    /**
     * workstation reduced response data
     */
    public static function getWorkstation()
    {
        $default =<<< EOS
{ 
    id,
    name,
    scope {
        id, 
        shortName
        source,
        contact {
            name
        }
        preferences {
            appointment
            client
            workstation
            ticketprinter
        }
        status 
    },
    queue {
        clusterEnabled
        appointmentsOnly
    }
    useraccount {
        rights
    }
}
EOS;
        return static::defaultFormat($default);
    }

    /**
     * availability reduced response data
     */
    public static function getAvailability()
    {
        $default =<<< EOS
{ 
    id,
    weekday,
    repeat
    startDate,
    endDate,
    startTime,
    endTime,
    type,
    bookable,
    workstationCount,
    lastChange,
    multipleSlotsAllowed,
    slotTimeInMinutes,
    description,
    scope {
        id, 
        source,
        dayoff {
            date
        },
        preferences {
            appointment
        }
    }
}
EOS;
        return static::defaultFormat($default);
    }

    /**
     * availability reduced response data
     */
    public static function getAvailabilityTimes()
    {
        $default =<<< EOS
{ 
    weekday,
    repeat
    startDate,
    endDate,
    startTime,
    endTime,
    type,
    scope {
        dayoff {
            date
        }
    }
}
EOS;
        return static::defaultFormat($default);
    }

    /**
     * scope reduced response data
     */
    public static function getScope()
    {
        $default =<<< EOS
{ 
    id 
    source
    contact
    shortName
    hint
    dayoff {
        date
    }
    preferences
    provider {
        id
        contact 
        name 
        data { 
            payment 
        }
    }
    queue
    status
}
EOS;
        return static::defaultFormat($default);
    }

     /**
     * scope reduced response data
     */
    public static function getDepartment()
    {
        $default =<<< EOS
{ 
    id 
    name
    preferences 
}
EOS;
        return static::defaultFormat($default);
    }


    /**
     * requests reduced response data
     */
    public static function getRequest()
    {
        $default =<<< EOS
{ 
    id 
    name 
    link 
    timeSlotCount
    data { 
        locations { 
            appointment 
        } 
    } 
}


EOS;
        return static::defaultFormat($default);
    }
    
    /**
     * free process list reduced response data
     */
    public static function getFreeProcessList()
    {
        $default =<<< EOS
{ 
    scope { 
        id
        source 
        contact 
        provider { 
            contact 
            name 
            data { 
                payment 
            }
        } 
        preferences { 
            appointment
        }
    } 
    appointments {
        date
    }
}
EOS;
        return static::defaultFormat($default);
    }

    /**
     * calendar output for day select page
     */
    public static function getCalendar()
    {
        $default =<<< EOS
{ 
    firstDay 
    lastDay 
    days 
    freeProcesses 
    requests {
        id 
        name 
        link
    } 
    scopes { 
        id 
        source 
        provider {
            contact 
            name 
            data { 
                payment
            }
        } 
        preferences { 
            appointment
        }
    } 
}
EOS;
        return static::defaultFormat($default);
    }

    /**
     *  reduced process response data
     */
    public static function getProcess()
    {
        $default =<<< EOS
{
    amendment
    customTextfield
    authKey
    id
    status
    createTimestamp
    updateTimestamp
    queuedTime
    reminderTimestamp
    appointments{
        date
        slotCount
    }
    clients{
        familyName
        email
        surveyAccepted
        telephone
    }
    queue{
        arrivalTime,
        withAppointment,
        number,
        status,
        waitingTimeEstimate,
        waitingTimeOptimistic,
        waitingTime,
        callCount
    }
    requests{
        id
        link
        name
        source
    }
    scope{
        id
        shortName
        source
        contact
        provider{
            contact 
            name 
            data { 
                payment 
            }
        }
        preferences{
            client
            appointment
            survey
        }
    }
}
EOS;
        return static::defaultFormat($default);
    }

    /**
     *  reduced process response data
     */
    public static function getPickup()
    {
        $default =<<< EOS
{
    amendment
    customTextfield
    id
    appointments{
        date
    }
    clients{
        familyName
        email
        telephone
    }
    queue{
        arrivalTime,
        withAppointment,
        number
    }
    requests{
        name
    }
}
EOS;
        return static::defaultFormat($default);
    }

/**
 *  reduced process response data
 */
    public static function getFreeProcess()
    {
        $default =<<< EOS
{
    appointments{
        date
        slotCount
    }
    scope{
        id
    }
}
EOS;
        return static::defaultFormat($default);
    }
}
