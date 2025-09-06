<?php

namespace BO\Zmsdb\Query;

class ExchangeUseraccount extends Base
{
    const QUERY_READ_REPORT = "SELECT IFNULL(o.Organisationsname, 'alle'),
       IFNULL(b.Name, 'alle') AS Behoerdennamen,
       n.Name,
       n.Email,
       n.lastUpdate,
       -- n.Berechtigung,
  	   IF(n.Berechtigung >= 10, 1, 0) AS 'Nutzung des SMS-Versands',	
   	   IF(n.Berechtigung >= 15, 1, 0) AS 'Ein- und Ausschalten des Kiosks',	
	   IF(n.Berechtigung >= 20, 1, 0) AS 'Administration von Öffnungszeiten',   	
	   IF(n.Berechtigung >= 30, 1, 0) AS 'Administration von Standorten',
	   IF(n.Berechtigung >= 40, 1, 0) AS 'Administration von Nutzern',
	   IF(n.Berechtigung >= 40, 1, 0) AS 'Administration von Standortclustern',	
   	   IF(n.Berechtigung >= 50, 1, 0) AS 'Administration von Behörden',
       IF(n.Berechtigung >= 70, 1, 0) AS 'Administration von Bezirken',   	   
       IF(n.Berechtigung >= 90, 1, 0) AS 'Superuser'
       FROM user_assignment nz LEFT JOIN user n ON nz.nutzerid = n.NutzerID
       LEFT JOIN department b ON nz.behoerdenid = b.BehoerdenID
       LEFT JOIN scope s ON s.BehoerdenID = b.BehoerdenID
       LEFT JOIN organization o USING(OrganisationsID)
       WHERE 1
       AND n.Name IS NOT NULL
       GROUP BY o.OrganisationsID, n.BehoerdenID, n.Name
       ORDER BY o.Organisationsname, b.Name, n.Name";
}
