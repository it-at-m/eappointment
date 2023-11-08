## 2.25.01
* #56991 - The curl option for setting up an API proxy server has been added to the configuration

## 2.24.14
* #56299 - remove invalid email from queue automatically
* #37 - Rejecting mail with invalid Content-Transfer-Encoding fixed

## 2.24.10

* #55383 - API access data are now read from the env variable ZMS_API_PASSWORD_MESSAGING

## 2.24.09

* #55079 Number of maximum characters per line for sending SMS via PHPMailer has been increased

## 2.24.00

* #49629 Sicherheit: Aktualisierung zentraler Bibliotheken für Stabilität und Sicherheit des Systems durchgeführt

## 2.23.11

* #49020 - env variable ZMS_MAILS_PER_MINUTE verfügbar gemacht um Anzahl zu versendener Mails konfigurierbar zu machen
* #44509 - Timezone in ICS Templates für Terminbestätigung und Terminabsagen eingefügt

## 2.23.06

* #44026 Bugfix: Eine Mail ohne Inhalt wird aus der Datenbank entfernt und in die Log Tabelle wird ein entsprechender Eintrag geschrieben.
* Wenn eine Mail wegen fehlender Absenderadresse nicht versandt werden kann, wird jetzt in der Fehlermeldung auch die Vorgangsnummer angezeigt


## 2.20.00

* #36252 Mail-Header X-Mailer mit Version-Informationen ausgetauscht

## 2.19.05

* #35764 Deploy Tokens eingebaut


