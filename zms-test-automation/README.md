## Ablöse Terminvereinbarung Testautomatisierung (ZMS-Test-Automation)

### Einleitung

In diesem repository finden Sie den Quellcode für das "test automation framework" zum Projekt Ablöse Terminvereinbarung, kurz ZMS, der Landeshauptstadt München. 
****
### Wo worden Testfälle Dokumentiert?
Alle automatisierten Testfälle sind in [Jira](https://jira.muenchen.de/browse/ZM) dokumentiert.
****
### Wie kann das Projekt gebaut werden?
Als Build-Werkzeug verwendet das Projekt [Maven](https://maven.apache.org), sodass mit den mvn Befehlen gearbeitet wird
```
mvn clean package -DskipTests
```
****
### Wie werden Testfälle ausgeführt?
Wie das Bauen funktioniert die Ausführung mit Maven Befehlen.  
Testfälle müssen im Cucumber Format als **"*.feature"** Dateien vorliegen. Der Test Runner ist so implementiert, dass alle feature Dateien unter dem Pfad `src/test/resources/features/` entdeckt werden. Bei Vorliegen mehrerer Testfälle (Cucumber Szenarien) werden diese parallel ausgeführt!  
Was ausgeführt wird kann weiter eingeschränkt werden mit dem CLI-Parameter `cucumber.filter.tags`:
```
mvn clean test -Dcucumber.filter.tags=@executeLocally
```
Der Befehl führt alle Cucumber Szenarien die das Tag `executeLocally` enthalten. Die Tags können über die Labels bzw. Stichwörter der Testfälle in Jira gesetzt werden.

#### Weitere vom Framework bereitgestellte CLI-Parameter:
Alle folgenden Parameter beginnen entweder mit **"taf."** oder falls in Datei **"testautomation.properties"** hinterlegt mit **"testautomation."**. Andernfalls werden diese _nicht_ vom Framework erkannt!
* **seleniumGridUrl**  
Die URL zum Endpunkt des Selenium Grid Servers.
* **seleniumGridToken**  
Das ist der Token der für die Authentifizierung am Selenium Grid verwendet werden kann.
* **browser**  
Betrifft nur Ausführung im Selenium Grid. Mögliche Werte sind **firefox**, **edge** und **chrome**
* **browserVersion**  
Betrifft nur Ausführung im Selenium Grid. Version des Browsers.
* **defaultScriptAndPageLoadTime**  
Wert für Script- und Seitenladezeiten in Millisekunden.
* **defaultImplicitWaitTime**  
Wert in Millisekunden der zwischen jeder Aktion mindestens gewartet wird.
* **defaultExplicitWaitTime**  
Wert in Sekunden der für bedingtes Warten gilt. Das ist also die Wartezeit, die gewartet wird auf vorher festgelegte Konditionen. Wird diese überschritten wird ein Timeout Fehler geworfen.
* **useProxy**  
Legt fest, ob eine Proxy-Konfiguration genutzt werden soll.
* **usePAC**  
Legt fest, ob ein Proxy-Auto-Config genutzt werden soll.
* **pACUrl**  
Hiermit gibt man die URL zur PAC Datei an.
* **proxyAddress**  
Die Adresse zu einem HTTP Proxy.
* **proxyPort**  
Der Zur Proxy Adresse passende Port.
* **useIncognitoMode**  
Aktiviert den privaten Modus des Browsers.
* **numberOfRetries**  
Gibt die maximale Anzahl zu wiederholender Schritte an, falls diese nicht erfolgreich durchliefen. Achtung wird nur von bestimmten Testschritten unterstützt und erhöht die Dauer der Testdurchführung potenziell immens.
* **testDataEncryptionPassword**  
Das Passwort welches verwendet wird um Testdaten zu ent- und verschlüsseln.
* **logLevel**  
Gibt an welche Kategorie von Logs auf der Konsole angezeigt werden sollen. Es gibt folgende Kategorien: DEBUG, FATAL, ERROR, WARN, INFO
* **enableW3cMode**  
Legt Fest ob der webdriver im W3C Standard konformen Modus laufen soll.

#### CLI-Parameter für die Authentifizierung:
* **username**  
Benutzername mit dem sich gegenüber Applikationen wie Jira authentifiziert wird.
* **password**  
Das dazugehörige Passwort.
* **auth_token**  
Alternativ zur klassischen Anmeldung mit Benutzername und Passwort kann auch ein Auth-Token verwendet werden. Dieser wird nur von bestimmten Anwendungen wie zum Beispiel Jira unterstützt.
****

### Resources:
* [Java 11](https://docs.oracle.com/en/java/javase/11/docs/api/index.html)
* [Object-oriented programming (OOP)](https://en.wikipedia.org/wiki/Object-oriented_programming)
* [Selenium](https://www.seleniumhq.org/)
* [Cucumber](https://cucumber.io/docs/cucumber/)
* [Maven](https://maven.apache.org/)
* [Jira API Doc](https://docs.atlassian.com/software/jira/docs/api/REST/9.3.1/#api/2/)
* [Xray API Doc](https://docs.getxray.app/display/XRAY/v1.0)
* [IntelliJ IDEA](https://www.jetbrains.com/idea/download/#section=windows)