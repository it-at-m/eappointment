-- Scope 380 (V60) points at BehoerdenID 91, which V4 never inserted.
-- Preconfirmation and cancellation mails then have a null department and fail
-- mail schema validation (invalidSchema) before the message is queued.

INSERT IGNORE INTO `behoerde` (`BehoerdenID`, `OrganisationsID`, `KundenID`, `Name`, `Adresse`, `Ansprechpartner`, `IPProtectZeit`) VALUES
(91, 3, 1, 'Waffenbehörde', 'Ruppertstraße 19', '', 0);

INSERT IGNORE INTO `email` (`emailID`, `BehoerdenID`, `serveradresse`, `authentication`, `username`, `password`, `ssl_coding`, `absenderadresse`, `send_reminder`, `send_reminder_minutes_before`) VALUES
(91, 91, 'localhost', '0', '', '', 0, 'noreply-terminvereinbarung@muenchen.de', 1, 1440);
