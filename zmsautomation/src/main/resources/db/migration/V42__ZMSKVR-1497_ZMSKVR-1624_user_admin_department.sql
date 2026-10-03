-- Benutzerverwaltung needs a department it can see, or the new-user form has no owner list to render.
-- The other role accounts already use department 40.

UPDATE `nutzer`
SET `BehoerdenID` = 40
WHERE `NutzerID` = 5137
  AND `Name` = 'user_admin@keycloak';

DELETE FROM `nutzerzuordnung`
WHERE `nutzerid` = 5137
  AND `behoerdenid` = 0;

INSERT IGNORE INTO `nutzerzuordnung` (`nutzerid`, `behoerdenid`)
VALUES (5137, 40);
