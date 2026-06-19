-- Start a transaction
START TRANSACTION;

-- Drop all existing ticket printers in the `kiosk` table
DELETE FROM `kiosk`;

-- Insert a new ticket printer for each organization
INSERT INTO `kiosk` (`kundenid`, `organisationsid`, `timestamp`, `cookiecode`, `name`, `zugelassen`)
SELECT 
    `KundenID`, 
    `OrganisationsID`, 
    UNIX_TIMESTAMP() AS `timestamp`, 
    CONCAT(`OrganisationsID`, 'abcdefghijklmnopqrstuvwxyz') AS `cookiecode`, 
    CONCAT('Ticket Printer for ', `Organisationsname`) AS `name`, 
    1 AS `zugelassen`
FROM 
    `organisation`;

-- Reset the AUTO_INCREMENT value if necessary
ALTER TABLE `kiosk` AUTO_INCREMENT = 1;

-- Commit the transaction
COMMIT;
