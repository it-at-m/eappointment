<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository;

class MailQueueQueries
{
    public const string QUERY_SELECT_DEPARTMENT = '
        SELECT
            standort.BehoerdenID AS department_id
        FROM standort
        WHERE standort.StandortID = :scopeId
        LIMIT 1
    ';

    public const string QUERY_INSERT_QUEUE = '
        INSERT INTO mailqueue (
            processID,
            departmentID,
            createIP,
            createTimestamp,
            subject,
            clientFamilyName,
            clientEmail
        ) VALUES (
            :processId,
            :departmentId,
            :createIP,
            :createTimestamp,
            :subject,
            :clientFamilyName,
            :clientEmail
        )
    ';

    public const string QUERY_INSERT_PART = '
        INSERT INTO mailpart (
            queueId,
            mime,
            content,
            base64
        ) VALUES (
            :queueId,
            :mime,
            :content,
            :base64
        )
    ';

    public const string QUERY_DELETE_QUEUE = '
        DELETE mq, mp
        FROM mailqueue AS mq
        LEFT JOIN mailpart AS mp ON mp.queueId = mq.id
        WHERE mq.id = :queueId
    ';
}
