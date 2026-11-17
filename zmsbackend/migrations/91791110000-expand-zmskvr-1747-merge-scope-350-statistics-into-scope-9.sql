-- ZMSKVR-1747: Merge Führerscheinstelle statistics of Scope 350 (Berufsfahrer B)
-- into Scope 9 (Berufsfahrer A) before Scope 350 is deleted.
--
-- Expand only. This does not delete the Standort. The deletion is a later
-- contract migration, after these rows already belong to Scope 9.
--
-- Kundenstatistik is buergerarchiv (buergerarchivtoday is the same-day copy).
-- Dienstleistungsstatistik is statistik. Both are one row per archived visit
-- or service, so reassigning standortid keeps every row.
--
-- Wartestatistik is wartenrstatistik: one aggregated row per scope and day,
-- and the waiting/way/estimated times are averages. Adding those averages
-- would distort a day both scopes served. Each hour is combined by the
-- waiting count (count9 * time9 + count350 * time350) / (count9 + count350).
-- An hour with no waiting count keeps a non-zero average from either scope.
-- Days only Scope 350 has are reassigned. Days only Scope 9 has stay as they are.
-- There is no unique key on (standortid, datum), so every row of a shared day
-- is folded into the oldest Scope 9 row (or the oldest Scope 350 row when
-- Scope 9 has none) and the other rows of that day are removed.
--
-- Idempotent: a second run finds no Scope 350 statistic rows.
-- Refuses to run when Scope 350 exists but is not Berufsfahrer B, or when
-- Scope 9 is not the Berufsfahrer scope of the same Behörde and provider.
-- A database without Scope 350 is left unchanged.

DELIMITER $$

DROP PROCEDURE IF EXISTS zms_zmskvr1747_merge_scope_350_statistics$$

CREATE PROCEDURE zms_zmskvr1747_merge_scope_350_statistics()
proc: BEGIN
    DECLARE v_pair INT DEFAULT 0;
    DECLARE v_same_department INT DEFAULT 0;
    DECLARE v_same_provider INT DEFAULT 0;
    DECLARE v_source_exists INT DEFAULT 0;
    DECLARE v_hour INT DEFAULT 0;
    DECLARE v_kind_index INT DEFAULT 0;
    DECLARE v_metric INT DEFAULT 0;
    DECLARE v_in_transaction TINYINT DEFAULT 0;
    DECLARE v_kind VARCHAR(32);
    DECLARE v_hh CHAR(2);
    DECLARE v_count_col VARCHAR(96);
    DECLARE v_avg_col VARCHAR(96);
    DECLARE v_select MEDIUMTEXT;
    DECLARE v_assign MEDIUMTEXT;
    DECLARE v_left INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        IF v_in_transaction = 1 THEN
            ROLLBACK;
        END IF;
        RESIGNAL;
    END;

    SELECT COUNT(*)
    INTO v_source_exists
    FROM standort
    WHERE StandortID = 350;

    IF v_source_exists = 0 THEN
        LEAVE proc;
    END IF;

    SELECT
        COUNT(*),
        SUM(source_scope.BehoerdenID = target_scope.BehoerdenID),
        SUM(source_scope.InfoDienstleisterID = target_scope.InfoDienstleisterID)
    INTO v_pair, v_same_department, v_same_provider
    FROM standort AS source_scope
    INNER JOIN standort AS target_scope
        ON target_scope.StandortID = 9
    WHERE source_scope.StandortID = 350
      AND source_scope.source = 'dldb'
      AND target_scope.source = 'dldb'
      AND TRIM(source_scope.standortkuerzel) = 'Berufsfahrer B'
      AND TRIM(target_scope.standortkuerzel) LIKE 'Berufsfahrer%';

    IF v_pair <> 1 OR v_same_department <> 1 OR v_same_provider <> 1 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ZMSKVR-1747: Scope 350/9 are not Berufsfahrer B and Berufsfahrer of the same Behörde';
    END IF;

    SET v_select = '';
    SET v_assign = '';
    SET v_hour = 0;

    WHILE v_hour < 24 DO
        SET v_hh = LPAD(v_hour, 2, '0');
        SET v_kind_index = 0;

        WHILE v_kind_index < 2 DO
            IF v_kind_index = 0 THEN
                SET v_kind = 'spontaneous';
            ELSE
                SET v_kind = 'appointment';
            END IF;

            SET v_count_col = CONCAT('hour_', v_hh, '_waiting_count_', v_kind);

            SET v_select = CONCAT(
                v_select,
                IF(v_select = '', '', ','),
                'SUM(COALESCE(w.`', v_count_col, '`,0)) AS `', v_count_col, '`'
            );
            SET v_assign = CONCAT(
                v_assign,
                IF(v_assign = '', '', ','),
                'target.`', v_count_col, '`=merged.`', v_count_col, '`'
            );

            SET v_metric = 0;
            WHILE v_metric < 3 DO
                IF v_metric = 0 THEN
                    SET v_avg_col = CONCAT('hour_', v_hh, '_waiting_time_', v_kind);
                ELSEIF v_metric = 1 THEN
                    SET v_avg_col = CONCAT('hour_', v_hh, '_way_time_', v_kind);
                ELSE
                    SET v_avg_col = CONCAT('hour_', v_hh, '_estimated_waiting_time_', v_kind);
                END IF;

                SET v_select = CONCAT(
                    v_select,
                    ',CASE',
                    ' WHEN SUM(COALESCE(w.`', v_count_col, '`,0)) > 0 THEN',
                    ' ROUND(SUM(COALESCE(w.`', v_avg_col, '`,0) * COALESCE(w.`', v_count_col, '`,0))',
                    ' / NULLIF(SUM(COALESCE(w.`', v_count_col, '`,0)),0), 2)',
                    ' WHEN SUM(CASE WHEN COALESCE(w.`', v_avg_col, '`,0) <> 0 THEN 1 ELSE 0 END) = 0 THEN 0',
                    ' ELSE ROUND(SUM(CASE WHEN COALESCE(w.`', v_avg_col, '`,0) <> 0 THEN',
                    ' COALESCE(w.`', v_avg_col, '`,0) ELSE 0 END)',
                    ' / NULLIF(SUM(CASE WHEN COALESCE(w.`', v_avg_col, '`,0) <> 0 THEN 1 ELSE 0 END),0), 2)',
                    ' END AS `', v_avg_col, '`'
                );
                SET v_assign = CONCAT(
                    v_assign,
                    ',target.`', v_avg_col, '`=merged.`', v_avg_col, '`'
                );

                SET v_metric = v_metric + 1;
            END WHILE;

            SET v_kind_index = v_kind_index + 1;
        END WHILE;

        SET v_hour = v_hour + 1;
    END WHILE;

    DROP TEMPORARY TABLE IF EXISTS tmp_zmskvr1747_wait_dates;
    DROP TEMPORARY TABLE IF EXISTS tmp_zmskvr1747_wait_keep;
    DROP TEMPORARY TABLE IF EXISTS tmp_zmskvr1747_wait_merged;

    START TRANSACTION;
    SET v_in_transaction = 1;

    CREATE TEMPORARY TABLE tmp_zmskvr1747_wait_dates (
        datum DATE NOT NULL PRIMARY KEY
    );

    INSERT INTO tmp_zmskvr1747_wait_dates (datum)
    SELECT DISTINCT datum
    FROM wartenrstatistik
    WHERE standortid = 350;

    CREATE TEMPORARY TABLE tmp_zmskvr1747_wait_keep (
        datum DATE NOT NULL PRIMARY KEY,
        keep_id INT UNSIGNED NOT NULL
    );

    INSERT INTO tmp_zmskvr1747_wait_keep (datum, keep_id)
    SELECT
        dates.datum,
        COALESCE(
            MIN(CASE WHEN waiting.standortid = 9 THEN waiting.wartenrstatistikid END),
            MIN(waiting.wartenrstatistikid)
        )
    FROM tmp_zmskvr1747_wait_dates AS dates
    INNER JOIN wartenrstatistik AS waiting
        ON waiting.datum = dates.datum
       AND waiting.standortid IN (9, 350)
    GROUP BY dates.datum;

    SET @zmskvr1747_sql = CONCAT(
        'CREATE TEMPORARY TABLE tmp_zmskvr1747_wait_merged AS ',
        'SELECT w.datum,', v_select, ' ',
        'FROM wartenrstatistik AS w ',
        'INNER JOIN tmp_zmskvr1747_wait_dates AS dates ON dates.datum = w.datum ',
        'WHERE w.standortid IN (9, 350) ',
        'GROUP BY w.datum'
    );
    PREPARE zmskvr1747_merge FROM @zmskvr1747_sql;
    EXECUTE zmskvr1747_merge;
    DEALLOCATE PREPARE zmskvr1747_merge;

    SET @zmskvr1747_sql = CONCAT(
        'UPDATE wartenrstatistik AS target ',
        'INNER JOIN tmp_zmskvr1747_wait_keep AS chosen ON chosen.keep_id = target.wartenrstatistikid ',
        'INNER JOIN tmp_zmskvr1747_wait_merged AS merged ON merged.datum = target.datum ',
        'SET target.standortid = 9,', v_assign
    );
    PREPARE zmskvr1747_update FROM @zmskvr1747_sql;
    EXECUTE zmskvr1747_update;
    DEALLOCATE PREPARE zmskvr1747_update;

    DELETE waiting
    FROM wartenrstatistik AS waiting
    INNER JOIN tmp_zmskvr1747_wait_keep AS chosen
        ON chosen.datum = waiting.datum
    WHERE waiting.standortid IN (9, 350)
      AND waiting.wartenrstatistikid <> chosen.keep_id;

    UPDATE buergerarchiv
    SET StandortID = 9
    WHERE StandortID = 350;

    UPDATE buergerarchivtoday
    SET StandortID = 9
    WHERE StandortID = 350;

    UPDATE statistik
    SET standortid = 9
    WHERE standortid = 350;

    SELECT COUNT(*) INTO v_left FROM wartenrstatistik WHERE standortid = 350;
    IF v_left > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ZMSKVR-1747: wartenrstatistik rows remain on Scope 350';
    END IF;

    SELECT COUNT(*) INTO v_left FROM buergerarchiv WHERE StandortID = 350;
    IF v_left > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ZMSKVR-1747: buergerarchiv rows remain on Scope 350';
    END IF;

    SELECT COUNT(*) INTO v_left FROM buergerarchivtoday WHERE StandortID = 350;
    IF v_left > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ZMSKVR-1747: buergerarchivtoday rows remain on Scope 350';
    END IF;

    SELECT COUNT(*) INTO v_left FROM statistik WHERE standortid = 350;
    IF v_left > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'ZMSKVR-1747: statistik rows remain on Scope 350';
    END IF;

    COMMIT;
    SET v_in_transaction = 0;

    DROP TEMPORARY TABLE IF EXISTS tmp_zmskvr1747_wait_dates;
    DROP TEMPORARY TABLE IF EXISTS tmp_zmskvr1747_wait_keep;
    DROP TEMPORARY TABLE IF EXISTS tmp_zmskvr1747_wait_merged;
END$$

DELIMITER ;

CALL zms_zmskvr1747_merge_scope_350_statistics();

DROP PROCEDURE IF EXISTS zms_zmskvr1747_merge_scope_350_statistics;
