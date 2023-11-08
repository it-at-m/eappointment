-- minimal DB data - 27-05-2022


SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;



-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `abrechnung`
--

CREATE TABLE `abrechnung` (
  `AbrechnungsID` int(9) UNSIGNED NOT NULL,
  `StandortID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `Telefonnummer` varchar(50) DEFAULT NULL,
  `Datum` date NOT NULL DEFAULT '0000-00-00',
  `gesendet` int(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `apiclient`
--

CREATE TABLE `apiclient` (
  `apiClientID` int(5) UNSIGNED NOT NULL,
  `clientKey` varchar(32) COLLATE utf8mb3_unicode_ci NOT NULL,
  `shortname` varchar(32) COLLATE utf8mb3_unicode_ci NOT NULL,
  `accesslevel` enum('public','callcenter','intern','blocked') COLLATE utf8mb3_unicode_ci DEFAULT 'public',
  `updateTimestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Daten für Tabelle `apiclient`
--

INSERT INTO `apiclient` (`apiClientID`, `clientKey`, `shortname`, `accesslevel`, `updateTimestamp`) VALUES
(1, 'default', 'default', 'public', '2020-02-07 12:56:28'),
(2, '8pnaRHkUBYJqz9i9NPDEeZq6mUDMyRHE', 'test', 'blocked', '2020-02-07 12:56:28');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `apikey`
--

CREATE TABLE `apikey` (
  `key` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `createIP` varchar(40) COLLATE utf8mb3_unicode_ci NOT NULL,
  `ts` bigint(20) NOT NULL,
  `apiClientID` int(5) UNSIGNED DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `apiquota`
--

CREATE TABLE `apiquota` (
  `quotaid` int(5) UNSIGNED NOT NULL,
  `key` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `route` varchar(100) COLLATE utf8mb3_unicode_ci NOT NULL,
  `period` enum('minute','hour','day','week','month') COLLATE utf8mb3_unicode_ci NOT NULL,
  `requests` int(3) NOT NULL,
  `ts` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `behoerde`
--

CREATE TABLE `behoerde` (
  `BehoerdenID` int(5) UNSIGNED NOT NULL,
  `OrganisationsID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `KundenID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `Name` varchar(200) NOT NULL DEFAULT '',
  `Adresse` varchar(200) NOT NULL DEFAULT '',
  `Ansprechpartner` varchar(50) NOT NULL DEFAULT '',
  `IPProtectZeit` int(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Daten für Tabelle `behoerde`
--

INSERT INTO `behoerde` (`BehoerdenID`, `OrganisationsID`, `KundenID`, `Name`, `Adresse`, `Ansprechpartner`, `IPProtectZeit`) VALUES
(1, 1, 1, 'Testbehoerde', 'Teststr.', '', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `buerger`
--

CREATE TABLE `buerger` (
  `BuergerID` int(5) UNSIGNED NOT NULL,
  `StandortID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `Datum` date NOT NULL DEFAULT '0000-00-00',
  `Uhrzeit` time NOT NULL DEFAULT '00:00:00',
  `Name` varchar(50) NOT NULL DEFAULT '',
  `Anmerkung` text DEFAULT NULL,
  `Telefonnummer` varchar(50) NOT NULL DEFAULT '',
  `EMail` varchar(200) NOT NULL DEFAULT '',
  `EMailverschickt` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `Erinnerungszeitpunkt` bigint(20) NOT NULL DEFAULT 0,
  `SMSverschickt` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `AnzahlAufrufe` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `Timestamp` time NOT NULL DEFAULT '00:00:00',
  `IPAdresse` varchar(40) NOT NULL DEFAULT '',
  `IPTimeStamp` bigint(20) NOT NULL DEFAULT 0,
  `NutzerID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `aufruferfolgreich` tinyint(1) NOT NULL DEFAULT 0,
  `wsm_aufnahmezeit` time NOT NULL DEFAULT '00:00:00',
  `aufrufzeit` time NOT NULL DEFAULT '00:00:00',
  `wartezeit` int(5) UNSIGNED DEFAULT NULL,
  `nicht_erschienen` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `Abholer` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `AbholortID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartenummer` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `vorlaeufigeBuchung` int(2) UNSIGNED DEFAULT 0,
  `hatFolgetermine` int(2) UNSIGNED DEFAULT NULL,
  `istFolgeterminvon` int(5) UNSIGNED DEFAULT NULL,
  `zustimmung_kundenbefragung` int(1) DEFAULT NULL,
  `telefonnummer_fuer_rueckfragen` varchar(50) DEFAULT NULL,
  `absagecode` varchar(10) DEFAULT NULL,
  `AnzahlPersonen` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `updateTimestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `apiClientID` int(5) UNSIGNED DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `buergeranliegen`
--

CREATE TABLE `buergeranliegen` (
  `BuergeranliegenID` int(9) UNSIGNED NOT NULL,
  `BuergerID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `BuergerarchivID` int(9) UNSIGNED NOT NULL DEFAULT 0,
  `AnliegenID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `source` char(10) CHARACTER SET ascii COLLATE ascii_bin DEFAULT 'dldb'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `buergerarchiv`
--

CREATE TABLE `buergerarchiv` (
  `BuergerarchivID` int(9) UNSIGNED NOT NULL,
  `StandortID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `Datum` date NOT NULL DEFAULT '0000-00-00',
  `mitTermin` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `nicht_erschienen` int(2) UNSIGNED NOT NULL DEFAULT 0,
  `Timestamp` time NOT NULL DEFAULT '00:00:00',
  `wartezeit` int(5) UNSIGNED DEFAULT NULL,
  `AnzahlPersonen` tinyint(3) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `clusterzuordnung`
--

CREATE TABLE `clusterzuordnung` (
  `clusterID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `standortID` int(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `config`
--

CREATE TABLE `config` (
  `name` varchar(150) COLLATE utf8mb3_unicode_ci NOT NULL,
  `value` varchar(250) COLLATE utf8mb3_unicode_ci NOT NULL,
  `changeTimestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Daten für Tabelle `config`
--

INSERT INTO `config` (`name`, `value`, `changeTimestamp`) VALUES
('appointments__urlAppointments', 'https://service.berlin.de/terminvereinbarung/', '2019-08-23 17:22:23'),
('appointments__urlChange', 'https://service.berlin.de/terminvereinbarung/termin/manage/', '2019-08-23 17:22:23'),
('calldisplay__baseUrl', '/terminvereinbarung/calldisplay/', '2019-08-23 17:22:23'),
('cron__archiveStatisticData', 'stage,dev', '2019-08-23 15:22:12'),
('cron__calculateDayOffList', 'prod,stage,dev', '2022-03-15 11:55:17'),
('cron__calculateSlots', 'prod,stage,dev', '2019-08-23 15:22:12'),
('cron__deallocateAppointmentData', 'stage,dev', '2019-08-23 15:22:22'),
('cron__deleteAppointmentData', 'stage,dev', '2019-08-23 15:22:12'),
('cron__deleteBlacklistedMail', 'stage,dev', '2020-02-07 12:56:28'),
('cron__deleteDayoffData', 'prod,stage,dev', '2019-08-23 15:22:12'),
('cron__deleteOldAvailabilityData', 'stage,dev', '2019-08-23 15:22:22'),
('cron__deleteReservedData', 'stage,dev', '2019-08-23 15:22:12'),
('cron__deleteSessionData', 'prod,stage,dev', '2019-08-23 15:22:12'),
('cron__migrate', 'stage,dev', '2019-08-23 15:22:12'),
('cron__resetApiQuota', 'prod,stage,dev', '2019-08-23 15:22:12'),
('cron__resetGhostWorkstationCount', 'prod,stage,dev', '2019-08-23 15:22:12'),
('cron__resetWorkstations', 'prod,stage,dev', '2019-08-23 15:22:12'),
('cron__sendMailReminder', 'none', '2019-08-23 15:22:12'),
('cron__sendNotificationReminder', 'none', '2019-08-23 15:22:12'),
('cron__sendProcessListToScopeAdmin', 'none', '2019-08-23 15:22:12'),
('cron__updateDldbData', 'prod,stage,dev', '2019-08-23 15:22:12'),
('emergency__refreshInterval', '5', '2019-08-23 17:22:23'),
('notifications__absage', '0', '2019-08-23 17:22:23'),
('notifications__benachrichtigungsfrist', '10', '2019-08-23 17:22:23'),
('notifications__blacklistedAddressList', 'test@muenchen.de', '2022-04-14 05:18:03'),
('notifications__confirmationContent', 'Ihre Telefonnummer wurde erfolgreich registriert. Ihre Wartenr. lautet:', '2019-08-23 17:22:23'),
('notifications__costs', '0.15', '2019-08-23 17:22:23'),
('notifications__eMailkonfigurierbar', '0', '2019-08-23 17:22:23'),
('notifications__erinnerungsvorlauf', '180', '2019-08-23 17:22:23'),
('notifications__gateway', 'mail', '2019-08-23 17:22:23'),
('notifications__gatewayUrl', '', '2019-08-23 17:22:23'),
('notifications__headsUpContent', 'Sie sind in Kürze an der Reihe. Bitte kommen Sie zum Schalter. Ihre Vorgangsnr. ist', '2019-08-23 17:22:23'),
('notifications__kommandoAbfrage', 'Berlin', '2019-08-23 17:22:23'),
('notifications__kommandoAbsage', 'Storno', '2019-08-23 17:22:23'),
('notifications__noAttachmentDomains', 'outlook.,live.,hotmail.', '2019-08-23 15:22:22'),
('notifications__number', '0174-0000', '2022-04-14 05:17:28'),
('setting__wsrepsync', '7', '2019-08-23 15:22:23'),
('sources_dldb_last', '2022-04-12T13:22:45+02:00', '2022-04-12 11:22:45'),
('status__calculateSlotsLastRun', '2016-04-01 00:00:00', '2019-08-23 15:24:23'),
('status__calculateSlotsLastStart', '2016-04-01 00:00:00', '2019-08-23 15:22:24'),
('support__eMail', 'l-zms@muenchen.de', '2022-04-14 05:16:29'),
('support__telephone', '0123', '2022-04-14 05:17:01'),
('ticketprinter__baseUrl', '/terminvereinbarung/ticketprinter/', '2019-08-23 17:22:23');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `email`
--

CREATE TABLE `email` (
  `emailID` int(5) UNSIGNED NOT NULL,
  `BehoerdenID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `serveradresse` varchar(50) NOT NULL DEFAULT '',
  `authentication` varchar(20) NOT NULL DEFAULT '0',
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(50) NOT NULL DEFAULT '',
  `ssl_coding` int(3) NOT NULL DEFAULT 0,
  `absenderadresse` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Daten für Tabelle `email`
--

INSERT INTO `email` (`emailID`, `BehoerdenID`, `serveradresse`, `authentication`, `username`, `password`, `ssl_coding`, `absenderadresse`) VALUES
(1, 1, 'localhost', '0', '', '', 0, 'zms-l@muenchen.de');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `feiertage`
--

CREATE TABLE `feiertage` (
  `FeiertagID` int(5) UNSIGNED NOT NULL,
  `Datum` date NOT NULL DEFAULT '0000-00-00',
  `Feiertag` text NOT NULL,
  `BehoerdenID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `updateTimestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------
-- 2022
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (1, '2022-01-01', 'Neujahr', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (2, '2022-01-06', 'Hl-Drei-König', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (3, '2022-04-15', 'Karfreitag', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (4, '2022-04-18', 'Ostermontag', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (5, '2022-05-01', '1.Mai', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (6, '2022-05-26', 'ChristiHimmelfahrt', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (7, '2022-06-06', 'Pfingstmontag', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (8, '2022-06-26', 'Fronleichnam', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (9, '2022-08-15', 'MariaHimmelfahrt', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (10, '2022-10-03', 'Tag d.Dt.Einheit', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (11, '2022-11-01', 'Allerheiligen', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (12, '2022-12-24', 'Heiligabend', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (13, '2022-12-25', '1.Weihnachtstag', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (14, '2022-12-26', '2.Weihnachtstag', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (15, '2022-12-31', 'Sylvester', 0, '2022-04-19 08:27:05');
-- 2023
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (16, '2023-01-01', 'Neujahr', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (17, '2023-01-06', 'Hl-Drei-König', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (18, '2023-04-07', 'Karfreitag', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (19, '2023-04-10', 'Ostermontag', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (20, '2023-05-01', '1.Mai', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (21, '2023-05-18', 'ChristiHimmelfahrt', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (22, '2023-05-29', 'Pfingstmontag', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (23, '2023-06-08', 'Fronleichnam', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (24, '2023-08-15', 'MariaHimmelfahrt', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (25, '2023-10-03', 'Tag d.Dt.Einheit', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (26, '2023-11-01', 'Allerheiligen', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (27, '2023-12-24', 'Heiligabend', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (28, '2023-12-25', '1.Weihnachtstag', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (29, '2023-12-26', '2.Weihnachtstag', 0, '2022-04-19 08:27:05');
INSERT INTO `feiertage` (`FeiertagID`, `Datum`, `Feiertag`, `BehoerdenID`, `updateTimestamp`) VALUES (30, '2023-12-31', 'Sylvester', 0, '2022-04-19 08:27:05');





--
-- Tabellenstruktur für Tabelle `imagedata`
--

CREATE TABLE `imagedata` (
  `imagename` varchar(100) NOT NULL,
  `imagecontent` text DEFAULT NULL,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Daten für Tabelle `imagedata`
--

INSERT INTO `imagedata` (`imagename`, `imagecontent`, `ts`) VALUES
('baer.png', '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', '2015-09-16 17:00:00');
INSERT INTO `imagedata` (`imagename`, `imagecontent`, `ts`) VALUES
('s_1_bild.png', 'iVBORw0KGgoAAAANSUhEUgAAAMgAAADICAYAAACtWK6eAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH4QQNCxIoVzIrUgAAIABJREFUeNrsnXecFEX6xr9V3TOzeVnSAksOkiRIEgQUFRRFEVEOUTFHTIcihjNgPD1UzBkxBzB7igkFA4on5ozhEIm7LJsndXf9/qiemZ7Z2YWFxVN/Wx/600NP2An11Pu8zxsKmkbTaBpNo2k0jabRNJpG02gaTaNpNI2m8YcYoukraBrbOExgENAfaANkAgooA1YB7wGbm76mpvFnXgwzgCnACiAM2O7kvgroAPhSnuMHRgOvu2DY2vEeMCDN6zRZkKbxhx/nAGcAPWIXsrINaqrt2H8V8KoLlg+Ak4G/A30ADEMyenQnBg5sR6dOOZimBBTl5VE++bSYpW+vorQ0GHutl4EZwK9NAGkaf/TftzOwFOhoGILd+uVy1TUDGL9fWwj7CSG55dZvuXTOSqIRByAKrAG6CiHo0aOAW26ZyPjxXVEEcZwojgIhBShQSiENgUEeb7zxM0cd9RQlJUGUUg6wO/BxE0Caxh91zERwNYqsfffO57JZXdhzn1aoSoWjTITpQ+HDyMmiqkpwzdxvuO66lQB07JjHHXccxEEH9QRqsJWzlemiMIRBKCI4b+ZS7rzzAwALmORalCaANI0/zDCAu4ETALng7k4cd2hLVNhACRNl+EGaIH36MEwQfjBMflwjGTlqIf6AwX/+czxt2mU2eJoIYTDr3LeZN+/D2JX+oL50X0g1AaRp/C9HNvAwMLlFvuSZh4vYa2A2TtQEwwWD9KOkPicDxYdh+lmzEY4+7i2Ki0N8+eWJIG1ANgyhIocLL3yT669/B6AS5G6wxy+QpeB11QSQpvG/GH7gJWBch3ZSfPNeIVlBE0xfAgQuEJAmKgYQwwfClzibPsJRH1Onv01Njc2SJdOxVaTB08W2BVMOe5EXX/wOUO8Do2CpgFnAx38KkMimOfWXGrcD+7VpJcSyp3LIqohqN8C2wIkmDjsKtoWwo+C496uoe9b/D5gWLz0/njZtclj41FcoO9pgdmSaiqefnUz3Hs0BRgJzYYyCaQIeEn+GBboJIH+dcTVwcpYfvnrboHO2pQUpOwpORAPBiWogeA7hRFzQWKDc2+41O1TFgntG8MUXmzBNv76vAUMpMIwoS5eeQE6OH+AsiTEGTAEfC1gMDP9Dg8Romld/iTFQCOYLMBc/LOiTDUoqr9fsztjURVu4/7zXhechAkPCoEGt+eij9XTpnItSCkTDpk1OLmRmZPP66z+aCtWvNfKBaoYAHwEnAq8KCDYBpGnstPEVkHfFLMHx40A57vLtpURJ2PD8J34SdXqoGQFB+6IsF2Sxp8sGMCTByD268uqrq1i7trJdNavy4bVXYIaED1236S4BoSYnvWk0+rgFOHvPobBskcDZ4CHOAhApapWRxmEXiesq5rSnu1+YYPg9zze3nW7hEKwO0K7tXKqqIkj8e3Th/JVraGtHKFNa3ToX/mAycJMF+XOPNsB9GRlkPHGzoK1M51WqZJolRO0lUnjZmHCpGGnpWNyCxG6IbXNjBQKfXxEJS5YtW43CLsygw6Ii2lKMQlOsGcCiP9Si3eSk/7nH+UCzCXvB0D518AGlEsqVYyXOscOOXUsoXMKJaDUrrnxZ2nGPPzfl3AA7cvmc0TRvHgA4aAPPDMpiIh3xCwgKHXgf3ESxmkajjBZAiQAi3wuMMs/Cn/aXFi5FcqlRuhiIl3rJWMQ9+RrS7wYbU2nYttEtQwhWrqxiyJA7ADb0YmFH+F5uIWxvpEzBOEcnAGz+Q1CtJor15x1XAyP+cY5gbM9tJe6O+0CRwq+8TruIkwuRpGilPC9GxYSHjGwD3VJAYWGAzz4t5YcfSnJKeLbYYO9Pm5NFBa2wWQ1UAav/EIt3E8X6c45s4ICsTJg8BpxtXWsVKdTJc6hoUgxEnyM6mJhCwRJ0y0u1IttMt4RUzL5gdxeL9owqvpRBpNETS0BYwEQB05t8kKax3aMT0LldIQzo2MBnKuWCxPJM7miyb2J77lcWQnkeiwsEFU3xUWKBSHub3saokV0ZPqI9QK8aFh7VjPOsCMh8WgioACYAmaIJIE1je8YgIHP/EQIjUL/BkBmaWdUGSR0TPMmauA68HU1OS3E8VsVOeR1720DiUMVzzx7lvk9129cUta1CGm0JSxNTwJcCxvzP/eQmH+TPOf4B9L3hYkGnvLr9D8eGM2+DYSMgR9Xhk8Tnn6rtY6QEGoXXb/G6Lip1Givtj2zFJ8nOhnVrI3zyyTpDUZXrZ/DrJqgA2VQigd4KaoDVTRakaTRo9AMYOhAcp24m5WsJS9/Jo+8+ilVb0izFcQk4kka+jdROcHSiCDsm+VqJI+l5UY3MbaBbCjjr7EFIIVCooyt4qG0Qv9EMS7QgIGC9gPFNFKtpNHh0y/ZDZrN6qIGEl1fAnsO78s4TR7L/iYpKWZ9PkuK02ym+iGPVBokT1QmOsefHHx+JU7OtgaR/v0KOP2EgQEaEtU+t46KaSsJGK6qFQaFrytqKJoA0jW0cuX7A362L9pfrZOgt4J83wMGjiujTOpvrz5/AsGmKcmoH0+OWpN7DCwiPxfAGGr2+i9o2x91WQe67fyJdujQH2E1inF9DlqjAlIIKAd8LOPt/9m2bjfliSqmGZLA1je34inNycjtXV0OfrgKq637gls3w/meKA0cWQdDib6OKUMYB7HHkYj56QpCdlm5Z1Epk9FI4IcDW8Q+BSPZ9RB3LbzysUldaisBWNdx55wQmTnyMaNS50Mm9/amzT3h5Y9duLWWrAVmqd0+faN16JhBQovFnlxJCODsdIEqpPGAesFsTSHbemD9/fuYRR0wltw31Jr+++5Fi/+FFyCwfBC0I20zdsxMlxftw6N/f5t+3Q8Byk37jP6LjSrnKM+tFwmGIyToOntSs1GCjSONpuK8lzbSp8gLB+PEdmT59IA888ElBVq71/vk39il2lEII4Vo8wU4Ah4VudXTp72FB3kE3Ddt5y6f7hUUiEZYsWcLatWtRStGiRQt69epFnz59/rqmw/3swWANAIVt0A16ZHq3Yul/4MyTB0NpKDHXq6OcMakHtmOz+xHL+PAxQSBV3VKOa0ncFxZu2rz0WBNDxG9rkCgPGJR+A8IjktkepawOS2KrKPPnH8w33xTz4Ydrig6fcnjR448+iemTIGUcaKLxUTJEKeUTQly40wCilGqxs8GxceNGFi1axPz58/nss8/SPkYIGDNmbyZMmMDYsWMZMGDAXwgiemJUV2uAFDVPWf09w1Hw7S9w3awiKA/rSRkbQYuzp/UhEoEps9/h6RvrsiQRPSlFqiURHufeCxKPRCxdUEj3eYaXqgmdv5UGJJaqZuGiwxg86F6ee+5Z5t5wHZdedimW5bgWRMQXikYeRwA7DyCN7cukjhtvvJFLL72UcCiEoxTZmXDcJEHXHhAIwJo18PWX8O/3FW+//TZLly4lIyOD/v37s2jRIjp06PDnth6e2+vWrdU+eE7dMLLzALsVZtROn95eHmHW33pimooBh77Dp88IMqwUdhSnWzHDoBIWwEkpxFK4PolKxEWSirZUojlp3JL4a4FEIClq7+fj/5xBp85zuezyy1i3fh133H4XtlIIFxyq8Tm83NkTu9Za9vlvwfhCsz3DnxGgZO33zDnvLN5+awmGFBw6XnHucYI9RigoV7q7rHI/Xp7mx6+/I3hqseKt94KsWLGCjh07cvoZZ3HU6edQ0KoLkXD4TwkRpRSBQICf1xYDkOcHR6Rh/A5UBqGgVTucyijSV8dvH7b4+1H9kMJg0rlLeep6KDBS4ipekMRMdK1ExwTlEvhQ3rkmhX6TXv8kbpGiLkhELXrYoZPgww9PYezYB7n77rsx/AFOOPdKpPS5Jb8qpUy4IYZY0SLLpENzfwPs9o5TrNbARu+1RSt1ZEo52/N68O1nb3H9zOMIVVeRnwtLFwr6NAefrVAtB6DaDodAXuLLLluP2PQxovxHHBMqETz6Bpx1iUZodl4+s+fdT68BY7cftf87eIASZGT5ufH8Y3l38TO885Bgj561mYqU8PBywbpfR3HhYT22/uI+ySPLfuW8a1/ju1cFzdK1wBJGSmp87X5a8QZ00odKmz6f8ljv89PQLSHg558seveehxVVtOnQhesfe5WMrEIc29bZ+2L7ANKlRQZDO2d5r64RQnRskGlpDMYs3A/akMPnN1n57pNcccrhhKqrOHayoOwLwcACiZnfD2fILFSPQyA7T9s/U4FpQqv2qL7TcHafhWgzijx/S848SFH+uWDKAVBdUc7lJ05hyXP3YJiqwe/rf3lIBEIopIBwsCo+Z9OOfHjlVRi6SwHbJPtEHaaP68pdV0xgn+Mkv4bSVNIqO6XYKlI7b8tOBBdFPGhYR0wlNRMYJ+0i2a2bwZo15zN8eAc2rPmFY/fsyXPzr6SmchNZ2Vmgtud3bBio/hCBQqXAdhyycv3cecWpzD1vBgCvPCZ44B/grBPYfU9C7XIgmBJUKAHBWJArGobIZrAqUa37orofiFM4jpwqxRNXwPuvCLIz4N5rLuSRmy/VTuWfxJIol8MrRIIiGnWYfx988LGgZ7dmyTOjvlEd4bChbZl/zRQGTVB8VQzCqgMkqcmNtYKEbrQ9DiKrHnB4IvRpMsocJWhdCEvePpL77psMwJN33cRZh+7O/H+dT25BFrbt7NTv/n8GECkNApl+IqFqykvXUlW2mqtOm8ybzz5BYQt45ynBAd2AqEL1PBKkQTwJDuXm+0TADruHe9sKg1UDThSV1RK16xGIQGtGNFOseEbQohm88NCdfLXiBcxAhua0fx6tF8epf0JEK2BNsaB9h2ZgGO4h9VlKV2YVaaWvwYXZfPLKcYw/Jpul65T+ymuBJJa75UktsVMthr4ubDulxNcTZfemq9QDEqUgEFCcdNIurN9wPn/7Wx+EE+KFh27npLG9Kd34A9LYedPY/L1/Y8dR5BXk89PXH/Dobf/k11XfUllWRrCmCtuKsktH+Nf1guwC2OyDFm0EFD+KqvCj/AWojntBs05gh7SDoyzXmbTdH9BOrHZK31ZFu8Omr+nr+5mPXhD03Ftx9ZkncMW9+fTabYwLkp0iH+4cc1vP+GE19OuaD5l+qLYSSlJMlvU+X3muu5Ozs9/g8+encuCJL3D4pBLOny5wyj1LqbJdRiTAUK6iFWvgUFtfEiiUULW1HKnSaz2GP61rbCuH1oWCp56axE8/VXPW2YtZ/MoPnDN5DFNPP5cjZlxCTWVYb8vwZwSIUgrDNKip/I3bLjmO919/ScuVzaB9IbTMgdxsqAnCebMVwQhUVEEkDGP3EJx1UpQRXTeR/+NCnGa7ojqMAGKAsLT8oqwEQJTjAUwU1aIbjlVJp2gJD98iOPIsh7uvOpcbFy1DihzYeRp7I3l07kSOvT+7dpa5EPDjL4pB/droSRwPsCmP7Eri/9Kd4IIkoDQPW7y6aCrN+97B6nVww9lurMRrSVQsAJiiUKXJP9HSrJW4JtxAY+ycWvIrfWlBohTYWHTq4uOVl6fxr399yBVXvMVjt11HJBLhqDMvIxJ2/nwAcZSidbvm3P/PC3jmvtsIh4N0bgvXXibYvSsU5guyMxxXxNfff9CCyqBkc0ixei3c+oji5C/htjmCSUO+wvn2V1SPiQknLxUgjh0HkFA2OEFo1hURKmPaGIu3pwnue+IX/nnOdG58cimlm0rjfP8Pa0mEwDCMuAKbTvlZswE6di/Qm6vFqYcHBLGFXDpuTbmTeIxrYUTrTC67ZhlXXwz3Pah4Yxm8t1DQyvSovo4NMpKSq+VaGkckA1uLR56Iu6iD3CtP7pavTpFVCImtgsyePZhx+3Vhn70fYtE9N9GysIg9JxyLlKLRfsPfxQfx+WzOPXxvHr/9X0gZZOYJ8Mv7giMGQOfmOWRmtMbJHY5TeBBOh6moToeQ0XYkrVp1oFerHPbvb/LKzbDqbcGiF+HR9wWOU4H4bpHm1FZYUy7b65OEEZY+9P1hsELQsg92Fdx7HezSGb74cBlP3nmtxzSLP6Rfotwl1PTrEsKw7TKalAX7x/Wwa+82LkAM7btJjx9iSM9tkfBTpOc+0+T+Jz7nuDGC/y4XjN5d0Hqo4rmVbuxFeEBip/gRdqIKMcnnUFGtbnlVMJWqcKW8xlZaUdjKot+AXBY9fTiGIXj0lmvIyIwxR/XHB4hyoHnrLC47cSKffbCUdoWw7DHBTTPBKS/E6Xkkqv+JqAEnobrug2rdG1XQDlXQGdVuEGqXg1F9pqC6H4yTPRR/qeKx6xS92sM9SwRRO4xYs9x1GDVIhBNG2GGE44LC8YDHiYAVROS2wVkP868TmCY8eef1OFYllmXHqYZS6g8GFP1eAgGt3wet9N/3llJoVZjv+ghG3Yf03naBIQ0I+Lj3qa/ZvT8UdQRnE9x/Ebz7lGDmHMUBMxWra3SMT9M2u3bprapDvbItV91KkypvR2v33bKtrYJEIBi7bxeOnj6IyvJS3n7pCUyfP+F27eBvuFMBkpktOWncEL5e+SGtm8MPrwuGdJA4GUNRA0+FgrY6huHUgFUF0RqwglqFilZDtFJPbDOAatYF1f9obFHEoCLBEXtCGAHBYohUgaWBEbcWSUckYUHskN4vwxdg1EDFEQcJqqsquOqMoylo0czzhf7xaJZSisxsnaheFan9Dm0HwmHILsjWap9hgGnUVrMMmbhuxh5jgs/AsmHBcz9yzT8EVLqGIgx7dIKf3hCYQJe9FFc+BjX+mB9kJ0u6tgsSO5qmMZ1V25I40biVqVXRuA0gsVWQW28ZB8BLD99HfrPMuMXdUUaw0wCSmZPB/Osv5L/ff0X3zvDlS4LskMDpfBiq+3hwqhIm2vLSo0iaCe5O/NAWaLsbqs0gmgtFrgmoMMIOueCIuK9Xx2s47v3RGvBl4ZQbPHIddOsMX698l+cWzENK6Qo/CUvyRxKwsnLzAChP0wxd2VBjQWZupgcM3sOs35IgKRMGocr1jNwtJe1EgqiAxfcLXpovmHu7ostYxfHXK1b8ArK1hSQCljvZ7VilYSRtoFCkaxbhbR+U1HGlfpAoFHl5mQDUVFfiODpfrDFAstMA8vkHr/L8Q3cD8Np8QSsfOB0OghadwK70ACHkTtxQMh2KgcVLlRx3cvuzUK0HoBxDR8bsKKgwOK6FcEJ1g8zxgNA0sTfDfXMEpgHPP3QbVZWlml55fpA/CkiUo8jOba4BUp2uxhyiNpimLyX2IVMsSIpVkQmr8v5Xm5g0oUY3wRa1fRynAnbrotnV5i2CR58VjJii6LuXYuEKm8oMi0goisCqHRCMVxim1LHHYyKxVkMpIIlZljpAYooA//jH2wDsf/gxlG+uwXFUo4Bkp6hYq1atYs7JUxHAkicFXZsr7HaToFVPsKo98qtbexA/e9Wn2teF934zAwr7JVYqy64dC1EpMZFa9zsICXv1hhH94d1Pf+P5B2/mpNk3UFNVhSMVUgkaUqkT472ZWVlk5QSQUuhgv9LzVAChYJSqimrAaZDa4jiQk9sSgJLK2stbLCJh265PIdBKVUzyiqtYKv3ttnlcfsj9zL+SOvvdyAy49EYIBgXdeo2kZ5+9+ew/z/PNf79k6umKvJwIw4dYDBtgccAImz2GC8iUUA5OVCF8idR44RHPdKfGaPqlW3gorzSTkGsIwSuLf+Xaa5eSlZ3LfoedSk11JaZpxl9GyViIRjS4d3yjA8RxHC655BKidpSTj4bRXcDJGgDNiiBSnmby1nFOut9CON6goFXPa1ge8NUBlHgLTv0NiBpYfJ8gZ4jixYdvZ99DjqR1u120Air1JFJsPY/HtmzymmWwad33vPz483zy3jL++91XVFdt0T+m4aNdp270HTqS8VMOp/uuI6ksC2OY29Z9ybEdsvO0D1K5hZQ6C+12+A0IhSwwMxOfsVYNR3qAfPaf9WzaWMqgHgIVTk83vtwIC57Sj59y9Dxs22LQ7ocTrK7gzVdu5OvPX+XNZVHeWBbh6lsiQBUHjPZzyJQcJu+fS16mjVkDjq10TFBqpVmBG3j0BhTrqE50QSKEwZIlm5hw4CMIIZh8woUEqyxMn4ET//F0LpsGiaq/f/HvAZAVK1awcOFCCnJg3nkg7VxUl9Ga3qSb4EmWxAsQC5EU7EsDhDpBZiUChUn/d9KKQ8oH2dVw02zBuf9S3H75Wcy5ZzFSSjchrs4GUHEKtn71t7z46M18vGwZFVuKk+7PyfEhBNRUR1nz83es+fk7Xn1qPlk5+ew9cTL7HX4iPfoNo3JLZb2RYKUcsnM11w5Xur9eJDkOkuWDmvIImDkJ8CiVFOdIe82UzL11Of+YGYtZ1I6xhE3Y/wh9z7GnPoCUJspxiIZDGIbJpKnXMmHyHFb//BHr1nzF6l8+5r8/fcTidyMsfreU0yilVXNJj14ZDOidSad2AXp0z6ZLxyyK2mXTur0P/BJsw40K2glF0XYTEw0TCPDBh+u5ed5HLFz4JQATj5zFXvsfjx2NIoRKxHeE1M+LB1kblmLe6AAZO3asVhMeE2QHFXb/w7TKVAsQ20il0k38JDA5CbCkAxL21pMSTe22HLUH3NAaVn21krU/f0LbTgPx+U0ct8pB1VEXrRx46dEbeeuF5+N/a+DA1jz77AkUFfkRwgIniEGI4rJy3lu2hscf/YFnX9rI4icf5OXHF7D/347n7KvuoLoyVK+K1bKwjXZGy7X75QWIYUDAD6VbqsFfCJbjsRgpFiQJMIqyqigrP13NY/MEztra64Bjwy3PwYZS2KX3GArb9sS2Ip4AuCQUqkQIQYfOA2nXYVd2H300GZl5bFr3HZ989CyfrHia4tIaipfX8MEHIZdyuu9FCEwJ7dpk0LtvHv13zad561x6dC2gS+dcWrTMZuPGMK+89g0PPPAFq1dXoBRIKTnl/AV06zWMmspqTL/hBusFAgchBY7QVEwlAWXbcxgaQ35srZTa+MADD3DKKaew93CH1+8S4O+Caj+ylmVIS6cc2+0Bq+qxDlYCEHW8RhJwGkI4bZA1cNerMGOuok37ztz/+g+UbylDGlI3CnQTJUWaIp+SDT/x+qL7+fn7r/lyxXvx+445ZiCzzx9A312zsSrLEVYIQ4Qhw6HktzJuX7CGW+9cz5Yyiz6Dducfty8ikJmH48lS1SzIQdkQCW/h2DE9GDUE3n1S4JR6fkwDLrxH0aLzZGbP2guC0eQ8rLRWRM/+Fd+Vs/jpucw5RtRuRudAJBc6jIKSLYqTz36Klq06x2mnzrcSaQhRLJVFEAjk4A9kU166jg0bfmBz8S9s2fwbVZUllJX+RkXFJirLN2JZkW36uQrb7kK/QeMZNfZYsnLzUEQxfQZmwMDn12fTZ2D6DaQhkYZAGgIhBV1abns9SKMBxLbtjWPGjGH58vd4+hbBIcMUdDkA5c9zVQin9kR2J7pIS7tq+yEJADiJ/Cu8Trizzc2T0xLsMpBZsNt0+OwnxVFnXcEhx8zEioY1SGIFLnWko1iWhemTZGYGWHjP1Tx661w9TRTcdMMIZp7eBTtcrWVtK4SwQ6hokC12kIGjfmbtRgt/RiaPvPMzQmbEAZ4AiCIjM5tDB2bRqxt8+7a75VrsI0h49HXFsp9Hcd+906GmJj2lSrEe5GRxxJH3cd60LxhcVLtMUTaD42bDQ88rBg2bzMQp1xAOV8b9HgXI1B2slPKcEpxNqfgmh9iWBrCQEp+ZgTR8OHaU6uotlJetZ3Pxz5SXbyRYXUZNTTkom87dhtBv0EH4/H4cJ4phmhg+iWFKzIAZB4cvYOqzz8DwGf97gPz6668bO3XqRJsWsH45OJX5qJ4HQbjKk1SYfBaqLiCkJh7WZSk8FqUxpFgH2AArNsDoGYrs/JbMW/ghGVn57pdLfM+MdAARQmDbDoZh0Lx1PpFQiPuuPY+Xn1hAOBRk5tl9uOmaXbCrKuMAwQ4iIyHIjbLPERt4+70oPQcM5tqHXiMaEXEVLAaQgpYtGd9D0CofNn0rcH5Lfg8ffK+YfXtn3v/wcghVpHHKa4NlwxbFsN3P56eXBUaa9eXLDTBwoiKQkcOFV35IKFSpc8KEiFtWzftFUqff2N9SHnXP2+zBSx1VHFAqxrgQ0sSQJobp139PSiKhSizHQkqBlO7ENyWGz8Dnk5h+A1+GiS8GEr+2IoYpEVIgDEHXBgCk0XyQu+66C4BrLhVQrlAdBkGozONrOK4aVYef4HW8kyyFXTdYHJtG3/NRwO5tYfAu8NF3JXzx4RsM3vNQhDA0OCRpHXalFFKYFLT08daLi1j64rN8//kKHMeOFznNu/UbNqwv4/F7e+FUJmIADhZsCfP6ggy6jbb4/vOV3DjrBObc9wJbSko9H1HEazQqqzy6rgerA/sIPlq5LpFqkkqnUpWsgI+/n/gI15wLPul+pV7macMNDymEkAwffQzhcDWGYejJJjWAkQKpVw/PwqFcX1ifdWxJxAGgHBUvWovRNFKy4hUOthPBjkTiC1DM36llCZ1kkCUASa3XbcicaTQLUlRUtLF4wzoiqwT2Zj90GecG7RITW2zVqbb0Mu5szel2dk414EbA0lngn2yAwTMUmVm5PL58LdVVIQxDIgwRl+VjbWiyc3P45ftPeePp+bz4yH1JL5kRkAQCeqVTyiFUY/PkQ304eLjfDWAG4ykwIhKiMhCmaLhDVTVccsejtOvYl6zcXDJz8skMZNOsVSaHDujI5o1rUKtFrYCebAWtBhh899M8WjQ36wBIwpoUbw6xzz7X8uWbZTjrkl9LKFgHdB6ucITBzEuW6FwwFxgxymIYEiFjflqCYsWNlaNw3Akc26JauY0oEo1PVC33SNQyNZ6YiNAOupRoC2IamH6ZoFYBE3/AwPRrK2L4pAaiiWtBsn8/CxIIBNpFIhH2HiGwy0FktUJFNLUSqSs/9ci2Xj8lHeVSO7G8coN+a7gC26AiOHZ/wUOvVXLPtedyHAuTAAAgAElEQVRx0gU3EQ6FMAQo6bbeVIqc/Ayu+/tRrFjyUtxSHHRQD449tj+7DcwjwxdFOWGkE0Y5IcrLKzn+1K845J1dcNZGkqrylLLICSr+OUvw96sU1888EX8gA38gA2lK8psVMnCPPfG5Gb2V1dRuIWrDHrsrvv1+E6NGdvAsx+mAYvHqkrWcefwW1LravocohGOnKSwFY8bNIDu3BZYVcsEhkaY+G7HbQlcsCo8sjQLbVhhK6Z8xttI7KkEdHU8qvIpZHVdyUjGYeGMjiY6L0pBJYNXX3XR3IdI3e+R3VrGAc4CbZ54G10+XGC27Q3ZLtyy2DgqVzhGPW5CUgJ/auXXHFLtyqeMe7lsqiUL30xS2mctNT3xIbkErDCnAEPh8Pqoq1vHPs4/m+y9Wkp3tZ/JhvZn7r/0oLAyAtRkrFNTZxZab/hINIZ0wz75djFJhDhth4kRqEkmUlm7WtiUKRfsohg+Bygoor4DKSsGm0uQV9af3BF18yZt3SgMeeEtQ4TuBv58ztO5wP4AoYMCup/DSbeW0zxG14h6fr4XdJikyMnO55LpPqa7ajGEaekL6JKYpkT6JYRgYLliETBROqRiVchTKcVyAaEA4jmtFHB0cVN4M6hhIPNRIJe1VIjTTjQHVEO57MTADEr/fhxmQCethaid+e5z0RrAghgH2ENOAHu0FpqlQvkw3MJiOQqWRY9PGN3aCf5HOKS/RtIqYKuyeBdDKB/vvBs98UMlHS59nzMEnIn0GwlGUFv/E+dP2o7y0hDZtslm27CR67JKFUha2VQ6WhRAa6MrNPRIqimNHmbxnBiUlNspOtiCxz9uiJew/QvDCPVBZDBFbb5JcExJ8swYWLYbHn1aUFEPXouRvyY7CQWMUs27boFU1M81PLATgZ97N/6Zzy3I6Nhe6kWJK3OPWx7TvMXqfU6iq9IDDkEjDQJpCS6k+Q9McD0DiRYoxamUrbMcBx8B286Qcx0l+jEpQsoSblGL53PcvELobkRQIQzvpRgy0ptDvzxBIIdwCKrar4VwjJCvaPqA7Avp0IpEKkDYr1820tbyZuyHP2U1IVNbOBYdwQbExQau84IhZEuHA3OP1V/rwLVeQm5uBbSuUE+Gas46kfMtmDjywB+vXX0iPXQIIBFIoz+Yy0eSzCwQnGqF5loOyrETintdKVsO4MUAUsv1QkAntAtA9Dyb1hccuh4ofBG+vqL3ESQl52VBe8gNSZtT5FZSX13DFnJe4/RqBitTOLqgw4cU3wDT99B98MFJqDo/EXbEFppmINWjub2ru79dnX8B0FSV99mf4MDNMAgETX4aBL8OH373uD/jwu36DL3429Dn2+n4zLuX6AtKVdLViFQOq6cq6RkzWNRKqY3JGxO8SSZcCHD/Q3TRgl44K5ct2s3BDtX0LJyUvyom411TKCrETrYYb7yDosSIqGRixawro0gzOO0xw4zMR7rjqbP5x80OcMWkEq3/4TrsuG6oYOuxOBIJmzfyMHduJ007tTV422FUpGa22t6OHp2YiZXdYZcORo5Oj5DEu7aDfe24OyLCbj2Ul+7FGOdjRH3CcAFJG0qyDikcf+4pTjiqjQ57ASQneSz888jJsLoORY44kN78V0WjQzYx3KYtPYvilnpB+A9N1lL0iRnzxd2mWE6NbysGxDU2vlNJ9HxyF41Gj4kwrKSM34aDjRseFITR4TZdmmTJOt6R7n5SahsqGi1g7PBUFBDoIEV6dnQmVn4MTaQ1GTkoALyVuYUWQIoISgopqqLT1b6yUrgLNFJCfAf5AcufLHTd2QKkHDHbK2Xt4aFiZAd1OUwTtLKaeOpuHb5mT9uVbtxR0KVKs+BweuH8kxx7WChUJemIeIYgG9eJhhXVxmB1y6VUDcZ4Bn38LA3rU3n1ZKbjoHsUeB89i0qSepDZms+1sitpM58uXoEWaHV1kayjoqyirhitu+pZQsCJOo7wWwxdXiWT8fmkk14PHYh/K0b0JlOOlVDH/xHXWVSIBIl1sJElhcyV3KSXCQAsFrlhgmNqCCCkT8Ssp9dMlDeqsuKM+iILIAKXggOFuU2PhT7TkqZV1G0VFLYzmileXww33KqQj6F4IHVpCXqbOjvi1BL76TTFgV7hshqDAp9XQ7arDl+4KW+VZkZ00tEqlAYf7/GYCJg2HB5fUULppHcP3OYRPlr9OJJSoWrrhMsGJU2Dq6XDrLaOYdkghygq5TdQ8foby0K94Wel2uE8h2LVL+q3JhYD9R8KsKx5n0qSbgS1ezZF9x87llGmKVr7aaSVSwhtvQFk19N51LJYVRrpxD0NKV1KVGKZInF3+L2MyuJsgqB1tnRyoHJDuRI9ZCt0xyHGlXsO9ngCEUys1JtmaxmiT/ruu7GskxALpidMIqL0x6e+TrKhGA4wcjd6Q1O9WCZIS8HOiCOVQLGHGWVD6G9x6tqBfB3RpZ8Sjc2cAuYLH3oJ++yqOP0YwazrkbU+v3yoSG82k0ig7zbV0ZtKBOVMFDy5RvPXSk/QcsDuRUJCMAIwdBS/dLyAI+x6j6D1sAGccW4SyQ7q6Lk4zo8n7kyuXYu2Ar1XferH3CME3p6zjt9/W0b59ZvwZL730LRWbvuLKiwTOmtovooB7n9MqRa9d9yEaCeHzm/EotJQaGNI04hFsrRBJ7SwbwpNxIlwLIMEgLucKtzu8BozUwFAKqVxl10ux4l0la39u4UbycYFixIKWblqQlCS2foinCP1uTnpu7C+PBBi/N6iIkXC2Y864pZ1x6XN4+XOYcIzi/H3g7TmCfhnglLjFgm4pgKN0hx5nIxw1AFbdLujugwEHK55ZAbJgG2aN7foYFR6rkY5OeS2IXa+ZpFNzOGeioKaqgk/ff4NhuwmWPCx46WaB2gA3PKpo0aUHt8/bDeXp0iGUZxdZO7WJgc3OGhI4abpk4VMr41d++y3I38+5jX/fbaPWpEdYeRi+WgU+M0CrNt0xfSZSaoUq5nvErYjPSImHuHTG0MqScAOIsRXdMEX8dWLBPSPuXJuYPhPD7/o0AQOfX+KLUTrPYXoOw2fGxQLpgjXmoOPSKpGUPycabRHaymgrYL0CaiRk2j8LnGJDN0RQnuZtrjb/+jdwwlmK7+8RZAYbCE0HfgjBoLMVF8yAS44StVvexKhTOI0/UZevYadYkvrspAO/mdD5aMVFpwqunUU8kzZSAP32y+OrLw7HtCvcmIYbHY/HOIIpfkc4PT9qRAk7mgPjTu/HW2/NYP26EP37z+b5u6Ps1aOO7aMdWKug+yiFL7OAmZe+iRAqTqF8/kSOky/TjE9cwzSQPu0Mx7m+J+Uk1QIkJTJ6ujrWzqtU9f8ubrAwnicZo1KxnDnSJJcK6Nw8sNN9EAE3AMe3gkhmz65akkQ42hx4FSkbPvgVjjhd8e1dguwIOLLhy2GvXFh1p2DM5YrN5YpbLxU4JWkc7lR/ItViqDqu1wMMIxcufgKeX6lY8axgWE8XHAJkHpw+W3HxJUMxqUnQKGUlfA/H8uwdntheWRq1c58a04SYYWjX/GtOPnkh3375Ma89EGFYj9qqlVeT/OJ7HXdp364XmVl5hMMVmr7ELEXMirgpJt4IdmL7jxSaFW9aJ7zBcE9sIlEvLpTwBAlF3SY9KXnYjboL4anO9VKq7bcDO+CDHKXA7APQp5/AKaN2KZrSisjkAxXv3eV25vPSnYYsiDa09cO//yEYcp7CLxRzz3A1/HQqlFM7rlEnkOoLg+bD+KuhWXv45i2BvRGc6sR3Xl0JT7wiWPBsF+zyLbX2FI8dyo5gR6KAhe2LYjvw319g1w7JGzY1arjHhiMPcJh25ltUrxE4a+oGBwA58MIr+ubg3Q8jGq7Rzq6rTsXpkeGRdGOOsXevizTlALH/xwN/QiT7E9557a7/attX61TE0Fi9lrcTIIOBlYA9GGBQL82oRGrQKhcOnaHYe4igZ76rJtkeP6GBNU0APQvghUsF+16sGD0MDhmq22qli2PUCRJVv1Meo4W/VML4CxVnnyk442DtF3n3ehEC3vlEse++HRKydqz9v20hAw74HJ78dyXLP6rGjkRR4SgDdlUM7QW7ddu5LAtg4mjYta9g82+wNfeNZvDCa/oH2XXQBKxoFabpiytD2s9IFCAZceuRMBRbm5jJ9yWbgqSuIypRWrA1HTXBt70Vn40TTNtOgBwNXCdgXB/DgE5t9MZDqZP9nZXw3gdQ/Kbu0IfpWSqkS8saCBInCnsXwZXHCSadpvhxsaBrdixFIQ0YnDooVj1/V0j4ajMMnaX48EXBrnlaOEjXReS7tTCwf4FuRxRzzB0L22dz2wMbuPyadVx0uuDWk3Ttu4pqiVYG3ISBnTycKnjzAb0tQu3MRs9nVlC2HjaVQUGL9mRmZlPtBONSqbYaOhtWxvOuNNXCkxzYsEVb1A0eQbLFqesVpGBnRpa3U8U6Hjg2A+jsN6FHm9p9YmUAbnlc8foDQqtJPhcgscPwXGvguxCZIF1+ecBpiho8iqmdPm0krbybZjWSmfDMl3DkPYpPXhAMKKxtGb2PD0d0HXgsrmH4bN75rIxRB39N+Jd1bH5OcME4sDeDKtGqmsygzrY6OyOtJseCgpytPMwHT77qgsqxqanZElegpEutRCxzN1aCLEXKPG98rujNzk137OyxnQB5XMBmP9De79cWRKWoISUWrF4HvVuAY6aAI/XweazL1t6wAa9+D5c8qK3GqtVw3p3akU6SbNNJuVuxHNIPC96FWY8pls0X9GxRfyRfCChsDt/9WAHSQVkR3li+hSOnf8MrFweZNU4gqrRuIUBznAx+922L1DZspuUo+PEHxZSDBOVb1nPbdRMJZGQiJPEAoBFLUpRueofA46D/CfZW+f0Ash6ws4H2GX4obJXyogVwygVw4iRBINMDAF894DC3bk2UAz9VwMQ5CiklU6bfTE5uK+55UnHHs25gyKlDxVL1gMO1HPe/Dw+uUHz/b0HBNq7w/ToLPl9ZDAHFd+uDnDPjc765U9Ei6Pn7PqDNdk5upQu4pGKn5m+WVMB+YwWPXgw9u0Bp8a98+ekrIFXC33DjHFKmUa7+LBsQ7XyANBdwlQOqA5A9qJeAlB11N22C5R8rTj9Gd7WL0ykjBRCxa+msSppJbDSHKTcoojYMHDKZ7ruM5MgT78I0/Zw3T/HpepfXp0sjqUcUkAG47x3FHUsUL98s8EW2TVlSCob0hm9+qGbz2goGj/yEV+6AfLc5C477+VrWH4hMS83drcSNQvi6ClYFQbbceROhIAvGdARfGBZcoh3m5x+bQ/OW7RBCJdSqmGMuk2vzRZMFiY1YCrUzAmDvPUlkxrrjxWXw6L0kUoDSgaQ+q2LUplwyACfeAZ/+BO3a9+WQqdfgKJtWrbux935nEY7CmNMUJbb7oepy0FO/ABOe/gzuegv+85Qgu6GyRQAm7yPYc9wnPDg3SuciT+zLAjrS4OxR5YPXv4ZDZoO/q+KwUxXTz1MMPEjx3o9pdqFtBD/FsPRXrhwY0Q9OPgRqqst5+sFLycrJ13JvrAmbG28QqZ1MmgDiaoF6jAXYe28SuU6urxrJgL26piTCiTQWZFt8EzeH59GP4aHXFXl5rTn6pPsIVm/RarEdYfc9p7PHmBOoqIHR5ygqI6RPYU/98BK+LoXzn1C8fq/ACG1HqXsZ/GM2RMJlTBrmsTwO0KVh8R7phw0C+h2mOPAExZjhit/egU8eyWPpTYLHboKDTlUUO6TPJNjekRKXckrhulN155Rlrz7ApvU/JGfFStz68z/iJhH/c4DE+JQaCTCkl2dSKBD50D4HpF3PX6zPD/ElWxjpg98EnHSrwlZw4KGXIt2WMzF50bZCjNlvBm2KerNqDcx+XOkdvOx64h0KVpXBmIsVr94raBnYPorvKBjUE86aJsiI+S1t3Pfv38YfwdApY3Megh57KibvB9aa9sw8ewIt9riMwPCL8O15LX177cJpR8DMOQqR1Vg6MImKSs93lW/DtHECy4rw4dJntMWIxzxEY4Ya/tBjOz7iAwJOawWRjYN7CT5+EVSZZ3JJiETAb27jymV7fiQ75XAgpKDL8YpNZXDQ5MsYOPRQbLc8VcSXMP0xDMPHVRcMBGD2FLj+SIFTlYbiOLr7x+AzFA/eL+iXv4PRbKFzm+JtdU1gE9Bi6/RKKPipGvY4XHHIOLj6zGxaD5yGk9lM7+cRT18WEKzA2PAgHUYFWfWC2Fb81eNEeYQE7/fv3t4chtaH6w/w2DtlBGsq8Gf4de6V6eZBSfnnctAbmIslG/7yJyiIjgAYOAQory3x+reVIxtprIjXemTAjPkaHL367sNuQye7rSm106gbl7lRXVPgqAjnXPQyUhrc9Cy89kX6FUBmwtFzYcIkQd/cRkj1UB5w4E6wltsADgOe/QL2PkLxyLwM7rtvX1qOuggnp0Dzv1gDbisMkTIdRPFlM264oKyiES2I10fzsIFWzeCcyfq/9113Otl5+fx/G9sp86p9AEb1b4SMbZmeasl8WPCh4qF/QyAjm8OPugHLjrgSo4z3YzI8aRCmz0dB6/YccOgFWDZMvlZREUjeklvZsGA5rA0prjxN/x2Z5U6SxlwE6/E9HFsnOU6crVj4ouKLl1uy/7TTsHOHgap0LWpYN/0OlUGoRO+uFS6FSGtGj1RURxrhPXobqqkUv02BUw5XTBfkZsC7ry1k09qfPe15VO0kxCaAAHTMAIZmZaC6tU/OTdqhYSSrWF+sgRPmgGGYzLz4LSw7orM/hdvRzxAIN7vU9LmNw3wCIRzGHnQWA4dNpCYMfU9XBH2JiRDJhrnPKR66TXD7QjhzDpx6BSz5GYL2zhdkBBD2w74nKXbrA0/dUUhevxOxDUOXTUYqILwZwiUQKnbBsRmCJRAsxhGZ9O0GIacRwLG1NBwFWUH42xhdBfjuq0/i2N5S2CYLkjIOBNY2A7pnZiE6tWz8d6MkyBYw9WL97e9/8IUe3d2taHOthunWSBu+WPGM7nJRVbWJw6ZfTZfuQ1lbKpixQNMqmQOzH1b06CGYME3Rryf8/Vg4fprghjsVQ6crys0GxiwaMictCGbq4q+/nwRXzhyIXTgNRBWEyyG4GUKlECyNn0VoMyJ+3oywKsnOgXBjTU6VAhivCqf0RlXTRwuU4/DWi4+QnZPhdkmErZVr/D8EyCsKnEFAq5b50LFd478hoxkcfRl89zO07ziAEXse60rtMVolXJ9DJqrR/IZbyKP/7/ObSJ/B9Bl34vMFePANxf3v6Ne/5xUoK1N88XqA0T2K6Nq5H8M6F/LaI1lcPAP67KNYZzQ+aRBAJAcGjFf86wo4eFBn7KKREC3V1iHkgiK0GREqdUERA8vmBGhCJZjC0PlfjeV7pEvL8VCvvXaDroVQWryOz5a/pluuOiRVOKm/qDnZnnnwOdD/4XmC6WMauesIcOdiOOtyRbNmbZkx+98ox4qnNcSLdnxumxmfEb/m7Qlr2wrbcsARbC5ex42X7Y9jW9w2A868E95b1Izh46dBjseTDpVjlP/I9ysXc+b1ggXXQltZT6JiQxZpG8IB6DtBcfWFcNTEftgtB+gOJ25fMOGEE/3CYrvxpu7660RAOWyuUtgmtA7sgNWwa6tWcbnXSr4uHfi8FAaeo8jJb8HDS9dSXVmeVDBV37YQ/59UrHFA/25FMP1wanXj21GkbrDgout0ntWEwy7DtiJxcMR9DpdaGf6E9dANxQzMDBMjdttvIAxFs+atmHjEJYDgzDthyljByMn7Qnau3hlXRfQRyMRuPYBuo07luRsNLr4JZPPGs4pTLlBcfA4cNbYddn43qNkEoRJtLWIWIrw5bkUIlbhWowTCZbpU1+0hlhuAZjtiQerwN7zUyns4DvQtgv6doKp8MyuWPJ/Umkf9hZ2RhgAkE7jelHDLVaJWJ/AdXmVzYdQRiqogDBk+la67jNR7QiDi7VxitMrwGbpQ3y/dTt66NtrvT9w2Y8X/fpN9JpyGNLQpuHNuPo7cNbl5nR0GqwailYgMk+ze07n5gmzufRHCO1izIQw45CzF0D5w4uROOC36Iqo3aX8iDoaEz0Fws+uLlOsdgdPU5PpNCIhGAAjp1atat4V2k6btreXs5W8+QyQc0X2uYj3//qIgaQhApgG79e4B4wc2srJnwMmXKn75FVq33YWJU6/BigTdFve6B6swDKRhYPiE7sHql4mmZX4Dw2/GnfW4ZfFpQK399QtsO8qeQyC/856oaKkLiBqIVrtH7HYVjrLI7zOJE/ZSOxQjEcCij2HYIJhzej4EWkPNRg8gNkNwS8LPCG9xQbH11qtqR8BRXzFZOvkXrVZOHar3k1/57usE/ALHchJ7gMSaM/zFgLKtABkIzAd4ZYHAaMRm61LCG1/BQ8/pyrCp02+msnxTcr1zjFaZQrd5cdtdGj6pW8W4gDH8sZaYGhzaefex6rv3EcDoYUCgCKKVGhBWlXuudo9kwMiO+5Ll377KJmnCez/D8g8VF03PwTGKXGvhHsHNCVBEK9393hXK1nEZubNqRlJjHqkKVipoPKNLOxg/UFBTXc77b76IYfh0A2r38W7bhb8USLblZ8gAHgN46FZBe1lHy5jtWWEVrA/B+GO1Y330CXeTX9AWw9DZcNJtVhZzxHX/JNdimLFGxS71chsJ6EO6FsQELDauW4WUMGRQAYbjgiNao51kqwYiNTjB6sR1S1sSpETldtou07GpAhYvV9x8iU9v8hcqdi2Gay3ioEhQKKXAKoCzboQVa3ZCXVV9nSTT+B6pAHEq4Naz9O17rzuHnPxmiX678Jf0RbZFozkT6DNkV5i6R+OBQypYG4YRU/WXOvbAmXTrPZJIuDreFCDeczXWb9Xnxj1M12K414Xhbt4CONJBSokFGD4wfJJN639GSujRNQ8VLItHgCUOtgn/uv17vl+1hSEDWzDjxI6I6oi7ZbANue30xI6UbXMUUZrw5ldw+XESuzwTweZE4+o0TfhlBhRXwR1PQW5YcVg/wfHnKL55X+jatJ1lQbyggbrTTjzA75QBe/SF5V9HeW/xE/Qfvl+8x640RCLC/v/EgvQD5gK8NF/gbyzLARQb0HOcYs166NFrT0bsdRyRSA3CiOVYxSyHp7W939OJz+3MF9vhSJeG4m6TJuM7n2blBijZsAZDwi6dfB4KVcWWykradH0VWx7HDfO+58c1E+jY+12qRQQZdSlXaAvkFhHfHHAbRjgIhw8Hny0Rlt6wM9GhofbsWbhccciJinP3hZn7C8b0gpMPE6z7rZF/7brq9NNZjjp+azsIJ+2jA7bvvfY00UjY3Rgntg8hfylfpD6AFACvACy4UdAm0AhRU6VznxZ/Dt33UlSHYcDgiRx76gNEI6F4EU4sGJhwyo34hNdnnWIiYmWgAk+lG4maaSnwBQwqy0uQEnw5CmGHENEaDCPEqed9y133PMWsiy+lxGnGnOvmcd3c+fQb/hnF1VWocLXbCbEGfLnb/AX4TDAd6t6S2m3F/1M53PQcWL8Klt8jyFWAHxwBZx6kN8/ZKdYj1dN36lCw0r11Afv1hYCp+Oaz5QglcCw7ufH0dofY/lwAmSUE7YcNhCNGNk7MQ2bC+XMVE05WVFRDn377cdDkOVRVlcbrnGN+hzSk618k1KgYvZKx5mWG1Fm0sUCVSCQyxjZPcRybaCRM50KhOz9GqxGRan7bXMHajbux5977sqESpOHjv2WCMeMP5Y67nuHIU9ZgtAhpaxOpBtPfaFVK0oFVFjz2JJwzGqYOB6cSlJ94NrMhoXurnWA90vkedQGojlFUCGP6QMWWYr79Ymlc5nVUcu/Av4IVqQsgnYGLlYKHrhdkmDtoNTLg22I46FzFDffrNvVjD5jJUSfdje1EtGIjEpPakLF95TQ4pAcksc5+3tYzMlYCGjvcwh4pBJGILnds2wpd7mjVILJCXHLZGqZMmYiR1RxTNwXHZ8Bv4Rx222Nf2nadzvmXrUeKGteRr9LVWztqQf3w6PvwzMMwZxoYyu0plnqYjZw4WReNqkv2rW9UwLwz9JtbcOMlNGveAsd2Eht0xoMj4i8LkPsB5s0R9Gq9A465rQuTbn4adp+iePlNyMzK45yL3mTYyCOprChGyoTPEWthL91YhxG3HFq6NdxgYay9vfBYjqQD3K2KcetHIDsTHRCMVIOq5skXq9hvv7HE+re6O3WRYcDq6gzmL7ibxcu68vGPlahwjRvJtnYMHC3h2iehUyZccLD7crHKw1SA+Gk8GWtrsY+tqFe1jJGAns1hRE9ByYbVfPbhW3o32vi2zsqjzKm/HEDaA8OL2sJxE7aPWklAZsPy1TD4YMXMKxURK4M99jqBi678GJ8/A0c5SCn01lixJMRYAmJsJ6OYauV36z583i1/STQrRunOG0KR2CJYE32ldLKYaaKdZaua9WuqAOjTp7fuJyF13MFwLYkUgk/XWTz26JOcf52J0XzH0laVozequ+RWxe7tYPQuujFDXYVi29ojrEEASUehnIaDI772VcIJ47XPuOSFBTr+4YBja6qlUH+J6Ho68rQr4B/SF/Ia6mspLXGWOHDw0YrPvhGEwtCuw64cceyt+P3Z1NSUIqURB4cR8xlcyVa6eVZJQUDTTGwz7G7ikujkndzuUniURp3DpT9iNIq7BZqieJ1NUbsi3QFVCt13ChckjgZKjfKTW9gbx9yLe59dwikH6AZwDZ6bCoxOMPVExTXHCbo3dxlMzELEOr7InchI6op/bKv1EAn3S3keM6oT5GYovvzPMj5e9jRd+/SmS6+RZOf5KC0p+0vQrHQAyQZkTLrbFh4sAJENP62FB/4N196qv8G8/NaM2f949tz3NMrLN8Trx2MT3Ij5EPFAn0h2yF2aJUw3HuI68cpd6RNbatXdJj8Q0Cmv1TXEpdaKSkWnzl3AtR6GnQBHbGcin4QSK5O5N17PuKgGYWEAACAASURBVDGDOWofQeZ2TEzbhFPPUVx/iqBzvgsOIwUcO3MOqa0cKbRLxnb4UlBZDeU1UBNVlFs6L8123JCODYFMCJhQXLqR2644IzGBcppx5pW3MHjPifFkxj9r36x0m2iXg3LWl2EEI5BVT0q1ssHIg1+3wNkXKN7+GCoq9X0HHHIh/QdNxDACVJSt1+qSSHTFiBVBydhed26EPLFzUEK1Mk3D3a3Im1oNW02vFhAI6D5em7YQ7wJYGoSWrQoTdNAFhxQu3bLd/Xtt6NpvEAdOnMqyz55i/wENaM6sQLaBc69SXH6UoEMzzd1/F4tRl/VIZzkctyS5AMIV8MS7ijc/hne/1VWLmZkGRe0L6dC5Nc2bF+DzZZCdk4fP78cIwoyZGjTBYCk33viyXoyqynh38bMM+z/2zjvcqSr/+p99SpLbK70jTVBA6QgCoigq9oaKBbsOKs7Yf/ZR7B0FdVCwVxwboNIFBAtNQIo06dzeU87e7x/7nCS3UoQZX8bzPOcmN8lNcpO99revddw5hIMVMQ7f//8BkiAgshjCJb/8QsbmEHRMdINJj+3PAPxQUAi/5sJTY+Cjz7TFSEpMoU/P3txx4+Ms3OSgnDBSRcAw3aAa13KImOXwpLh8Rqwg6FXK7Zg4vRF1rajRtaoNIZbPj2GYbNrhaJJaoCICCQkJUQtieODwThcwlgHLt4Z56MEHuW30+5zcY+/nXwwbxr0Jd54jaJJcxXL8J9dLTY2ICgxDsTMXNleY/LwxlRlL0siTmfTu3YoL7m7JQx2a0ap1fSDR/aMwmpwo6F7XnKqbNpXwyCNf8MorMyq9rM9n8+Osd2nVoQfJafUJJCYTDoX+v7MkVQByKTAuF/hnRYinBp6vePcZweBBbgAZhHk/wKtvKuYthfUbo4wgzlWX3moO6Hc6mekNUDKMkkVgGBhol0h4s+QiRp+vC4FGNEsVm+/Q7eym5QXmrmpqNbXSuj9sQwgMI0xCUgqhUIH+bt14RLqpOS+DZbiulQcWQ3gKDTbCn8qaDUfhpC5B7N677JITgpH9ddFQGv8FYFBz8U+EFUXF8O8NXUlp25/WnZtz0lDF5Q3BZ2ejSb3qoTmLEuN8Qe8JvamqMA8++AD33fcGAMnJFgkJPnbvLgNg1pcfMevLj/AnJNGkZWsGnz6cC0fdQc6OIpSS/78C5AKgvgkPPi0wjsjJkZcdP0Jvuwk+KK+U0RLlAjvXR5MZg7vdN+PiC45/Iy83n1AohG3qEVmEcuMFN+ZwV6JuITFivVW2p79txTpyvXqH4Wnfibj1tZc+rRCEI4LktHQKdhWQXwTplnYJCgoLtAURca5VnCUxDH1bwIQcowF9+/ZhwvtLuGro3lkRYejaotoHYAi0ijZSyyqEIxCR4DMhsD8lGC/lKoVuYfBlQYdhBBr35BIBWgI4BegAHK5dg2rBS1UxRwcwePHFCSxfnseSJXPo0qUhsBpPqjgYLGbWrF956625zJ69mt/XreLVR+/i9afu58HXPqJV+574/Ak4B1HE9CAA5DYB84RJsmjJrf4Ccm8s4J1pDhX/BxzhgiPPJPUnPy3n+2m3OZEj8nzUk+UVhdkRJxyNMQzhpW913GEI3XgYrzVhWLHJQNsLyKOC9LFWEk8YkrigfF/MdDioyMiuT96OjWzeoaXUhIDcnFyvXBK1HLE0r7tvusApDcGISy/i2kte4qqzBRTu5Yube4cKQ+qNe+N6mPSuYt5C2JIHx/eAtkcKBnfWdYd9Tp9VgEjrCE1PRaQ0hoB2K/20BFrowKNS6qEq07dT43WlHM4//zj+9reL0BreDtAW2AKsxe83OPHENpx4YkeKiopZujSXp5/+kk8/XcRdl55Gx6N7cPk/HqJF2577pV3+XwLITgGpZLJLCHwk0BYfd37Tlnu/3MTr6bArJZXjZCkrU4P8nm5h+BTCdij0KVKIqjIKYnrapl7Y8ZkqL51r2TEp4ej8huXNnLtulSfSImJSvvvsZUQcMrOzWSuhIF9XeWwLtm7ZGnOxiAOGEbMehtI6f4aAXv36sj3PpDhPknSAxG8ME0orYP5muOtSxZJ1cGx3uPEqwenHu95MwT4KfSoFZgBhtUAceR0k1HcthR/oBbSvJc1VEygiNVoQIST16gWAnCr3ZQKNgB3ubSFSUxPo2zebfv2u58svT2PkyMdY+fMP3HrhSXy8ZCvhUEDHln9+gEgFDY16+CnBEQ5K2PiNjTycpiAgEFYBXyeCmWBhGFKH3G62VVcevDRsdKFHh55iI7PxPFbRSnm0CdF0g3FDN88eAIp90/aRkt4IIQTrcuBYAYkBQV5+btSticYecXUQwwOOoUvHheVw8ilnsmL9R/RqK/7Ynue230z9CW55VJGRBg/8Q6vnZtfT61nu2MeYRQAhhag3ANHoeEhuiqbXb+S6UA1rKa3XZS329HtN1+trVBNk3rxNjB8/m5ycEnbsKGTnjjxKS2LFpLKSEiw78KdOA1uxj7cTsIkwiUIhhYEQJiZhhGFiGArHNDAN7dBGxXXd0rVSphnHV+UW9wxXUD4qIG+b0alA0x2AMmwjWkWPikUasTaSGDj2p+ikMC2LzHpZOFKwc4dEOYJ6SVBSUq7jJdvGNESsHlLVkkiFJQS7CsKcMORklm/6iJ5t/+Cnngj3vQEPPqX411OCkadpiTZh6KEk9qNAK0QadL4TkdzAjQUy0FqSVhVQOLVcRvYSAE4VcNV0fyawlR49DuOBBz7jm2+W6RxpUjKmL4kO7Y5m+Kjb8fvrua0o4k8LkjgL0hP4mjQUJVQIixQE5YZeHkpILMARQtdUlb6UEgxlWWbEsgxsn3ajfD7THWhy+6YstzPXdOsathuke5kqd4bDMOJjDhUHDtjvFJCCpq27oJRkozu3lJqq79q2bRstWrSouR4C+E1JQ385BfkFBPzZ9OzZm7de1nqIYj+CZkNBrgVDL1b8vlWwcorg8EZabmD/GCoVOCCaX4xoOAiMYncHPxpoQHV+H/kHru8NMOKZsA18vhBTpoxmyJBnmDFjGZ269eehf31JJBSivKwCKSN6E1TKVbndhwTMf+gwKucEHaTbyqmIILDcrhoHQURJhHQ01qXCiQCOQkUsU4V9fkuzivgs/H6rEh2Pvs/G57c0uZvfdu/XVsXwOHYN4Q5MuVmwqOX4A96MktRr3BSALb+DsqBxPTBN2LRpU1QMxjTisldCl0ySK7bTv9dRHN6mOfO/nkx6egZrt7JfApxCwBYHGvVUmMD2n7WktQz9gfSvSMXo8gyi8TFgBIFzgaFo5uwQWriltrO8yvXyGq7Hn8Eq95fX8TwxN8o0g3z66U00aJDG0gUz+GHmFArzigiHwprGVMrYuO6fsDUlDiCFQACFDwu/chAoTGVgSAfTAdMRqIgBEZBhgYiACApkMOC3Qz6/iS9gE0iwCCRY2AELX8DE9lvYAVNzVfk1Nahpu+6VGWNmFx57CbGAfP/cqupH81adAFizVkAKZGSB3xasXr06rmbiulVuQTPNF2Hy5I8pLi5BSsnw4cP5/ffNlBakYu6jNodhwIod0Lq/4oxTBLPeEDjb2K9uXUNAhaVYVdgDo+sYSLDQ7vFlbv2ivIZFW1HL4i+vAoDyOCDsCTjBWp7HO2PBUUpKhFGjTiQcDjLnqw+QjtSnVHoSUUqXxtTj/FV/RoAsAZpTDsrAUQJkOUqaSGno0kEEVFhihcAIqeinJIIJSXZFIMEmkGASSLBISLSxAxZWwMIXsFx2EZeiJ26u3LSM6OSfMDzh+PhU7oHZTVLSs/AnJLFpi7YcRGDYsYIfflxCJBKJ7vAiriyWmWTx9jvvMnv2bB566CEAbrjhBix/a0ja+3cmBGwNw5HDFCMvFnzwONih/XOphAkLNitGPtqTdgOv1aV+zgS6ua5NbQu6fA/WoyIOHOV7sDZVrUjV1/Aq7ZWPW245CYDpn71FcmoiTthBOSoGEs96xMUkfyaAKNgkYIsKkYeNqQSW9GPIEEQUMmygwgozJJBBBeWgKkCVA2Vpaf7yhEQNDH1p4fO7oPDHg8PCtKyo5TA8YAiBwK11HAQfVMoQ7Y/sRUQp1m0BymDYUMWatZspKSmJxsSm4Z0KS8DmzZtJTU3jiiuvJC27BT/+vIKF36+JysLt1WsbcMpIxWEt4dmbtKTAvuLeEKACcN2zindmnsp779+EbaUCZ6F7rmta7DXdVpc1qajDulTU8RpB9/RSwjUfCQmJnHjikSjpsHrpTzhhiRNxkBGpCSOlAqn+dBWRuH2snoIwW0iXKSQoRYUSBKVFYtjCDisiIUG4wkBVCGSFwChTyDKFKMusl1IWSDBISPSRkOgjkGhj+yxsf6xS7qkSGSZRjQ+8SyGiQfmBBocQUFpSTvdjTwDg85l6gZ5xAvy6cjWhUKw9IFooRFFaXkFFRQhhWEhHcljrdpDckp35ZajI3ldkTAUvPCjYth125e2fUSwC2g1VlIljef7509C98se5T1a6FwAorwNAdT22vJbfg26MUzcoKh/FnHPOsQAsnDkNywro2RFHopSsTGP6J4pF4gAyBehNiN3sRsoUkKX4IwbFThAVNrGC4A+iVdHKFaocRJlClTVpnF4eSDQJJJj4AxZ+N94w48FhxihEPaIFsbddufuFjNhnHApW0GvQqQBMmwqkQZIPku115OcXVHKzPCsiBJSUlpGfXwhI/D4HAo2AAAVle//1SQn9j4BbrxVM/WGfyFEQQNiG469QDDzxBCZNvAbNsTxEm8Fad/ja3KTa4oyKPQDIO0NUU/zcq/9C85P16aMzhr+vX4lhmDgRFePVktUZg/4MXlYcQFYqWKygldrCdmVhy1RsJ4gd8REKgxOOEAoqjAoTWWFilptYFRZmebsODYOmZeDzW/jdwFxXzr1AnDgGksoNh5WBcoAREqXNNMhu1ADbF+DbH/V0oJMDQ06Ejz/+OJZOjotBEgMBQsEgv23aiVJQlPs7wvQDaRSV7Nv+Jovh1vPh2dcUBf59iF+SoPdwRct2xzB+/NmuO9UJyNuD+1RbBmpP7lNtoNgzFWptx4YNFbz33jLAJuCSGwTLS5EOSMfRlzJu+lDFo0P9mQACcB+QpiCd30CZbJWp+JwggUgYK+THDglUMIyoCBEOhghWhAgFW3TIcgESayGxLA0MwwNFPDAOMlV+NDXsvq4hoLRIctjhXREClqzQ7ein9IQXX3wO0zSjQaFh6J4xgEbND+PyUa/y8tgXWLFqrc7JigxKi/fdA0j2wb/HCQZfoFhXuBcJLAXjJyvad+3HBx9ej2U1cOsbZXWkYctryTCV72N2y7MUf3yBtmqVzfDhz/Hbb/ns3KmHhVJSs4i4QToqZj2Ugj9bX5ZR/Wu5ExggK9gi19JZllPipFHo+DCdCCoShrDEF5IYIYUv5JAQSkn1O6an+GTpoh9uTSMKDq9PK3oeTB9TVPtdSodW7bsgBCxaob+E/p0FObt3Mnv27Oj78WbUQ47khMGD2FVh8chz77kBRQpYiRSW7jvjiJTQJh2mjhNcd49ih1VF+LPSg8FoBEs29OXNSSNcl6adazmqWg3P1SqLA0l5HZmm+LM0LoYJHzBQ1HRccMFYvvtOM+G17tCDitJSzQ4vdR9+vOFQf2KAAFuUJnI/WUkWspUElYffsbCdNBIjqVRE0sh3UiiPpFASyaI0kpBgS0MQFVTB7eg1KjGN/BcrpEJrHTZsdhhCmCz7FSIRSMuEswbD8y+MIxgMxuIQAeXBCOefewbIMKLZOWAlQVZ3IEJJqY6TjWRdUS8Mwo4ifRaFtHUykqufIhHqpcK37wueGwfLd9VsiYRQ/L6pG488ez227QAdayn8BavEFXVlnpwqL+a1lxzcQ7oDQz/+uJE773wVgI6dBxAMleNyzcUF5n++oxbGq20KzhFwh3Towi7mi12slzZHYbMVqHATsooIKcpOtKTQctmaxqdSjCFq2dX/c+DwSiqt2nVBAT8t1xV1VQhPPyRoO3AuO3ftpHmz5tFdw7J9dGjflgbJOexK6Y84cgwYfgjnEVIw8WvFmx/Dj6v1h5iZlIYC8ksLUQZ0bAENMqGwWBAMKnw2NKoPjRpBjy4w6nqDxukKcrRScLQuIhSkHkuzdheDKELPaUSorbN2z9cDbNtWxgcfzGThwl/ZujWf1NQEevVqz6hRJ5OeLlxwHZzvJz+/vJIV9fkTSc9sRjBcpFO7f45QY18BAlCm4F73U5uswFFhtoswzagsrFyGzycUGC5lv07RebPn/71/XiEAGUfJcVinrhjC4MelDuEAWGXQOBWO6rCVmTNmceGFw7FtO9qTldmgCf3aBfl45S+I1Paoiq0ItvC30e3p3aYvo/qcyQlXDSYxIzHWfhKGioJyvv11Bm/Pf4/ft/1AvxOzGDCgH2lpNrtztjJ/+U6eePN3kuxSTjwxj6FdC+nURM/Ck9QL0f4adIt6kvsVlbPvXbawatVu7rrrQz7//EdSUhIoLCjFBEy/xYwZv3LvvR8xevQwHnlkGIGAPCgg2bkzB4CGjdvTsEk7Bpw4kqLCHPwJvhiHr4eTP2GryT6+m2ZCd2rGr/pdSLmtvhBiR/wjP/opP1YY/W/AI/6DdyRORJGYlMJDN5zB4gXTeP1ZwWX99KzFF0vhnOsFpWUVWJaFEIJQBIKOIlicS6vWbSgJBmiQ6OOd0a/TLbE7aU3TdFjQwF3D8f9nObAB+A0KKwpZVbyamyddz1HHHc7LLz2n7+AXSktzycn1sW1rCXkbZ3LKgFRoPJTYrGy2zknXCISq8xrx9yk2by5k7tzttG1bj/r1/Tz5zBy+ev7f2EB541R+XjqWdes2M3nyIubP/5W5c+9DDz/98Y2pogJ8PoVhWLzzzs9cdNHL9Bt0OSed/Q89UGeDP6DbkeyA1nDx+vJML9tJ5VGHA7ni90WjcB9JRX9X8HsNPrP40xlJIaq0KwgoKynlhLPOZ/GCaTz6nOKy04G8NAZ2TaNe9jZuvPFGnn/+eW1FTJcGyDAZfNxAUgtSmTR6kl78fdBNsxXuOq46c54ENNa1vLRP0+i1vQdf3vk1Jz99EnqDCQCdSUraRVLSQlo0z4c+F7pPEoyLEQJx8cOeXKnKX0Hz5hlcdJEHrnS++mQOae5bXLOtiNLSPHr3zqR379MpKzuNcLgY2z4wlnvVqjJsO8gRR9Rn5sxVADRo3I6KslL8CX4MTE127Tkcf2KCOYP/gUOTySnWrZjHuIdvBcCX3Izy5LsxOt1Lao97+L97L+PVVyewdetWQqEQkWAQGSql34A+nNv0XCZdPElv6F3chNEm9EBdXtyZj+75LHYvlwHbQWQJ7n7nLu5/7P+A3ejaeI67OnoAfYFmrlvlHW7TWJ0tI3XVKLxiXgKvvTadnC35UXe/EfDav2ahpwzDJCZGDhA49E60du3v7NxZQmlpOT/8sBHL8pNVvxWmZVWqT8UyVqJKDeQvgPxHfUiFwLYdHr/1YooKcrn66sEsW/YECdlNwZ8MSK67bigNGgQYMWIExcXFfPbZZxzdrStXdLuSi7pdpC1CxF3fuS4gcqtcz4k7twGLtBH4/OfPmbHlW4ac0C0uNeulWIvchZ6OVrpzsx2Y7v1VaxT7lo4tLg5x9dUTaBW3BhsJwTczVhIOhw7CJ57KG2/M5Pff81i9upSlSzeSmJxOw8bt4U9Y5/jfBojrv2ZkZ3LPVadQVlLMiBEDGT9+hLvFx/VqUs5FF53Ad999R79+PVi69F2eeeY8tuSt161POXGAyI0DRDwwvNsLgRWgHMX2iu1cOe5yFsyZimnG1x68sywOMEGgueujybj07f7VKJRSPPXsdFqge8KiH4tS5G/OIT8/csC3o02b8pgyZSm7dpVz1llPAHDKWfdgGGbMBRUiVg+LNxliP0Pj/04W69AxIXOnvs/a5Ytp3boekyZdXQUcADavvDKXSZO+4Z13RjF8+LFAHpGI4vZ7JrCp+HZaWC20pfB6vOLjjvjrprveQyBSBafefQrvffo6WVkl7gMicacTd+nEASHBtRZ/hBZHUV6ewFMPfkRbpSp1T4WBwt9z2bWrjPr1Uw/YglTK5OKLXwTg9tsnAtCmwzEc2XUo5cF8LZcnYn14XvE4DimxVPdBA4lLP/WXBfGahB2+emcchmFwzz3noFRRtX/bcSK0bZvFjh0vMnx4V9cMCCzL5PVxz9H7772pMCpixsZLIHm1O88AlLjYC4G0Jf3u7c+wywcyaGAz1xK4FkPVdJbGLiml7rHFvZmVSaZf/wfIiMgaWwuTgI8/+ukA7ZEKSOHxx2cwf35sCK1x046cc9ETlJTm6pafOHEk4Q7/e6SAgrhui4MADulIUtPTqCjdwdLvv/0LIKDZEyORIhbPn01Sks1ll/WpMW1omjBoUAvXpYkAJrNnb6W8zE+PHudz3pXn8tKcl2plPq/2fRrwz2kP0+3kdtx/zzUgSzU1vKzQp6rtDIIKaa5XKUGasVPFn4aeCVZG9RN934svTmfNkg2VuEzi37IfeP/tWW7s80cPH08++RV33PE2UirOvfhxRlz9Kjfc+rlmzzTMqP6Lx3xjuBQghmHEmljjwXGA0ruGYaBUGCkLefjGc7nk2MP59Yfv/nKxADLrZXH3yBEAvPDClexda4Vk2rRN/LpqBwMGjEapch577AGOOLI7XVp0YfBhg7XlqO3ww93/vodVaj6fPPUYhLdr6TccV7Mwosm2lOtaeZfRBkujRnXZ6G1CVb8tficXipw8g/vufp+2UtX6Vn3A0nU7KS0tJSlpf10azXp9661v8uSTX2AYJhddMZ6WrXvgOEFKSnIwTNPVjXTHq71hOZfxxgNOlOBaVE7V75c9k4rElEQi4QqmvDeO76d/xrKF86P3d2j+VwwCQO6uTfwwcwoJCTaXXjow6jrtAVY8+8zLjBt7Jyg/QpUS8CumfPExhx/enRVPrqB9avta18uEH19n7o4vmfPdOxDcVjnG8ABSqY5RmyGvS05ZVQdQdOWn0b37FaQUldUZ0puuFZkxYxnDhnVg32Y8PNfUz3HH3c+cObrWcck1E2jStBPhSIV2p0Rs8YuoexVHImjGbvMsiOKPFQcdxyEpGT4Y9zBvPvcIIDENTZ17+/mCOy5U+DP/AgimaTDtg7cBGDFiILEIu+5j69YcNqzPoUXzriALXH1jh7bt6zNz1qece/m5TL99OvXsepVj6AC8MHssby0bx3czx0PZBrcAEw8QlzZHKJdp3qrlLanaQ4867xeMumkCZZtzaOM6jKoWnzqCLutMmbqYYcOOoG6zWP0Ih/0MHfo0c+asIiUlm4uufJV69VsRjgSjsYTHqxylnDXNKDiEp2JsesTmVOJB2xtycu9RpmmRlp7MD3OmMnfK+0yf/AGhoO4BO2MgnN9bcF4fPcogS0Bl7b29PGQBEgoGWfHzd9i2yQkndHRvTXCXRriGDAwIEeDTT7/h+IFHg5mkJdtQemEHI/TveRjPjb+Hpic2Y90La2lqN9UVe6l4ce5YXlnwDMuXfQRlO2NgUHGFPCH2EHyrmq3CHkGjrc2CxTn86/l/c6wb5tvufxovzFwIbIjrMvjxh437bD3CYZtOnW5n7dqdJCalc/Nd0wmHK1xwuMkRjzo2Sjcb04LxhulMUz8uGqgb8ZJ6Yi+shSQlJcCaFQt48PoRFOXuAiRKwugzBQ9cBoFyMPVAo9ZnsfYt8j40AaJAGA4b1/yKz2fRr18bZszYxG23vc+UKbdQr55VzeF48MFpXHbZsSxc+Bt9e/TV+gVOXN+T0lZg0KBOfL/gQy64/iyu6jmKYzv04/mpL7Ko6FuWLpyEKtwa1UqM9aHUstgPlPUQsPTXHPofcwMnxMUY3jsIu+9iM3Biy5Z83q4dHdPSIDGRjnOmkpdfQmZGYK/qKkIkMXz4S6xdu5NGTTpy5d/eIRQqiwkkGZrTzNOSjLJqWnoEWxMGulRPpqjCvcweyayVgrTMNHZsXsOimZ/z1XsT2bhmBQB9O8HZ/WHkIEF6Esgyd5cQVc7/dYAoFNIJs2PzBpo2TWfLljKGDHmUceOuqAEcinHjFhGJKFq0qMfmTfkMP6MehIOarjhqCdwYIlzBUe0ymf71i1x+7b3ceu8o/v6PS5l/ywtQXlB7ClbAngt9+xF3KEVpxOb0ix7iLM96xv2F91cFQKOUFN7u2RPKy6GiApSileUnLz9CZsbepM39vPTSLD7+eAGGYXLVje8TCpa5VsDTgYkF4R57ZjwoNG+zGU33em4YIr6OWPOH4PNbIEI8eN2Z/DBrKk4kgpSSHh3gnbsFzZPAcpuSZagWcNSdIa+GzkMTIAo2rVsKwCmndGfkyFfIzk5i5Mi+VXxtRXFxgJtvfo3168cD5aSm+SksDGpNdUe5lsOzICoKlICs4N1J/wS/H0ryoLgchBtTqFoshKrsElX6KuIthKqSQq70uMp/FJIWJ551B23WbiEtLu6oqp1TAvy9QwcNjKgfGqKTmUhubpA2rRP3uLXm5ZVx440TEIbBqNu+Ihwqc2ONuJgjjqNZU8t6SsWaH03YJpabzfKUiEUtsYeUisTkRMpKCvh18Rymf/o+c6d8AkBGEpw+CK49WdCrIyhPM9SsAxTeWauX+zfgxUPfgvgDCSz+bhYAc+asYNWq7YwZMxzDqLqDJ/DAAx8QDEr69buL9etfpmfPlqxaswUiQYgYceCQuAROsexTflHcWrUrd6bW5T6JKoFBXVZC1WZZFI6wGHrxPSR+t5jOQlAK+FVlbql4kPTJytLTWd5Qm+NwREoaa9bl0KtHxh7dq4kTFyIlHH7k8fgDSSiEu/vHFwEFhuWRlGtw2J5qcVQCww3SLUOPHRsx90oInab1BfwEEhQTnribaR9OpKSoAOkK7jx6neC6kyAx5AbexcSks2tzp+Iva7UglwLzhCYvOYQBH4Ge1AAAIABJREFUkpDkY8NqnXpctWq7m8k6Dl3NNirtD198sZD161/isccm06TJlVx88fEsW7QcwtJtf4q4mSwZ292EVcMCV3UYbLWHtEmcRdmb4FwAPj83P/Ayztffc7wQ5HlxhxDRVnLv6X4Fwj4fzS2r8ksIQauAnykrt6AlEmp3AaUUfPHFEpRStOswANMKIIQTBYdX14iXsjDj+Jctn4nhat57WS2BS1SuwDBNElIC5O/ezua1K5j52SS+nfxB9N89qj2c0xfuOE+T3spQHaCo6zTqMpTfCmirYPGhbkEMtmxYU+m2Jk3S3TpI5cWXkZFCq1YNePnlsxk5chCnnjqGxMR0CnNLSEtKdNeM96nuZ22irthjj7GJqgZEZVqcfuVDlE3+mtOEiMrjRC2GKxbvABuB3s2a0Ts7GymqTLAJQaJts/7XzcRmT2qEB1KmMGeOljHo1HUoSkW0BqXQ2SnDcAeebIFpa9Uwy7McPtMN0r1CoeFWuCWWbZPdIIXF82fw1vOPsm7FYooL86PW4tpTBKPPh0Y+SAmALHcbBvYXHNQFkGIBZys4XMAD6pAFSCSsSEnLZJe1GScSxrbNWrbnCFdcMRAoQQhBz55Z7Nr1JmefNYaVW4rp0zqxCtNbDQvZ606nrpFVFfe4P5DeFQJlmfz9sVcJT/6akUKwoyo4vL9wU7lbgZnduzN12zZvMKbSs2eYJrs35ewBICbffbeGSARatelFYlIaJaW5mCLWW+VJWkS5l31aXi+qN2kZ+Pw+EpICVFQUU5i3hbycbfw4eyqfv/0qkZAeFKuXCn0Ph9P6wq1n6cqhLNUrNaoUvC8gqHpfHUF6D9L5gcChHYMoBXm7C7nnpU9ISrI466hGhMNOnCcev30EufLKPlRmI9/ObbefxlNjXqHPa2OhIC/OXVKV0y0QlfmNPq8QVWJN6W3Clb8ZEReQC1ULOmTsOaUkbPsZcOEoms5awDlCUOQu66rg8E6fEDROScHvOByTlsaGigqyfL5Kr5BoCIryi/cQoCcxaZJu8Btw/LWUluZFe6h0KteMgcNvYdmGq0kpSElPIzUzmbKSAhbO+Iz530xm828rKCrIp6QwP/oKA7rCfRcJ2jWGbJ/Ofcgy923Zf8BK1ASQWv7VQooEbK6UFjlEs1gK20zAn5hGx6P7sfLn71i2bDudOwf2ou5g0L17Nit/W8K87xdzTIe2unGw1jRrTdml+MeZ1TNTex28u39rGORUhBk+cjTtZi/gBNet8hIyvhpAIt37GqakYEhJY7+fRjVYkCTTpHBncdxfihotyBtv6F6mw9r3JRgqwbZ9bmxhYVgghIMjKwgGywmFywkFg2zbspTli+az/Mc5FOXtij5bcgAaZkHbNnDhYMGFg3WwrQp0LyaGu++YtQTZtQXdVW/bE4iqebsN0aq/OYemBanEiiGgKL+EE868mJU/f8f48Z8zduxl7iPL6nwe04Svp93L0V2uY+W308j0mbECVuUoN25xVwGIElXii5qGgqpakCr3ubMSP63fwQVXX0nZ+q2c64Ij/rBrAYgEMgOBuAxxdRAnmCbh4rraTATLlv0GQLuOA8jP38bG376nvCyf8rJCigp3kJ+3BelECAVLKSstoqy0iHCootKzNM8SnHoMHN8TOjSARgmQnuomB0tcLXl7PyxEvKUw9gIodWSxghRQtcvikK2kA4RDQXoNOh3DvIGXXprB889fwSmnjOHDD0eRklJ30a5xY5ux4y7mbw8/ylv/fBgjHK5SvxCVFr9Srq6iVFG2+j2PmKo6unIFEUzenTmbux68k19WjGPEpU+y9t8/0VZUFxG14kDhvWquEKSbZp3OkxnVYZG1vsfp01djmgZrVs7m6QcHVfl7sEyBZSr8PkjyQ3Y2JCfAqUdBnyMFQ48GIwko0t3+wtb7h4y4C9bch91/f869rJxLkoQGiDhUASIqWRPDMAgFQ3TvfxI/zP6KSy4Zyzff/MKoUW/zxhuXukXD2vKrkrPPPoJ581dz0pXXMe1fExBlpbEaQhX3LALMWbKYwf0HQGFR7S5XVXDUmKcOsK2wkAtHXUOf/o1Z/ssY0tIKeXX8pXSc+yv3FpSRoKrXGX1V7FqhUrS17Tr7mhyl3DxEzasoEpHMn78Wx9EAOqatoP9R0KkVNEoEn+XS+PgEaTYk2pCdAv4kon0uskKPw3iDKGpvXKYDde7NZuo+ztF8+tVyMIeuCVEgDJszLvk7AO+8Mw8pFRMnzubFF79zl5TB9u0hHWZU+0TDPP3UpXTpncRpN1xNiWGDtMAxKw8zSRNbWTRufBiPTfgXwbDSK6GGx1U/LX3pmKBs8kIOL7z3EU2OPorZ8xYQCoVJSgoAggYNknjzzVE8IlWN+71wvRTbzWw5gM8w6lwnxZEIyfWSa1lNilAogV8WL2fsNYLIp4LvnoAxw+DCDjCwJfRtDn0aQ7f6cFgWNEoD23Tnw0Jug6AZdxp7cRl/1nVbTZfmnoqBtR+tUegBstToYjjESRv0l9ysdVeuu+uFWOYmOZ1RoyZw440fAimMHTudoUNfZubMDWj5ZCsubZvPE4+fwbkjWnL4kP5M+vYbqNcUwiY4tntaEDE5PLMhLbIbMfiGK/hiwQLIbgzKDyEDIrY+HSt2hk0IC0hvSBEWf3viUfpdeB6PvfScW89J5umnP6NPn4coKLCBICef3IE7nr2CfytFCjW3s/tckKQAIVl3p26O45DRPIvq3L3awZg8eTHndCvh+v4gCnXVWrrmSvh0zRTLDa7NOhZ31UW8t6dRx21VL//gEKKMRm+jD3ULEuMDFgJCoXK69j6NS296hhHXP831d0zE9ifywgtTOO64+znhhL7Mm7eK444bQ5MmV/LYY7PZulVQUiIIhyUQ4pIRR/PLr4/xxMQH6HlSL5YV7yYYDsecXCUgohjUuTvLV/zCw68/y1GDe/Lqt1+yDSi0JGWOorzCoTykKMag0DL5dPUvnDFyOA16H0GLI9rzy4pfGDZsGABnXjCGhk3a89NPG2jR4noKCwNAiJtv7E35SV2YoxQ10Vl5dbSmwNZQKEbSVkNO/IeCQgac0gPdCF/1SOHGUS9zenc3QrH2sHiNfVz0e7ICe/tc4uCvpD+aPaoP7Iy/7b9BPerNOCil+3lkROJEJJGQJBwMg7IIloUJBx22bVrN2+NHs2Praho0SKOwsJyKisrZnHbtGtK2bRNatMigY8dGNGyYQcuWDZg7dxNTp3xH7q4Ax/XoxwnHDKLP0d1JyqoHCT7adW3DA4/9k65duzB79lzWrF7NR+9/SKf2HSitCCKUwrIM0utl0advX7p07coxffsCUFpayrhx47jv3vs4/tR/0LvfCD7/+H6W/PAJrVrV58cf/0lmZoRQKMDg45+k19yVtBeimsvlOnl80KQJn3ftilU1DlEKEhJoN+tbpi68h9atEqssB8Hbby/h6pEvUTpZIAu8OY84n937/WAE1mLfinx7v+IVIvM2ROvH4mOQ34UQzfvyjD2fkITbpOd+HFJBelW60Si9jDsTHQ6GkDKClBGy6jVj9P99zusvXcOvy2dFnZX0zKYcM+AiZkwdx/r1+axblxNNnEpZHe0/LV3M468+D0BmcjJHduoKQrFw4SJOPeVUzj/vPBzH4dRTT+Wbb75hzJgxmrkxEiEcDhMOh7EsKyq/YJomaWlpGKZJQd5WIuEgp57xf+zeuZYNG5bTrdudbNjwEj5fPl9+MZojetzHNWu304DKA47emirOyyNi21iRKvP4ts2l8xdw+shjaN0qg6oThcFghAkTZpOQZLEtzyEjDX7PhZ+36tfp1AgOb+BO9AXdoaQDnXXaY2vIwTAW/wNpXuGWIWLioFSeiXbpZkKhcq4Y9QarV8xhzjevsWblbArytnDMcdfR85jLydm9gfyczRQW7qC4cCelpfkUFe6kvLQAKR2UO/lmWT7ycjaTV7iT2Qs1Y8b3CxZgmiahUAjLsujWrRuTJ09m3rx5HHHEEUg3NohXt/KO7OxsTNOgorwQKSMoJCNveJuJL1/Gxo0/M3Dg/Xz99d9JTXVYMOMOhgx9ks7LN3OGEOTHbbJlQGJZGTPz8hialhaTcQoEeHzlKnwndeCJRy+AaNnRO3xMnrycdu1KGXBsOwbfsZL2XRvRqFUG9bJspIQfVwoK54VZ/v1aWjcJ0acjXHCMoEFKDVmqg5mF+o/lRQ8BFyv6flw5YeVIHEfhhB2ckCQUihCucIgEI4SDEcIhByfkEAlHMM0AeblbkZEIqWkNkY6jF64QKOmgpA5iTcPCMG1XB0W4w1kR/AGdCQpWFPHyM2eTn7uZIUOGMG3aNPLzdUtFRkYGl112GU899VRMR6UGKzh//nzOOussWrY5htPPewjTtBFCEg6HeOXZ8ygs2MHVVw9h/PiLgTLKy330G/AIxT+v506pqIhbl7nAGMOg8PTTkcEgIb+fixd8T7BnYyZ/eDW2Xf0L2rChjHbtRrN8+UlkZ/v5/vtCevfOIBhUCGFgWT7GjV/No2OWkWI7nDMERp4l6FZfTwTsk1WoqSXkoIqP1epitejLM9Z8SiXcHXWxDs0gXcSq6drF0r1BWiZOz0V7wzy6A9XCkUFSU7NITW+AQmq3zBQIoTBNE8v2Ydk2GODIMOFIkHCknEgkiJQOZWUFlJXm4UjJNTd9QEZWc77++muGDBmCYRhIKcnNzeXJJ59k8+bNtWdSpKRJkyYIIQiHdPOgHks18Cckc9n1E0lISOWVV77muuveAnwkJIT4adHdjB57Oe8N7sxEpZimFMuVIqgUzR2HwbPmMPzHJfRfsZD+d53AF5/eXAM4DHbsCHP88Y/y7bfHU79+ElKa9O1bHyltUlMTmT49h8aN32f8E4t5+h8OO2cIxt4r6JbhduT80cD8v2c1VE2EdYemBXEHl5RSSEfhOBIZloTDEicUIRzUViQSlkRCEZywJBKRKEe6ssS4mt3ERF6qhsCqxnphVG2vvKyY8c+cRXl5ET169GDRokXk5eVVmp6r+rxKKVJSUrj77rt5+umnadayOxdc+gym7YvyRiklqSgv4oUxpxEKlXL55YOZMOFKNwslcByIRJL45ptVfPXlIrZu2QVC0bZtCy4YfixHH90Awyiv0Zn46ac8jj32bj799Dg6d07DMEz3vRoINzq3bT85OSFmzNjG22+tZNbsbZx7HDx7j6CxUcWC7E0f1X9886w9SO/JGHsRUsJdUQtyyANESYWU4EScOJA4hEMOkZAGiRN2cMIS6bin1G6a10LiPWkUMFUAoqqCRoFp2RQW7ODtf91A7q71dOvWjS+++IL09HSklEgp3fhFh4GLFy/m888/56mnnooG7EOG/YMex5yHaZrxLWYIYbFj2xree/0mCvK20b37YTz77MUcc0x7YlqFBprFxWtw8m6Pl8RLAGDRoo3861/zWLbsZx5/vBddu6YTCimUEghhVAKJ0sEdfr9FUlIixcUR3n5nDRNeX0Oz1N94+kZokS1ibSRQvQfqP2El3O/K8AqVtlsg8ilIuA0aVQfIUdxhLyZNwh2HOkD0f608kDjaksiIJBJxNChCGhSRsKMB4qaEpftYpRR4sUxUrcq9VNWtSLyEsVIi+jeOE+K15y8mZ9d6WrduzQUXXEBWVpZLdSNYt24dX375Jdu3bycYDKKUolWbXlx4xVgtPmpp5g+PGke7YdrK2T6LMXcOpLQkF8u2aNosi+uvPZHBx3emTet0UtMC7sqIX50OFRVlzJy5kS+++J4PPphPq1YJPPpoD448MsMldtarSrgdxTGAiOjvnkXxrofDguW/lHDSkAl8OylCnxZx1uQgWgqB+zoKTNvFfIILhjJYvhWWroYdv8Gvm2HZKsWwM27lngcfrwaQLtziW0oD55BN81ZO9xKny06Uo8lU1UkRhKH9fMM0kBGJVHEgcS1HzCrFQBgFhawCzjjNPcMMcPPdU3l3wihWLJ3GI488UuN7btioPU1bduXoXufQsk13Skt2a6I1U7hkCHqVJSSlkLtrC4sXfsaUT7S8QHYKXDI4wvmDdvLJzEk8Nhl+3gjrtkPjxhapqT4sSy9owxCkpNg0a5ZM9+4NmDFjKEcemUF+fgVKGdVcqsqXRhWwGK6FMUhJsenfL51fVt7GCce9wMxXimiSKg6ctVBgWIBbrsnNgR05sKUI8vLg992wfhOs+Q12bIVVm2vfmfufEP7fzmLFL1RAV5KlDoA9SyJd3UIn4rpYjtS3S4V0NPmYlKqSayU9pMiq1iS+SKmicYtSynXXJCgoKtjF+jULydm9CSUlqWkNaN7qaLKyWiCVxLAsTaEjXDZCS2iCNctA4pCRWZ9P3nyAeTPeJBgsAwV/P09w71mQLF2gp8FHC+Da5wze+WAQRxyRrlV043Z9y9IjsTp5UN2NqgqA6gCJ/Z6YmMbq1blMnbqKXbtLEcJg164QgdAcnr9JIMN/cLOTIOprFqYvZsHUb+GreYryEASDEA5DOCJqrFElp2bTqPHh1GvYmgZN2tG0ZQey6jem59FNGdqv6f+uBYlaEdf3MYRAGQqvbU94X7SQUd5Yw5FIUwNIxy3StRyqkvXwUsie++XFOt5uEP0bqZAK/bxSsy+mpGXTtefpGIalydsjISLhcJQ+EyGiRAa6uCnABNtvs2blfB678y5KinJJToBhfeGRSwVt6+luWcPSdY/RYxXrgi1YvqqPK6umF7qeATei6WkvntC/G3EA2hsron8PBHy89NL33H77NNo0LmPEObreGArBr5vQ+qM5+2EpEqC8An7eAFMWwWf/huXrKo8ep6bWJyUjm6SkTAKJqdSrfxjpmY3Jrt+arOwWpKbUx/T7cCJBIk4FijCmCabfxDD2ftkf4gI6sXkLj4PJQCBRmgLIAMNQCFOgHANpacui4xZXaNKLPWTM1VJKxrlRRMX2YlZDaBYURxGRAuVm0gzDJhKRSBlCOiCRCMvEiPP5dBHT1ZsXUL9hM1556lJ+XvA5ALddKLhlqB5LNdAds8KEPBOOvlZx76MD+OewpnGxRGzBx4Mh9vueLIioATT693BYcM01vbnggp6MvvkTPp72A7MnCawKV71hH8FhAE423P+c4pV3IadAWwiAxMR0Onc7jcOPHEJmVrOod2LZASzTimb4lGv1i4p3YpQa2q12qYiEYWKaxj5JKxzSAIm2nsSBxLMk+mYDaSiEo5BC6VqJNOIKjSrOlYrFHEqasRjd5aGqZFmUQkkD6SgMRwf/whE4EYUlBNKR2np5r6W8in88EzrYfh+P3jGYjesW0ygLJv1dcPzhsdkK5UoqryuAk++3eGrcYE45uQllZU4VqxDb/asCIhZH1A6E2qyKZ4VSUy0mTrqe+x+Ywil/+5B/Pw1+uZeCcQoMH+SUwMQZcOcDirAEy7LJbtCGw9odQ69+F5Fdvw3lpXlUVJRqnRERi/ylE47VvzxuYGFEr2tqIrdvzG1grekw8auqg2OHvARbJZAgNLmIGQOMUAopwDDdnd+NL5QEZdWQvfKI2SrdXhlEShGtqciIxDAlRkRiGBJHgDQEjhBuNkq7ckJ5DOcGQkiyGjTn0TsGsmXjLxzeHKY9IGiW6k7huX1Phglri6HXKPhw8jB69MikvFxhGL5qcUN8Bqr6wjf3ymLo56w5u6VUGfffN4RHHw3Q/5JJzH9dVNJFrNVqpMFbUxV/fwh2uaxMPfsOp/exl+EPJOP3JxEJBynM36pza6ZZ6bv1LG+U0teIuy8qvwBU1SGp4fCTDYTU/xRAKoHEHWUVbgsJhl7YptCpWWGYoJTrWukxVP1nwq2JqBrqIK4F8ADiWRMpdJHS1cDwiJqFAU5EavfOiYHSe28I8PkTeeDmnuzesZ72zWDJCwI77D7MNQQC2B6EwbcKPvjkLHr3rkc4LKu5UftqEfaUsao9RtEp5DvuOIVfV23mnbmzuLS/oM5xFAGvfQxX3a3/9UaND+fsi58kI7MZwYpSDMMgEg5Sle1diCqWIF5XJKp/WDk7GWN+pDJIRLyLlwe05X8OIPG7jddfJaq0pQgVsxKGEm58YVaaGdeGKD7VS1wxMRaUKAXSc7FM14KYjv6iwjq+MA2HSERheJkydLYrs14TnnngNHbvWM9RbWDGwwLbcZuC4vqURCaceJngiedOo0+fhi6jqFUt/Vrzgt/T7XtjVWrbifN55rnz6Np5BeecmENicS3VNgmliXDV3frDPO3ch+nYeQhCQThUFuX79b47L+nifY+e6wToJEx8h5H79jxqIs0u7/JzmUYl9yweJhZFSjduyv89gNQKFC896+0+ylNWj6sFqvigX2gW0rjPNoqPKGAUhsTNirl1DENgGNLV6ZNETIFhuS6WVCgEgUASLz12AetWLSArFaY/LkgTXmdy3GnDiX9XHDPkKM45uw3BoNuIGZZuTcJXixXw3CNjnzJWQuxLF6EgIz3ATaNP5PKb3+KDpwWqBhIZIxGenqCvdzn6NHr0OZ+S4lxtneMWvo61Yr97a9vTEvHiCm1VXRJtL5Zzie1iADGxLIPq+NBfYXOy1f8Gq8k+AMVjIBTxvRxeikRUpXTwdiqvuUToWypZFLeCb4CQAmFIhKH0aRoIw0G6bfe6rUUH6gkJCXww4T5WLJ5Oy8Yw72lBhuXyQ8X1NhkmvLtQkXFYZ15++Wzee38J7767ii1bCsjPK2HChHPp3Ts1Li9U2+LX8UR8Nbw6kPantVYzNdwyehiHPfcR23YEaZRWQz0sBb6Ypm8cPHQ0xUW7EIZZmendBYhpCP39GB5nhqisbagFc/V1pQvC3ohDlHHeZXg0bS29UNPxNYVAvopXlf+fBUitVqUGldXKEt6xHS76s8rn7ennSIEOal3LIQwHQ5g4poFhOkhlohyJbfuY9dXrzPzqdQI++OwhQaNElysKKpE2FgXgyscSuP76LLIy7uaIZnDdlXDfkzbzF91NZqb+A91HtTeBeNXb979+7Di6qp2WpvD5Shlx2eks/O19Tu8qapSCKCl1U7jJGUQiFdHYgagQT1xWzwBDuKpVRowVXhhCX48LyIX7OM+SRIuulonl8gPXdOzU5dy/LMgerUrdD6oUf1R2LuJoh4TXWKhAGghbD24ZhtTBualZ2IWymT31Ld4Zdw9JCTD3JcGRTdAV6CquVYUB/a5QNMgop0PyTJZNFDTpDKdfbvDwM5eQlWVi2yl8/fVa0tNtjj66cR2xxr66TnuRkTJM/vGP18nNreCDD67hzDN78MZL73NG1xpSvgIS/FXiQKELo4Ynp+AF1l4vmhEns+C6T14KF6Fvw60jxQuIGlFlXS3kY5i1/b9dFcz7CyAHBkzVd9po+7oX2xi63mJ4mS7bQDnCdbcUFoLff1vMm8/fjt+Gd+8TdK7nNt/Fz3q7VmTzbrj3RsE5x4HcrRlFlv4M81ak8skZHYlEwowe/SUvvjiDYPA5lApXc6cqW4oD/ZlEeOSRETRtei39+z3M198+zJp1yYiMUlRBVXMD9RsCayFUUaRb+l1BT8MyYlrqZiyOiAHG5f4VRhQsRDUORex54gHjulqGJTCN2ronM4Hx6i+A/AcskHItigCUqRkXTQUSAabCwuSHOZ/wzJ1XAfD2PwXDeum2kWrzFO7v7ZtA+6Yg83SgLmyY+LFi9M0DMc10rr56Ip99PIfRo0/CMAKum6VHZIU4GE1xgmXLttG5c0uggpISSZMmDRhwTH2O6riJwcc/QoJIhkBpdW6+IHRpJ5g2V7F1ywpate2uF7UpYtkmK1YF92TdKgEl3gXz3Cti7hmeBTFEXEwiEFZtADm3xur+X8dBA0sspaxdBm3iLdti07qFPHPnVVgmvPaA4MzOrihMHRQ3kspEbBL4bilcecWx3PL3j/j9lzkMGQwjRpwIRBDCYtWqfG677XNiIgkH8lAkJ2fwt7+9CZgsWZLDTz/9xtHdu3Ll+XBEi1V8//MOXXytis9yaN1JX123Zh6BhBS9u9smlqstYvtM7ICFz2/iC1j4AjaW37vNwg5Yset+/XjLb2L69d9bfkML+LjBueHKMNReKyz/CyD/LZDoxJgkKSXAV+8/z52XngzAM3cIrjiO2NhGbeOo7qkMzTcXlBCSEHIgJ8dgycKvmTJBsGErHHlkI9eG2dx001v06dOZyhIPBw4grVs34vXX55CbG6Zp0wROPfWfpGVms2ktPH871E+vMY+BisCgbvr6mhUzSUxOcWMEA8MV3bH8JrbfxAp4YDDxBWwNBr+l7/dpLRLL7yla6U5l02diWlZUmsHTThR1t2KpvwDyXwKJUpDVMJsnb72UiU/dj2XChEcE1w90A/K9ZRVUsL4MNhVqleqkFIuTT76L5+5wqCgEEZVfgm3bKvjmm+V06VKfveyM2q8jMzOBsWPnkpkZIDnJx/33vctvOYJMH9x0qahRW0gJaOd2nOfnbkEpGXWvbNtyLUgMBJbPxLItLJ+hb3ctjbY4lssxYEbdM9MUunvXA4YrESdMgzpMyF8W5D99KAVpWZlsWvM9VwzuwOwvP6Z5E/j4ccHlg4gJ2++Jq9a9blqwfE1svefkRrh5RClHthI6vWx6HO+pXHLJWOqlgt9/sNgQdDtO86bpjBnzPuXlCbRt5PDYA4rF6/TbuGskqF3VX14IvTG0a65/Ly3NiypRGTaaTMNn6MXvM7FsQ6tW2SaGra1MrDquJSvih8u8YF4P17gxipdn3Ie94q8g/SAe0pEkpVrcd+Up/DBrKlJKBnaHfz8lSFFuV64Zt1XtDTdUMnw7GzqNACcCZ54mGD1cqzGZAhJ9UFhYRjhczPTpv/LQrbDilxyaNWt00P7PJg0MsntJxo2fTaNGQW45U/DTBg2QKKt7Db6MKILDO8KazZCz6zcat2xfqeLtuVtRXUM35asr6CKuB8uIz2XEwGtU5SlR+5zV/suCHASL4Q/4CYduq5TEAAAHBElEQVSK+eaTFznjyPosnPEVLRpJ3nxEMHOiIFlVIXuujee2pi8yFX78QW+MCQG4fwQ4rqKOz6czXdOnr2Tud+s5Z4iiSweYOm0Relb1wMcgYFBUVMIFF8D9933AsJPKMcugV2M3oVDH4TjQraP+N7du+gXTiKV3hVvYi7aJeA2H7ui0Lp3rtvfoXiKive5xgYaKu0nE1X7+Ash/3mJIRVpGAlM+GM9lA9sx4fH7cSIhHrxJsPJjwUX93BStRe2s5WIP6zECy9YrbBNMA3wOsd4iAaccC889P5kF3+9m9FWKLi0EkyZNP0hZLP2mNv+ew/AhggZZQXq00okEuTet7ia0bymwbcGWTcv1YnbTtqZX64gWCYnriBaxxS5U3G3UcP4x1/IvgPxhi6GwLBthRFizbBZXDTmCcQ/egoyEGNIfVn4iuOdi8IXjrMZ+MpQbAuYt0ZbDrkGZSkkYOkjww4K1LJi7iO5HQvPm0KVNiPfen38Q4hCT+fNX0aIBUAzz3xE0TN63EKZTSxCGYue231DScRN+buGPuIUfF0dUtgwHl0PorxjkD2SnnIhDVoMs5k55m5cfvJ2cHdtRStKzk2Dik3BYisAy4kTv4Y+xB/phwXywfe7T1fQ85fDPOwXjJq7BDAlUOXz0mmDQRV9y9llH1Eg1uv9HItdeN5GHb9Bp21aZ+0jSoaBTWx07bVz3E1bAB0pV6sOKJwTzmhdq6mI4WMdfFmQ/LIZp20gZZPO67/nbaT148LqL2b19G107St5/SrDwQ2iXpF0gJaq4VH/kezVh6nxFVgYk1uIxyQq4ZTgMO1kgi7W7kxmCgT3XMXbs/APmaikFC75fj5+tDBsqXDbK/UuEXaTlUFi2aAqmbVfKkXm0TP+1jfAALZo/He3PQbEYjqR+40ymfTiRSc8+wI7Nm1BK0rYZvPaYoHsTSAzE9VIdSCJmBbvDcNRZiuwMWDRR8P/aO9fQOKoojv/OTGZnkzY+Ui39kGaj0qQaxIqE0tjQCmohBR+orYUIVb/1Q6FCQPBRUKFYFJtvSq0Uja2Ij2or9IPEFrXgAy3WPGxJo2nEprVN82gem+wcP8y0u1nZvPaRZrk/WNiFmTvMvfe/95x77zk3NEnHGfYgnOCrRh1YXqe8unMLT9WvIL11EQVKePD+F9n98inKCmdfmgBdw1C+RllQXMLbB7oZHDyH4xbghCxsp+DqNhMJ9lClZVUJlJe4VJdPmLQ4IyJlZgSZ5T+lE3IYHemjo7WZTSsjvL5tMz1nOqla5rHvDeHkEaG2DMJuEIuWhUTMVhieexP+OQduyH/WZBQmrRi7UWhrFna8speW1v40m96loeEDHqk9SVlRmlJTiJTCsjLh8sBFWo43+w46oH4GgYkX5xgjkMmcbydE8XUWTY0vse3x1byw+Ql6/u6iZoXQ3CT8uEfYtMbfWatOFkaNK41UCPXblX2f+5GOFZGZe48qEB6Aw+8M8dhD22lr75tl89s0Nn5LXfVBtjws6fdZgdh5qN/g//zm0LvxaE/JoJlzLQpE5qEwbNtGRImO9PLl+zuoq7yBT/fsYqivmzuXw7H98P3HSm1Ecd0gwYOl8SipDH/EUnZ+onx4IG7c3FsNjM+8LEVZuhDavx7i+a2v0XF6YIZdwObgF79TveQ91paDN56hd7SV9TX+Oeutv3xHTIcSpmmthKTXkvOOmTUf5NCJvuB4mXnkZ1g2p9t/5dhXTXS0nGCwvxexHNZWC88+CneX+zpQLzfqlxDs+gx2fwSuGwIRLl0a5fBeoWJhGnWrEC3w2H90EavWbaCyonhat3ndPzDcfYQFRTYay/C7FsHGBvjt1Bh1T26lZt1GVLyri4fYftK/TMR4Lb3R5a7Swmn5IFkTSN6ZXPN0RDRMi6w76Xnvyxhx5DVWVju2iJwlOdrdYJg//JyLf/4HgC5T14Z5RjPwTE4sB/XP5yo2dZ58/mA8G6DqlU3a2TQGNdX0a+BKTdXs8fsTJ44m+GFK0hSMJCah9L/PcA44IQIzIYmiH0yfpS1XKiIDxrSec/flPmAJcAdwHrgHWJSh4h2gXeKBJQJ04p9JmGwsDAq4ClVAbJIucUEgCvTi75SS4Hobi+tZTCGX6VGbERljFIgApTjEGGNEFA8hikcUIQYMU4wGR08MoYzHM79iY1OkF3AIcwvKX9qPKNysMKCwGHgLaNM5ajxDftV3BHia1KOUB6wE1k9SRidwNGmkkUDc//L/eVYvEJAVCDYKnE3IS1kBhPHz3qYiBvwJ3Kr+AYNVwO0KlcDcrRYYgRgRprazUrJKUruvUYHVTDxnYwy4TeGmKR55ETgO/BEI5qc5FYcRiCGXXW0K30cCUeXL9laDwWAwGAwGg8FgMBgMBoPBYDAYDIZrm/8AQ49HUDcPIZsAAAAASUVORK5CYII=', '2017-04-13 16:23:02');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ipausnahmen`
--

CREATE TABLE `ipausnahmen` (
  `IPID` int(5) UNSIGNED NOT NULL,
  `BehoerdenID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `IPAdresse` varchar(20) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kiosk`
--

CREATE TABLE `kiosk` (
  `kioskid` int(5) UNSIGNED NOT NULL,
  `kundenid` int(5) NOT NULL DEFAULT 0,
  `organisationsid` int(5) NOT NULL DEFAULT 0,
  `timestamp` bigint(20) NOT NULL DEFAULT 0,
  `cookiecode` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  `zugelassen` int(2) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kunde`
--

CREATE TABLE `kunde` (
  `KundenID` int(5) UNSIGNED NOT NULL,
  `Kundenname` varchar(50) NOT NULL DEFAULT '',
  `Anschrift` varchar(200) NOT NULL DEFAULT '',
  `Module` int(3) NOT NULL DEFAULT 0,
  `Startkennung` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `Anzahlkennungen` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `TerminURL` varchar(200) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Daten für Tabelle `kunde`
--

INSERT INTO `kunde` (`KundenID`, `Kundenname`, `Anschrift`, `Module`, `Startkennung`, `Anzahlkennungen`, `TerminURL`) VALUES
(1, 'Teststadt', 'Teststadt', 7, 100, 100, '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kundenlinks`
--

CREATE TABLE `kundenlinks` (
  `linkid` int(5) UNSIGNED NOT NULL,
  `kundenid` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `organisationsid` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `behoerdenid` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `beschreibung` varchar(50) NOT NULL DEFAULT '',
  `link` varchar(200) NOT NULL DEFAULT '',
  `oeffentlich` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `neuerFrame` int(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `log`
--

CREATE TABLE `log` (
  `log_id` int(11) NOT NULL,
  `type` enum('migration','buerger','error','') DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `ts` timestamp NOT NULL DEFAULT current_timestamp(),
  `message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mailpart`
--

CREATE TABLE `mailpart` (
  `id` int(5) UNSIGNED NOT NULL,
  `queueId` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `mime` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `base64` int(1) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mailqueue`
--

CREATE TABLE `mailqueue` (
  `id` int(5) UNSIGNED NOT NULL,
  `processID` int(5) NOT NULL DEFAULT 0,
  `departmentID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `createIP` varchar(40) COLLATE utf8mb3_unicode_ci NOT NULL,
  `createTimestamp` bigint(20) NOT NULL DEFAULT 0,
  `subject` varchar(150) COLLATE utf8mb3_unicode_ci NOT NULL,
  `clientFamilyName` varchar(150) COLLATE utf8mb3_unicode_ci NOT NULL,
  `clientEmail` varchar(150) COLLATE utf8mb3_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `migrations`
--

CREATE TABLE `migrations` (
  `filename` varchar(250) COLLATE utf8mb3_unicode_ci NOT NULL,
  `changeTimestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Daten für Tabelle `migrations`
--

INSERT INTO `migrations` (`filename`, `changeTimestamp`) VALUES
('260417-messenger-superuser.sql', '2019-08-23 15:22:12'),
('27465-create-mail-tables.sql', '2019-08-23 15:22:12'),
('27500-migration-system.sql', '2016-06-03 12:06:35'),
('28300-create-config-table.sql', '2019-08-23 15:22:12'),
('28301-create-notification-table.sql', '2019-08-23 15:22:12'),
('28736-create-datasource-tables.sql', '2019-08-23 15:22:12'),
('29407-workstation-name-length-to-varchar-5.sql', '2019-08-23 15:22:12'),
('31466-statistik-wartezeit-index.sql', '2019-08-23 15:22:12'),
('31523-soap-superuser.sql', '2019-08-23 15:22:12'),
('31586-sendMailReminder-config.sql', '2019-08-23 15:22:12'),
('31772-optimization.sql', '2019-08-23 15:22:12'),
('32130-archivedProcessesIntoStatistic.sql', '2019-08-23 15:22:12'),
('32183-config-blacklisted-address.sql', '2020-02-07 12:56:28'),
('32694-migrate-user-assignment.sql', '2019-08-23 15:22:12'),
('32897-statistik-client.sql', '2019-08-23 15:22:12'),
('33245-115-superuser.sql', '2019-08-23 15:22:12'),
('33493-create-slots-table.sql', '2019-08-23 15:22:22'),
('33648-add-email-column-to-nutzer.sql', '2019-08-23 15:22:22'),
('33756-create-apikey-tables.sql', '2019-08-23 15:22:22'),
('34135-deleteOldAvailabilityData.sql', '2019-08-23 15:22:22'),
('34308-add-lastupdate-column-to-nutzer.sql', '2019-08-23 15:22:22'),
('34516-create-scope-preference-table.sql', '2019-08-23 15:22:22'),
('34628-add-column-source-to-standort.sql', '2019-08-23 15:22:22'),
('34628-create-source-table.sql', '2019-08-23 15:22:22'),
('35550-deallocateAppointmentData.sql', '2019-08-23 15:22:22'),
('35726-column-source-with-default.sql', '2019-08-23 15:22:22'),
('36114-add-scopeid-notificationqueue.sql', '2019-08-23 15:22:22'),
('36114-modify-telephone-varchar-size.sql', '2022-03-15 11:55:16'),
('36380-config-mail-attachment.sql', '2019-08-23 15:22:22'),
('36427-apiclient.sql', '2020-02-07 12:56:31'),
('36432-buergeranliegen-add-source.sql', '2019-08-23 15:22:23'),
('36476-alter-source-collation.sql', '2019-08-23 15:22:23'),
('36616-wsrepsync-config.sql', '2019-08-23 15:22:23'),
('36795-nutzerzuordnung-add-missing.sql', '2020-02-07 12:56:31'),
('37117-slotTimeInMinutes-modify.sql', '2020-02-07 12:56:31'),
('44884-mailpart-index.sql', '2022-03-15 11:55:17'),
('45305-request-provider-alter.sql', '2022-03-15 11:55:17'),
('46962-calculateDayOffList.sql', '2022-03-15 11:55:17'),
('47486-primary-key-clusterzuordnung-source.sql', '2022-03-15 11:55:17'),
('50845-userid-unique.sql', '2022-03-15 11:55:17');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `notificationqueue`
--

CREATE TABLE `notificationqueue` (
  `id` int(5) UNSIGNED NOT NULL,
  `processID` int(5) NOT NULL DEFAULT 0,
  `departmentID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `createIP` varchar(40) COLLATE utf8mb3_unicode_ci NOT NULL,
  `createTimestamp` bigint(20) NOT NULL DEFAULT 0,
  `message` varchar(350) COLLATE utf8mb3_unicode_ci NOT NULL,
  `clientFamilyName` varchar(150) COLLATE utf8mb3_unicode_ci NOT NULL,
  `clientTelephone` varchar(50) COLLATE utf8mb3_unicode_ci NOT NULL,
  `scopeID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `nutzer`
--

CREATE TABLE `nutzer` (
  `NutzerID` int(5) UNSIGNED NOT NULL,
  `Name` varchar(50) NOT NULL DEFAULT '',
  `Passworthash` varchar(200) NOT NULL DEFAULT '',
  `Frage` varchar(200) NOT NULL DEFAULT '',
  `Antworthash` varchar(200) NOT NULL DEFAULT '',
  `Berechtigung` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `KundenID` int(5) NOT NULL DEFAULT 0,
  `BehoerdenID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `SessionID` varchar(200) NOT NULL DEFAULT '',
  `StandortID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `Arbeitsplatznr` varchar(5) NOT NULL DEFAULT '',
  `Datum` date NOT NULL DEFAULT '0000-00-00',
  `Kalenderansicht` int(2) UNSIGNED NOT NULL DEFAULT 0,
  `clusteransicht` int(2) NOT NULL DEFAULT 0,
  `notrufinitiierung` varchar(4) NOT NULL,
  `notrufantwort` varchar(4) NOT NULL,
  `aufrufzusatz` varchar(250) DEFAULT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `lastUpdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Daten für Tabelle `nutzer`
--

INSERT INTO `nutzer` (`NutzerID`, `Name`, `Passworthash`, `Frage`, `Antworthash`, `Berechtigung`, `KundenID`, `BehoerdenID`, `SessionID`, `StandortID`, `Arbeitsplatznr`, `Datum`, `Kalenderansicht`, `clusteransicht`, `notrufinitiierung`, `notrufantwort`, `aufrufzusatz`, `email`, `lastUpdate`) VALUES
(1, 'vorschau', '128196aca512b2989d1d442455a57629', '', '', 0, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', NULL, '', '2019-08-23 15:22:22'),
(136, 'testadmin', '$2y$10$C2szb/GeBKp9EdyuI0KiaO1.GHS3A6DzQRP2rJlGa.un63MepwJzu', '', '', 70, 0, 74, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', '', '2022-03-16 15:01:46'),
(137, 'testuser', '128196aca512b2989d1d442455a57629', '', '', 10, 0, 0, '', 0, '', '0000-00-00', 0, 0, '', '', '', '', '2020-03-02 13:10:32'),
(138, 'superuser', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', '', '2022-04-14 05:10:10'),
(5118, '_system_messenger', '128196aca512b2989d1d442455a57629', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', NULL, '', '2020-03-02 13:10:18'),
(5119, '_system_soap', '128196aca512b2989d1d442455a57629', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', NULL, '', '2020-03-02 13:10:20'),
(5120, '_system_115', '128196aca512b2989d1d442455a57629', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', NULL, '', '2020-03-02 13:10:23');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `nutzerzuordnung`
--

CREATE TABLE `nutzerzuordnung` (
  `nutzerid` int(5) NOT NULL,
  `behoerdenid` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Daten für Tabelle `nutzerzuordnung`
--

INSERT INTO `nutzerzuordnung` (`nutzerid`, `behoerdenid`) VALUES
(1, 0),
(136, 1),
(137, 1),
(138, 0),
(5118, 0),
(5119, 0),
(5120, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `oeffnungszeit`
--

CREATE TABLE `oeffnungszeit` (
  `OeffnungszeitID` int(5) UNSIGNED NOT NULL,
  `StandortID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `Startdatum` date NOT NULL DEFAULT '0000-00-00',
  `Endedatum` date NOT NULL DEFAULT '0000-00-00',
  `allexWochen` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `jedexteWoche` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `Wochentag` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `Anfangszeit` time NOT NULL DEFAULT '00:00:00',
  `Terminanfangszeit` time NOT NULL DEFAULT '00:00:00',
  `Endzeit` time NOT NULL DEFAULT '00:00:00',
  `Terminendzeit` time NOT NULL DEFAULT '00:00:00',
  `Timeslot` time NOT NULL DEFAULT '00:00:00',
  `Anzahlarbeitsplaetze` int(5) NOT NULL DEFAULT 0,
  `Anzahlterminarbeitsplaetze` int(5) NOT NULL DEFAULT 0,
  `kommentar` varchar(200) DEFAULT NULL,
  `reduktionTermineImInternet` int(2) DEFAULT 0,
  `erlaubemehrfachslots` tinyint(1) NOT NULL DEFAULT 0,
  `reduktionTermineCallcenter` int(11) NOT NULL DEFAULT 0,
  `Offen_ab` int(11) NOT NULL DEFAULT 0,
  `Offen_bis` int(11) NOT NULL DEFAULT 0,
  `updateTimestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `organisation`
--

CREATE TABLE `organisation` (
  `OrganisationsID` int(5) UNSIGNED NOT NULL,
  `InfoBezirkID` int(11) NOT NULL DEFAULT 0,
  `KundenID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `Organisationsname` varchar(50) NOT NULL DEFAULT '',
  `Anschrift` varchar(200) NOT NULL DEFAULT '',
  `kioskpasswortschutz` int(2) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `preferences`
--

CREATE TABLE `preferences` (
  `entity` enum('owner','organisation','department','scope','process','availability') COLLATE utf8mb3_unicode_ci NOT NULL,
  `id` int(5) UNSIGNED NOT NULL,
  `groupName` varchar(50) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `name` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `value` text COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `updateTimestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Daten für Tabelle `preferences`
--

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`) VALUES
('scope', 0, 'appointment', 'deallocationDuration', '15', '2022-03-16 14:04:36'),
('scope', 0, 'appointment', 'endInDaysDefault', '60', '2022-03-16 14:04:36'),
('scope', 0, 'appointment', 'multipleSlotsEnabled', '0', '2022-04-12 11:31:15'),
('scope', 0, 'appointment', 'reservationDuration', '15', '2022-03-16 14:04:36'),
('scope', 0, 'appointment', 'startInDaysDefault', '2', '2022-03-16 14:04:36'),
('scope', 0, 'pickup', 'alternateName', 'Ausgabe', '2022-03-16 14:04:36'),
('scope', 0, 'queue', 'callDisplayText', 'Herzlich Willkommen', '2022-03-16 14:04:36'),
('scope', 0, 'queue', 'firstNumber', '1', '2022-03-16 14:04:36'),
('scope', 0, 'queue', 'lastNumber', '999', '2022-03-16 14:04:36'),
('scope', 0, 'queue', 'maxNumberContingent', '999', '2022-03-16 14:04:36'),
('scope', 0, 'queue', 'processingTimeAverage', '12', '2022-03-16 14:04:36'),
('scope', 1, 'appointment', 'endInDaysDefault', '60', '2019-08-23 15:22:22'),
('scope', 1, 'appointment', 'startInDaysDefault', '0', '2019-08-23 15:22:22'),
('scope', 3, 'appointment', 'deallocationDuration', '15', '2022-04-12 11:32:03'),
('scope', 3, 'appointment', 'endInDaysDefault', '60', '2022-04-12 11:32:03'),
('scope', 3, 'appointment', 'reservationDuration', '15', '2022-04-12 11:32:03'),
('scope', 3, 'appointment', 'startInDaysDefault', '2', '2022-04-12 11:32:03'),
('scope', 3, 'client', 'emailFrom', 'noreply@muenchen.de', '2022-04-12 11:32:03'),
('scope', 3, 'notifications', 'headsUpTime', '10', '2022-04-12 11:32:03'),
('scope', 3, 'pickup', 'alternateName', 'Ausgabe', '2022-04-12 11:32:03'),
('scope', 3, 'queue', 'callDisplayText', 'Herzlich Willkommen', '2022-04-12 11:32:03'),
('scope', 3, 'queue', 'firstNumber', '1', '2022-04-12 11:32:03'),
('scope', 3, 'queue', 'lastNumber', '999', '2022-04-12 11:32:03'),
('scope', 3, 'queue', 'maxNumberContingent', '999', '2022-04-12 11:32:03'),
('scope', 3, 'queue', 'processingTimeAverage', '12', '2022-04-12 11:32:03'),
('scope', 3, 'ticketprinter', 'buttonName', 'Termin Wartebereich BB Leonrod', '2022-04-12 11:32:03'),
('scope', 3, 'workstation', 'emergencyRefreshInterval', '5', '2022-04-12 11:32:03');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `process_sequence`
--

CREATE TABLE `process_sequence` (
  `processId` int(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='This table is just a helper for some queries';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `provider`
--

CREATE TABLE `provider` (
  `source` varchar(10) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `id` varchar(20) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `name` varchar(1000) COLLATE utf8mb3_unicode_ci NOT NULL,
  `contact__city` varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL,
  `contact__country` varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL,
  `contact__lat` float NOT NULL,
  `contact__lon` float NOT NULL,
  `contact__postalCode` int(5) NOT NULL,
  `contact__region` varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL,
  `contact__street` varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL,
  `contact__streetNumber` varchar(20) COLLATE utf8mb3_unicode_ci NOT NULL,
  `link` varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL,
  `data` text COLLATE utf8mb3_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `request`
--

CREATE TABLE `request` (
  `source` varchar(10) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `id` varchar(20) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `name` varchar(1000) COLLATE utf8mb3_unicode_ci NOT NULL,
  `link` varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL,
  `group` varchar(200) COLLATE utf8mb3_unicode_ci NOT NULL,
  `data` text COLLATE utf8mb3_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `request_provider`
--

CREATE TABLE `request_provider` (
  `source` varchar(10) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `request__id` varchar(20) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `provider__id` varchar(20) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `slots` float NOT NULL DEFAULT 0,
  `bookable` smallint(5) UNSIGNED DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sessiondata`
--

CREATE TABLE `sessiondata` (
  `sessionid` varchar(100) NOT NULL,
  `sessionname` varchar(100) NOT NULL,
  `sessioncontent` text DEFAULT NULL,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `slot`
--

CREATE TABLE `slot` (
  `slotID` int(5) UNSIGNED NOT NULL,
  `scopeID` int(5) UNSIGNED DEFAULT NULL,
  `year` smallint(5) UNSIGNED DEFAULT NULL,
  `month` tinyint(5) UNSIGNED DEFAULT NULL,
  `day` tinyint(5) UNSIGNED DEFAULT NULL,
  `time` time DEFAULT NULL,
  `availabilityID` int(5) UNSIGNED DEFAULT NULL,
  `public` tinyint(5) UNSIGNED DEFAULT NULL,
  `callcenter` tinyint(5) UNSIGNED DEFAULT NULL,
  `intern` tinyint(5) UNSIGNED DEFAULT NULL,
  `status` enum('free','full','cancelled') COLLATE utf8mb3_unicode_ci DEFAULT 'free',
  `slotTimeInMinutes` smallint(5) UNSIGNED DEFAULT NULL,
  `createTimestamp` bigint(20) DEFAULT NULL,
  `updateTimestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `slot_hiera`
--

CREATE TABLE `slot_hiera` (
  `slothieraID` bigint(5) UNSIGNED NOT NULL,
  `slotID` int(5) UNSIGNED DEFAULT NULL,
  `ancestorID` int(5) UNSIGNED DEFAULT NULL,
  `ancestorLevel` tinyint(5) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `slot_process`
--

CREATE TABLE `slot_process` (
  `slotID` int(5) UNSIGNED NOT NULL,
  `processID` int(5) UNSIGNED NOT NULL,
  `updateTimestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `slot_sequence`
--

CREATE TABLE `slot_sequence` (
  `slotsequence` int(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci COMMENT='This table is just a helper for some queries';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sms`
--

CREATE TABLE `sms` (
  `smsID` int(5) UNSIGNED NOT NULL,
  `BehoerdenID` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `enabled` int(3) NOT NULL DEFAULT 1,
  `Absender` varchar(60) NOT NULL DEFAULT 'Service',
  `interneterinnerung` int(2) NOT NULL DEFAULT 1,
  `internetbestaetigung` int(2) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `source`
--

CREATE TABLE `source` (
  `source` varchar(50) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb3 DEFAULT NULL,
  `editable` tinyint(1) NOT NULL DEFAULT 0,
  `contact__name` varchar(255) CHARACTER SET utf8mb3 DEFAULT NULL,
  `contact__email` varchar(50) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `lastChange` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Daten für Tabelle `source`
--

INSERT INTO `source` (`source`, `label`, `editable`, `contact__name`, `contact__email`, `lastChange`) VALUES
('dldb', 'Dienstleistungsdatenbank', 0, 'Dienstleistungsdatenbank', 'noreply@muenchen.de', '2022-04-14 05:27:15'),
('unittest', 'Unittest Source', 1, 'Testfirma', 'noreply@muenchen.de', '2022-04-14 05:26:51');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `standort`
--

CREATE TABLE `standort` (
  `StandortID` int(5) UNSIGNED NOT NULL,
  `BehoerdenID` int(5) NOT NULL DEFAULT 0,
  `InfoDienstleisterID` int(11) NOT NULL DEFAULT 0,
  `Hinweis` varchar(200) NOT NULL DEFAULT '',
  `Bezeichnung` varchar(200) NOT NULL DEFAULT '',
  `Adresse` varchar(200) NOT NULL DEFAULT '',
  `Stadtplanlink` varchar(200) NOT NULL DEFAULT '',
  `Bearbeitungszeit` time NOT NULL DEFAULT '00:00:00',
  `Kennung` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `Termine_ab` int(5) NOT NULL DEFAULT 1,
  `Termine_bis` int(5) NOT NULL DEFAULT 90,
  `smswarteschlange` int(2) NOT NULL DEFAULT 0,
  `smswmsbestaetigung` int(2) NOT NULL DEFAULT 0,
  `smsbenachrichtigungsfrist` int(5) UNSIGNED NOT NULL DEFAULT 10,
  `smsbenachrichtigungstext` varchar(255) NOT NULL DEFAULT '',
  `smsbestaetigungstext` varchar(255) NOT NULL DEFAULT '',
  `wartenrsperre` tinyint(4) NOT NULL DEFAULT 0,
  `wartenrhinweis` text NOT NULL,
  `notruffunktion` int(2) NOT NULL DEFAULT 0,
  `notrufausgeloest` int(2) NOT NULL DEFAULT 0,
  `notrufinitiierung` int(5) DEFAULT NULL,
  `notrufantwort` int(5) DEFAULT NULL,
  `emailPflichtfeld` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `anmerkungPflichtfeld` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `anmerkungLabel` varchar(255) NOT NULL DEFAULT '',
  `telefonPflichtfeld` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `standortinfozeile` varchar(255) NOT NULL DEFAULT '',
  `standortkuerzel` varchar(20) DEFAULT NULL,
  `aufrufanzeigetext` text NOT NULL,
  `reservierungsdauer` int(4) NOT NULL DEFAULT 60,
  `anzahlwiederaufruf` int(2) NOT NULL DEFAULT 3,
  `startwartenr` int(5) NOT NULL DEFAULT 1,
  `endwartenr` int(5) NOT NULL DEFAULT 999,
  `letztewartenr` int(5) NOT NULL DEFAULT 0,
  `wartenrdatum` date DEFAULT NULL,
  `mehrfachtermine` int(2) DEFAULT 0,
  `schreibschutz` int(2) DEFAULT 0,
  `defaultabholerstandort` int(4) NOT NULL DEFAULT 0,
  `ausgabeschaltername` varchar(50) DEFAULT 'Ausgabe',
  `ohnestatistik` int(2) NOT NULL DEFAULT 0,
  `smskioskangebotsfrist` int(5) NOT NULL DEFAULT 0,
  `emailstandortadmin` varchar(250) DEFAULT NULL,
  `wartenummernkontingent` int(5) DEFAULT NULL,
  `vergebenewartenummern` int(5) DEFAULT NULL,
  `kundenbefragung` int(1) DEFAULT NULL,
  `kundenbef_label` varchar(250) DEFAULT NULL,
  `kundenbef_emailtext` text DEFAULT NULL,
  `telefonaktiviert` int(1) DEFAULT NULL,
  `virtuellesachbearbeiterzahl` int(5) NOT NULL DEFAULT -1,
  `datumvirtuellesachbearbeiterzahl` date DEFAULT NULL,
  `smsnachtrag` int(1) DEFAULT 0,
  `wartezeitveroeffentlichen` tinyint(1) NOT NULL DEFAULT 0,
  `loeschdauer` int(11) NOT NULL DEFAULT 0,
  `qtv_url` varchar(250) NOT NULL DEFAULT '',
  `updateTimestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `source` varchar(10) DEFAULT 'dldb'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `standortcluster`
--

CREATE TABLE `standortcluster` (
  `clusterID` int(5) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `clusterinfozeile1` varchar(250) NOT NULL DEFAULT '',
  `clusterinfozeile2` varchar(250) NOT NULL DEFAULT '',
  `stadtplanlink` varchar(255) NOT NULL DEFAULT '',
  `aufrufanzeigetext` text NOT NULL,
  `standortkuerzelanzeigen` int(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `statistik`
--

CREATE TABLE `statistik` (
  `statistikid` int(5) UNSIGNED NOT NULL,
  `kundenid` int(5) UNSIGNED NOT NULL,
  `organisationsid` int(5) UNSIGNED NOT NULL,
  `behoerdenid` int(5) UNSIGNED NOT NULL,
  `clusterid` int(5) UNSIGNED NOT NULL,
  `standortid` int(5) UNSIGNED NOT NULL,
  `anliegenid` int(11) NOT NULL,
  `datum` date NOT NULL,
  `lastbuergerarchivid` int(5) UNSIGNED NOT NULL,
  `termin` tinyint(1) NOT NULL DEFAULT 0,
  `info_dl_id` int(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `wartenrstatistik`
--

CREATE TABLE `wartenrstatistik` (
  `wartenrstatistikid` int(5) UNSIGNED NOT NULL,
  `standortid` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `zeit_ab_00` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_01` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_02` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_03` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_04` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_05` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_06` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_07` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_08` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_09` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_10` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_11` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_12` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_13` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_14` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_15` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_16` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_17` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_18` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_19` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_20` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_21` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_22` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `zeit_ab_23` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_00` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_01` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_02` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_03` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_04` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_05` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_06` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_07` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_08` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_09` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_10` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_11` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_12` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_13` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_14` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_15` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_16` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_17` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_18` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_19` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_20` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_21` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_22` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `wartende_ab_23` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_00` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_01` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_02` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_03` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_04` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_05` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_06` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_07` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_08` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_09` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_10` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_20` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_11` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_12` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_13` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_14` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_15` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_16` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_17` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_18` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_19` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_21` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_22` int(5) UNSIGNED NOT NULL DEFAULT 0,
  `echte_zeit_ab_23` int(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `abrechnung`
--
ALTER TABLE `abrechnung`
  ADD PRIMARY KEY (`AbrechnungsID`),
  ADD KEY `StandortID` (`StandortID`,`Datum`);

--
-- Indizes für die Tabelle `apiclient`
--
ALTER TABLE `apiclient`
  ADD PRIMARY KEY (`apiClientID`),
  ADD KEY `clientKey` (`clientKey`,`accesslevel`);

--
-- Indizes für die Tabelle `apikey`
--
ALTER TABLE `apikey`
  ADD PRIMARY KEY (`key`);

--
-- Indizes für die Tabelle `apiquota`
--
ALTER TABLE `apiquota`
  ADD PRIMARY KEY (`quotaid`);

--
-- Indizes für die Tabelle `behoerde`
--
ALTER TABLE `behoerde`
  ADD PRIMARY KEY (`BehoerdenID`);

--
-- Indizes für die Tabelle `buerger`
--
ALTER TABLE `buerger`
  ADD PRIMARY KEY (`BuergerID`),
  ADD KEY `speed_test` (`StandortID`,`Datum`,`Uhrzeit`,`Abholer`,`NutzerID`,`nicht_erschienen`),
  ADD KEY `AbholerIndex` (`Abholer`,`Datum`,`istFolgeterminvon`),
  ADD KEY `Aufrufanzeige` (`NutzerID`,`nicht_erschienen`,`aufruferfolgreich`,`aufrufzeit`),
  ADD KEY `istFolgeterminvon` (`istFolgeterminvon`),
  ADD KEY `vorlaeufigeBuchung` (`vorlaeufigeBuchung`),
  ADD KEY `Datum` (`Datum`,`Uhrzeit`),
  ADD KEY `StandortID` (`StandortID`,`nicht_erschienen`,`NutzerID`),
  ADD KEY `StandortUhrzeit` (`StandortID`,`Uhrzeit`),
  ADD KEY `AbholortID` (`AbholortID`,`Abholer`),
  ADD KEY `NutzerID` (`NutzerID`),
  ADD KEY `updateTimestamp` (`updateTimestamp`);

--
-- Indizes für die Tabelle `buergeranliegen`
--
ALTER TABLE `buergeranliegen`
  ADD PRIMARY KEY (`BuergeranliegenID`),
  ADD KEY `BuergerID` (`BuergerID`,`AnliegenID`,`BuergerarchivID`,`BuergeranliegenID`),
  ADD KEY `AnliegenID` (`AnliegenID`,`BuergerarchivID`),
  ADD KEY `BuergerarchivID` (`BuergerarchivID`);

--
-- Indizes für die Tabelle `buergerarchiv`
--
ALTER TABLE `buergerarchiv`
  ADD PRIMARY KEY (`BuergerarchivID`),
  ADD KEY `Datum` (`Datum`,`nicht_erschienen`,`mitTermin`),
  ADD KEY `StandortID` (`StandortID`,`Datum`,`wartezeit`),
  ADD KEY `scopedate` (`StandortID`,`Datum`),
  ADD KEY `scopemissed` (`StandortID`,`nicht_erschienen`),
  ADD KEY `scopeappointment` (`StandortID`,`mitTermin`);

--
-- Indizes für die Tabelle `clusterzuordnung`
--
ALTER TABLE `clusterzuordnung`
  ADD KEY `clusterID` (`clusterID`,`standortID`),
  ADD KEY `standortID` (`standortID`,`clusterID`);

--
-- Indizes für die Tabelle `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`name`);

--
-- Indizes für die Tabelle `email`
--
ALTER TABLE `email`
  ADD PRIMARY KEY (`emailID`),
  ADD KEY `BehoerdenID` (`BehoerdenID`);

--
-- Indizes für die Tabelle `feiertage`
--
ALTER TABLE `feiertage`
  ADD PRIMARY KEY (`FeiertagID`),
  ADD KEY `BehoerdenID` (`BehoerdenID`),
  ADD KEY `DatumBehoerd` (`Datum`,`BehoerdenID`),
  ADD KEY `updateTimestamp` (`updateTimestamp`);

--
-- Indizes für die Tabelle `imagedata`
--
ALTER TABLE `imagedata`
  ADD PRIMARY KEY (`imagename`);

--
-- Indizes für die Tabelle `ipausnahmen`
--
ALTER TABLE `ipausnahmen`
  ADD PRIMARY KEY (`IPID`);

--
-- Indizes für die Tabelle `kiosk`
--
ALTER TABLE `kiosk`
  ADD PRIMARY KEY (`kioskid`);

--
-- Indizes für die Tabelle `kunde`
--
ALTER TABLE `kunde`
  ADD PRIMARY KEY (`KundenID`);

--
-- Indizes für die Tabelle `kundenlinks`
--
ALTER TABLE `kundenlinks`
  ADD PRIMARY KEY (`linkid`),
  ADD KEY `behoerdenid` (`behoerdenid`);

--
-- Indizes für die Tabelle `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `reference_id` (`reference_id`);

--
-- Indizes für die Tabelle `mailpart`
--
ALTER TABLE `mailpart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `queueId` (`queueId`);

--
-- Indizes für die Tabelle `mailqueue`
--
ALTER TABLE `mailqueue`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`filename`);

--
-- Indizes für die Tabelle `notificationqueue`
--
ALTER TABLE `notificationqueue`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `nutzer`
--
ALTER TABLE `nutzer`
  ADD PRIMARY KEY (`NutzerID`),
  ADD KEY `Standort` (`StandortID`,`Arbeitsplatznr`),
  ADD KEY `Name` (`Name`),
  ADD KEY `lastUpdate` (`lastUpdate`);

--
-- Indizes für die Tabelle `nutzerzuordnung`
--
ALTER TABLE `nutzerzuordnung`
  ADD PRIMARY KEY (`nutzerid`,`behoerdenid`);

--
-- Indizes für die Tabelle `oeffnungszeit`
--
ALTER TABLE `oeffnungszeit`
  ADD PRIMARY KEY (`OeffnungszeitID`),
  ADD KEY `StandortID` (`StandortID`,`Terminanfangszeit`),
  ADD KEY `Startdatum` (`Startdatum`,`Endedatum`),
  ADD KEY `updateTimestamp` (`updateTimestamp`);

--
-- Indizes für die Tabelle `organisation`
--
ALTER TABLE `organisation`
  ADD PRIMARY KEY (`OrganisationsID`);

--
-- Indizes für die Tabelle `preferences`
--
ALTER TABLE `preferences`
  ADD PRIMARY KEY (`entity`,`id`,`groupName`,`name`),
  ADD KEY `updateTimestamp` (`updateTimestamp`);

--
-- Indizes für die Tabelle `process_sequence`
--
ALTER TABLE `process_sequence`
  ADD PRIMARY KEY (`processId`);

--
-- Indizes für die Tabelle `provider`
--
ALTER TABLE `provider`
  ADD PRIMARY KEY (`source`,`id`),
  ADD KEY `id` (`id`);

--
-- Indizes für die Tabelle `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`source`,`id`),
  ADD KEY `id` (`id`);

--
-- Indizes für die Tabelle `request_provider`
--
ALTER TABLE `request_provider`
  ADD PRIMARY KEY (`source`,`request__id`,`provider__id`);

--
-- Indizes für die Tabelle `sessiondata`
--
ALTER TABLE `sessiondata`
  ADD PRIMARY KEY (`sessionid`,`sessionname`),
  ADD KEY `sessionname` (`sessionname`,`ts`);

--
-- Indizes für die Tabelle `slot`
--
ALTER TABLE `slot`
  ADD PRIMARY KEY (`slotID`),
  ADD KEY `scopeID` (`scopeID`,`year`,`month`,`day`,`time`,`status`),
  ADD KEY `year` (`year`,`month`,`day`,`time`),
  ADD KEY `availabilityID` (`availabilityID`),
  ADD KEY `updateTimestamp` (`updateTimestamp`);

--
-- Indizes für die Tabelle `slot_hiera`
--
ALTER TABLE `slot_hiera`
  ADD PRIMARY KEY (`slothieraID`),
  ADD KEY `slotID` (`slotID`,`ancestorID`),
  ADD KEY `ancestorID` (`ancestorID`,`ancestorLevel`,`slotID`);

--
-- Indizes für die Tabelle `slot_process`
--
ALTER TABLE `slot_process`
  ADD PRIMARY KEY (`slotID`,`processID`),
  ADD KEY `processID` (`processID`),
  ADD KEY `updateTimestamp` (`updateTimestamp`);

--
-- Indizes für die Tabelle `slot_sequence`
--
ALTER TABLE `slot_sequence`
  ADD PRIMARY KEY (`slotsequence`);

--
-- Indizes für die Tabelle `sms`
--
ALTER TABLE `sms`
  ADD PRIMARY KEY (`smsID`),
  ADD KEY `BehoerdenID` (`BehoerdenID`);

--
-- Indizes für die Tabelle `source`
--
ALTER TABLE `source`
  ADD PRIMARY KEY (`source`),
  ADD KEY `source` (`source`,`lastChange`);

--
-- Indizes für die Tabelle `standort`
--
ALTER TABLE `standort`
  ADD PRIMARY KEY (`StandortID`),
  ADD KEY `BehoerdenID` (`BehoerdenID`),
  ADD KEY `updateTimestamp` (`updateTimestamp`),
  ADD KEY `source` (`source`);

--
-- Indizes für die Tabelle `standortcluster`
--
ALTER TABLE `standortcluster`
  ADD PRIMARY KEY (`clusterID`);

--
-- Indizes für die Tabelle `statistik`
--
ALTER TABLE `statistik`
  ADD PRIMARY KEY (`statistikid`),
  ADD KEY `anliegen` (`anliegenid`),
  ADD KEY `organisationsid` (`organisationsid`),
  ADD KEY `behoerdenid` (`behoerdenid`),
  ADD KEY `standortid` (`standortid`,`anliegenid`,`datum`),
  ADD KEY `scopedate` (`standortid`,`datum`),
  ADD KEY `departmentdate` (`behoerdenid`,`datum`),
  ADD KEY `organisationdate` (`organisationsid`,`datum`);

--
-- Indizes für die Tabelle `wartenrstatistik`
--
ALTER TABLE `wartenrstatistik`
  ADD PRIMARY KEY (`wartenrstatistikid`),
  ADD KEY `scopedate` (`standortid`,`datum`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `abrechnung`
--
ALTER TABLE `abrechnung`
  MODIFY `AbrechnungsID` int(9) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `apiclient`
--
ALTER TABLE `apiclient`
  MODIFY `apiClientID` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT für Tabelle `apiquota`
--
ALTER TABLE `apiquota`
  MODIFY `quotaid` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `behoerde`
--
ALTER TABLE `behoerde`
  MODIFY `BehoerdenID` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `buerger`
--
ALTER TABLE `buerger`
  MODIFY `BuergerID` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `buergeranliegen`
--
ALTER TABLE `buergeranliegen`
  MODIFY `BuergeranliegenID` int(9) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `buergerarchiv`
--
ALTER TABLE `buergerarchiv`
  MODIFY `BuergerarchivID` int(9) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `email`
--
ALTER TABLE `email`
  MODIFY `emailID` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `feiertage`
--
ALTER TABLE `feiertage`
  MODIFY `FeiertagID` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `ipausnahmen`
--
ALTER TABLE `ipausnahmen`
  MODIFY `IPID` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `kiosk`
--
ALTER TABLE `kiosk`
  MODIFY `kioskid` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `kunde`
--
ALTER TABLE `kunde`
  MODIFY `KundenID` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT für Tabelle `kundenlinks`
--
ALTER TABLE `kundenlinks`
  MODIFY `linkid` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `log`
--
ALTER TABLE `log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `mailpart`
--
ALTER TABLE `mailpart`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `mailqueue`
--
ALTER TABLE `mailqueue`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `notificationqueue`
--
ALTER TABLE `notificationqueue`
  MODIFY `id` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `nutzer`
--
ALTER TABLE `nutzer`
  MODIFY `NutzerID` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5126;

--
-- AUTO_INCREMENT für Tabelle `oeffnungszeit`
--
ALTER TABLE `oeffnungszeit`
  MODIFY `OeffnungszeitID` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT für Tabelle `organisation`
--
ALTER TABLE `organisation`
  MODIFY `OrganisationsID` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT für Tabelle `slot`
--
ALTER TABLE `slot`
  MODIFY `slotID` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `slot_hiera`
--
ALTER TABLE `slot_hiera`
  MODIFY `slothieraID` bigint(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `sms`
--
ALTER TABLE `sms`
  MODIFY `smsID` int(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT für Tabelle `standort`
--
ALTER TABLE `standort`
  MODIFY `StandortID` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `standortcluster`
--
ALTER TABLE `standortcluster`
  MODIFY `clusterID` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `statistik`
--
ALTER TABLE `statistik`
  MODIFY `statistikid` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `wartenrstatistik`
--
ALTER TABLE `wartenrstatistik`
  MODIFY `wartenrstatistikid` int(5) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
