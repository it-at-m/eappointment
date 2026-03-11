<?php

namespace BO\Zmsdb\Query;

class ExchangeUseraccount extends Base
{
    const QUERY_READ_REPORT = "SELECT IFNULL(o.Organisationsname, 'alle'),
       IFNULL(b.Name, 'alle') AS Behoerdennamen,
       n.Name,
       n.lastUpdate,
       IF(EXISTS (
              SELECT 1
              FROM user_role ur
              INNER JOIN role_permission rp ON rp.role_id = ur.role_id
              INNER JOIN permission p ON p.id = rp.permission_id
              WHERE ur.user_id = n.NutzerID
                AND p.name = 'logs'
          ), 1, 0) AS 'Nutzung des SMS-Versands',
       IF(EXISTS (
              SELECT 1
              FROM user_role ur
              INNER JOIN role_permission rp ON rp.role_id = ur.role_id
              INNER JOIN permission p ON p.id = rp.permission_id
              WHERE ur.user_id = n.NutzerID
                AND p.name = 'ticketprinter'
          ), 1, 0) AS 'Ein- und Ausschalten des Kiosks',
       IF(EXISTS (
              SELECT 1
              FROM user_role ur
              INNER JOIN role_permission rp ON rp.role_id = ur.role_id
              INNER JOIN permission p ON p.id = rp.permission_id
              WHERE ur.user_id = n.NutzerID
                AND p.name = 'availability'
          ), 1, 0) AS 'Administration von Öffnungszeiten',
       IF(EXISTS (
              SELECT 1
              FROM user_role ur
              INNER JOIN role_permission rp ON rp.role_id = ur.role_id
              INNER JOIN permission p ON p.id = rp.permission_id
              WHERE ur.user_id = n.NutzerID
                AND p.name = 'scope'
          ), 1, 0) AS 'Administration von Standorten',
       IF(EXISTS (
              SELECT 1
              FROM user_role ur
              INNER JOIN role_permission rp ON rp.role_id = ur.role_id
              INNER JOIN permission p ON p.id = rp.permission_id
              WHERE ur.user_id = n.NutzerID
                AND p.name = 'useraccount'
          ), 1, 0) AS 'Administration von Nutzern',
       IF(EXISTS (
              SELECT 1
              FROM user_role ur
              INNER JOIN role_permission rp ON rp.role_id = ur.role_id
              INNER JOIN permission p ON p.id = rp.permission_id
              WHERE ur.user_id = n.NutzerID
                AND p.name = 'cluster'
          ), 1, 0) AS 'Administration von Standortclustern',
       IF(EXISTS (
              SELECT 1
              FROM user_role ur
              INNER JOIN role_permission rp ON rp.role_id = ur.role_id
              INNER JOIN permission p ON p.id = rp.permission_id
              WHERE ur.user_id = n.NutzerID
                AND p.name = 'department'
          ), 1, 0) AS 'Administration von Behörden',
       IF(EXISTS (
              SELECT 1
              FROM user_role ur
              INNER JOIN role_permission rp ON rp.role_id = ur.role_id
              INNER JOIN permission p ON p.id = rp.permission_id
              WHERE ur.user_id = n.NutzerID
                AND p.name = 'organisation'
          ), 1, 0) AS 'Administration von Bezirken',
       IF(EXISTS (
              SELECT 1
              FROM user_role ur
              INNER JOIN role_permission rp ON rp.role_id = ur.role_id
              INNER JOIN permission p ON p.id = rp.permission_id
              WHERE ur.user_id = n.NutzerID
                AND p.name = 'superuser'
          ), 1, 0) AS 'Superuser'
       FROM nutzerzuordnung nz LEFT JOIN nutzer n ON nz.nutzerid = n.NutzerID
       LEFT JOIN behoerde b ON nz.behoerdenid = b.BehoerdenID
       LEFT JOIN standort s ON s.BehoerdenID = b.BehoerdenID
       LEFT JOIN organisation o USING(OrganisationsID)
       WHERE 1
       AND n.Name IS NOT NULL
       GROUP BY o.OrganisationsID, n.BehoerdenID, n.Name
       ORDER BY o.Organisationsname, b.Name, n.Name";
}
