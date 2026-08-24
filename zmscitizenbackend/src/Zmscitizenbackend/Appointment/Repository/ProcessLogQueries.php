<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Appointment\Repository;

class ProcessLogQueries
{
    public const string QUERY_INSERT = '
        INSERT INTO `log` (
            `type`,
            `reference_id`,
            `message`,
            `scope_id`,
            `user_id`,
            `action`,
            `display_number`,
            `queue_number`,
            `appointment_at`,
            `slot_count`,
            `citizen_name`,
            `services`,
            `scope_name`,
            `citizen_email`,
            `citizen_phone`,
            `process_status`
        ) VALUES (
            :type,
            :referenceId,
            :message,
            :scopeId,
            :userId,
            :action,
            :displayNumber,
            :queueNumber,
            :appointmentAt,
            :slotCount,
            :citizenName,
            :services,
            :scopeName,
            :citizenEmail,
            :citizenPhone,
            :processStatus
        )
    ';
}
