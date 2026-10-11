-- ZMSKVR-811 / ZMSKVR-1244: Citizen API reads infoForAllAppointments from
-- standort.info_for_all_appointments (see zmsbackend Query\Scope), not only the
-- preferences shadow row inserted in V44.

UPDATE `standort`
SET `info_for_all_appointments` = '<p>ATAF Hinweis Feuerwache 7: Termine freitags.</p>'
WHERE `StandortID` = 271;
