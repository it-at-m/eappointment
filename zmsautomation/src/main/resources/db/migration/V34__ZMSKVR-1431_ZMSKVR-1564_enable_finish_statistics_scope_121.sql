-- ZMSKVR-1431 / ZMSKVR-1564: show the finish statistics form at Bürgerbüro Pasing Serviceschalter.
--
-- Scope 121 (provider 10181768) does not offer Meldebescheinigung. That service is on sibling
-- scope 136 in Behörde 46, so it belongs under "Weitere Dienstleistungen".
-- V6 stores queue.statisticsEnabled = 1, but the scope query maps that preference to
-- NOT standort.ohnestatistik, and the column is 1. The finish page then skips the form.
-- Do not insert opening hours here. ZMS-878 and ZMSKVR-1672 create the first hours on this scope.

UPDATE `standort`
SET `ohnestatistik` = 0
WHERE `StandortID` = 121;
