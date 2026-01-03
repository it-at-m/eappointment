# Agiles Java Testautomatisierungs-Framework

Ein robustes und flexibles Testautomatisierungs-Framework, entwickelt in Java 17, das das automatisierte Testen mit
Cucumber und einem Testframework (TestNG oder JUnit) vereinfacht.

## Inhaltsverzeichnis

1. [Einführung](#einführung)
2. [Erste Schritte](#erste-schritte)
3. [Verwendung](#verwendung)
4. [Tests ausführen](#tests-ausführen)
5. [Konfiguration](#konfiguration)
6. [Berichterstattung](#berichterstattung)
7. [Beitragen](#beitragen)
8. [Lizenz](#lizenz)
9. [Kontakt](#kontakt)

## Einführung

Dieses Testautomatisierungs-Framework ist darauf ausgelegt, Ihnen schnell die Einrichtung und Durchführung
automatisierter Tests für Ihre Anwendungen zu ermöglichen.
Es bietet:

- Unterstützung sowohl für BDD-Tests mit Cucumber als auch für traditionelle Testfälle mit TestNG und JUnit.
- Nahtlose Integration mit beliebten Testbibliotheken.
- Einfach zu konfigurierende Runner für TestNG und JUnit.
- Um eine Integration zu Jira Xray, als Testmanagement Werkzeug, zu erreichen werden zudem die vorhandenen REST
  Schnittstellen von Jira und Xray verwendet.

## Erste Schritte

### Voraussetzungen

Stellen Sie sicher, dass die folgenden Programme installiert sind:

- Java SDK mindestens Version 17
- Maven
- Eine IDE wie IntelliJ IDEA oder Eclipse

### Installation

Fügen Sie folgende maven dependencies Ihrem pom.xml, je nach Bedarf, hinzu.
Ersetzen Sie ${version.ataf} mit der von Ihnen bevorzugten Version.
Das Weglassen des version tags führt dazu, dass Sie immer die aktuelle Version verwenden.
Dies wird nicht empfohlen.

#### Paket core:

Zwingende Voraussetzung für die Verwendung von ATAF.
Enthält notwendige Funktionen für Cucumber und die Jira Integration.
Außerdem nützliche Klassen rund um Testdaten und Properties.

```xml
<!-- https://artifactory.muenchen.de/ui/native/mvn-release-local/de/muenchen/ataf/java/core -->
<dependency>
    <groupId>de.muenchen.ataf.java</groupId>
    <artifactId>core</artifactId>
    <version>${version.ataf}</version>
</dependency>
```

#### Paket rest, optional:

Paket enthält Klassen für API Tests.

```xml
<!-- https://artifactory.muenchen.de/ui/native/mvn-release-local/de/muenchen/ataf/java/rest -->
<dependency>
    <groupId>de.muenchen.ataf.java</groupId>
    <artifactId>rest</artifactId>
    <version>${version.ataf}</version>
</dependency>
```

#### Paket web, optional:

Paket enthält Klassen für browserbasierte Tests.

```xml
<!-- https://artifactory.muenchen.de/ui/native/mvn-release-local/de/muenchen/ataf/java/web -->
<dependency>
    <groupId>de.muenchen.ataf.java</groupId>
    <artifactId>web</artifactId>
    <version>${version.ataf}</version>
</dependency>
```

#### Projekt bauen

Nach der Konfiguration Ihres maven Projekts pom.xml, können Sie es mit folgendem Befehl überprüfen, ob das Bauen
funktioniert:

```bash
mvn clean package
```

#### Weitere Installationshinweise

Weitere Installationshinweise können Sie auf der offiziellen [Confluence Seite](https://confluence.muenchen.de/x/QwaoH)
von ATAF finden.

### Grundlegende Konfiguration

Für die Konfiguration verwendet das Framework property Dateien. Diese müssen im resources Order abgelegt werden.
Beispielpfad:

```txt
src/test/resources
```

#### cucumber.properties

Konfiguriert das Verhalten von Cucumber.
Weitere Details finden Sie
auf [GitHub](https://github.com/cucumber/cucumber-jvm/blob/main/cucumber-core/src/main/resources/io/cucumber/core/options/USAGE.txt).
Hier ein Beispiel:

```properties
# Deaktiviert das Veröffentlichen von Testberichten.
cucumber.publish.enabled=false

# Unterdrückt die Ausgabe von zusätzlichen Informationen zur Veröffentlichung.
cucumber.publish.quiet=true

# Angabe des Pfades, wo sich die Cucumber feature Dateien befinden.
cucumber.features=src/test/resources/features

# Angabe von Plugins hier generiert sowohl einen JSON- als auch einen HTML-Bericht.
cucumber.plugin=json:target/cucumber.json,html:target/site/cucumber-pretty

# Gibt die Pakete (Komma separiert) an, wo sich Cucumber Step Klassen befinden.
cucumber.glue=test.automation.framework.steps,ataf.web.steps
```

#### jira.properties

Diese Properties werden für die Jira Integration verwendet.
An manche der Werte kommt man nur über die Jira REST-API, mehr dazu in [Confluence](https://confluence.muenchen.de/x/UQaDJ).
Hier ein Beispiel:

```properties
# Die Projekt-ID als Ziffernfolge.
jira.test.execution.project.id=12345678

# Verwendeter Summary Text, der bei generierten Testausführungen gesetzt wird.
jira.test.execution.summary=Automatisch generierte Testausführung

# Gibt die ID des Vorgangstypen an, der für die Testausführung zutrifft.
jira.test.execution.issuetype.id=10202

# Label welches vom Framework verwendet wird, um Testausführungen zu identifizieren.
jira.test.execution.labels.automation.label=automatisiert

# Umgebung, die für den Test verwendet wird. Betrifft nur generierte Testausführungen.
jira.test.execution.test.environment=test

# Die Transition-ID, um einen Vorgang in Bearbeitung zu setzen.
jira.test.execution.transition.id.in.progress=1

# Label welches vom Framework verwendet wird, um Testausführungen, die bereits ausgeführt werden, zu identifizieren.
jira.test.execution.labels.in.progress=inAusführung

# Die Transition-ID, um einen Vorgang fertig zu setzen.
jira.test.execution.transition.id.done=3
```

#### log4j2-test.properties

Konfiguriert das Verhalten des Loggers.

```properties
# ------------------------------------------
# Liste der Appender (Ausgabeziele für Logs)
# Hier definieren wir zwei Appender:
# Einen für die Konsolenausgabe und einen für thread-spezifische Logdateien.
# ------------------------------------------
appenders=console,threadFile

# ------------------------------------------
# 1. Console Appender
# Dieser Appender gibt Lognachrichten auf der Konsole aus.
# Nützlich für die Fehlersuche während der Entwicklung oder schnelle Loganalysen.
# ------------------------------------------
appender.console.type=Console
# Name des Appenders, wird zur Referenzierung im rootLogger oder anderen Konfigurationen verwendet.
appender.console.name=STDOUT
# Das Layout definiert, wie die Lognachrichten formatiert werden. Hier wird ein Muster-Layout genutzt.
appender.console.layout.type=PatternLayout
# Das Muster definiert den Aufbau der Lognachricht.
# Beispiel: [thread-id %T] 2025-01-01 12:00:00 INFO ClassName:42 - Lognachricht
appender.console.layout.pattern=[thread-id %T] %d{yyyy-MM-dd HH:mm:ss} %-5p %c{1}:%L - %m%n
# Stellt sicher, dass die Konsolenausgabe UTF-8 verwendet, um Probleme mit Sonderzeichen zu vermeiden.
appender.console.layout.charset=UTF-8

# ------------------------------------------
# 2. RollingFile Appender für thread-spezifische Logs
# Dieser Appender schreibt Logs in Dateien.
# Jeder Thread erstellt eine eigene Logdatei, benannt nach der thread-spezifischen Kontextvariablen 'scenario'
# oder 'default', falls keine Variable gesetzt ist.
# RollingFile bedeutet, dass Logdateien nach einer definierten Richtlinie (z. B. zeitbasiert) archiviert werden.
# ------------------------------------------
appender.threadFile.type=RollingFile
# Name des Appenders zur Referenzierung im rootLogger.
appender.threadFile.name=ThreadLog
# Der Pfad und Name der aktuellen Logdatei.
# Die Variable ${ctx:scenario} wird für thread-spezifische Logs verwendet.
# Falls keine Kontextvariable 'scenario' gesetzt ist, wird 'default.log' verwendet.
appender.threadFile.fileName=logs/${ctx:scenario:-default}.log
# Muster für die archivierten Logdateien. Alte Logs werden nach Datum archiviert und komprimiert.
appender.threadFile.filePattern=logs/${ctx:scenario:-default}-%d{yyyy-MM-dd}.log.gz
# Layout für die Lognachrichten in der Datei, ähnlich dem Konsolenlayout.
appender.threadFile.layout.type=PatternLayout
appender.threadFile.layout.pattern=[thread-id %T] %d{yyyy-MM-dd HH:mm:ss} %-5p %c{1}:%L - %m%n
# UTF-8 sorgt dafür, dass Sonderzeichen korrekt in der Logdatei gespeichert werden.
appender.threadFile.layout.charset=UTF-8
# Richtlinien für die Rotation der Logdateien.
appender.threadFile.policies.type=Policies
# Zeitbasierte Richtlinie: Die Logdatei wird jeden Tag um Mitternacht rotiert.
appender.threadFile.policies.time.type=TimeBasedTriggeringPolicy

# ------------------------------------------
# Root Logger Konfiguration
# Der Root Logger definiert das Standardverhalten für alle Logger in der Anwendung.
# ------------------------------------------
# Legt das minimale Log-Level fest. Nur Nachrichten ab diesem Level oder höher werden ausgegeben.
# Verfügbare Level: TRACE < DEBUG < INFO < WARN < ERROR < FATAL
rootLogger.level=info
# Gibt an, welche Appender für den Root Logger verwendet werden sollen.
rootLogger.appenderRefs=stdout,threadLog
# Referenz zum Konsolen-Appender, der oben definiert wurde.
rootLogger.appenderRef.stdout.ref=STDOUT
# Referenz zum thread-spezifischen Datei-Appender, der oben definiert wurde.
rootLogger.appenderRef.threadLog.ref=ThreadLog
```

## Verwendung

### Schreiben von Cucumber-Tests

Erstellen Sie Cucumber Szenarien, entweder in Jira oder legen Sie diese als feature Dateien direkt in Ihrem repository
ab. Achten Sie auch darauf für das jeweilige Paket die Cucumber tags web oder rest zu setzen. Dies sorgt dafür das die Korrekte Hook Klasse angesprochen wird. In Jira setzen sie tags als Stichwort zu den Testfallvorgängen.
Beispiel:

```gherkin
Feature: Login-Funktionalität

  @smoke @web
  Scenario: Erfolgreiches Login mit gültigen Anmeldedaten
    Gegeben sei der Benutzer auf der Login-Seite
    Wenn der Benutzer gültige Anmeldedaten eingibt
    Dann sollte der Benutzer zum Dashboard weitergeleitet werden
```

Nun müssen Sie jeden Cucumber step noch in einer Ihrer step Klassen implementieren:
```java
import io.cucumber.java.de.Dann;
import io.cucumber.java.de.Gegebensei;
import io.cucumber.java.de.Wenn;

@Gegebensei("der Benutzer auf der Login-Seite")
public void gegeben_sei_der_benutzer_auf_der_login_seite() {
    // Code für Vorbedingung hinzufügen
}

@Wenn("der Benutzer gültige Anmeldedaten eingibt")
public void wenn_der_benutzer_gueltige_anmeldedaten_eingibt() {
    // Code für Aktionen hinzufügen
}

@Dann("sollte der Benutzer zum Dashboard weitergeleitet werden")
public void dann_sollte_der_benutzer_zum_dashboard_weitergeleitet_werden() {
    // Code für Überprüfung hinzufügen
}
```

### Schreiben von TestNG/JUnit-Tests
Sie können auch weiterhin Testklassen, unabhängig von Cucumber, unter src/test/java erstellen:

```java
import org.testng.annotations.Test;

public class LoginTest {

    @Test
    public void testValidLogin() {
        // Ihr Testcode hier
    }
}
```

### Provider Konfiguration
Surefire wählt normalerweise automatisch den zu verwendenden Test-Framework-Provider basierend auf der Version von TestNG/JUnit, die sich im Klassenpfad Ihres Projekts befindet. In manchen Fällen kann es wünschenswert sein, diese Auswahl manuell zu überschreiben. Dies kann durch Hinzufügen des erforderlichen Providers als Abhängigkeit zum Surefire-Plugin erfolgen.
___[Quelle](https://maven.apache.org/surefire/maven-surefire-plugin/examples/providers.html)___

Beispiel für TestNG:
```xml
<!-- https://mvnrepository.com/artifact/org.apache.maven.plugins/maven-surefire-plugin -->
<plugin>
    <groupId>org.apache.maven.plugins</groupId>
    <artifactId>maven-surefire-plugin</artifactId>
    <version>3.2.5</version>
    <configuration>
        <trimStackTrace>false</trimStackTrace>
    </configuration>
    <dependencies>
        <!-- https://mvnrepository.com/artifact/org.apache.maven.surefire/surefire-testng -->
        <dependency>
            <groupId>org.apache.maven.surefire</groupId>
            <artifactId>surefire-testng</artifactId>
            <version>3.3.1</version>
        </dependency>
    </dependencies>
</plugin>
```

### Test Runner Konfiguration
Das Framework kommt mit drei vorkonfigurierten Test Runnern:
- BasicJUnitRunner, für JUnit
- BasicTestNGRunner, für TestNG
- ParallelTestNGRunner, TestNG Cucumber Szenario parallele Testausführung

Haben Sie sich für einen Test Runner entschieden sollten Sie Ihren Runner lediglich erweitern:

```java
import ataf.core.runner.BasicTestNGRunner;

public class TestRunner extends BasicTestNGRunner {
    
}
```

Sie können aber auch Ihre eigene Implementierung verwenden, hierzu müssen Sie lediglich folgende Methoden implementieren:

```java
import ataf.core.utils.RunnerUtils;

public class CustomTestRunner {
    
    public void beforeTestSuite() {
        RunnerUtils.setupTestSuite();
    }
    
    public void afterTestSuite() {
        RunnerUtils.tearDownTestSuite();
    }
}
```

## Tests ausführen

### Ausführen von Cucumber-Tests
Um Cucumber-Szenarien auszuführen:

```bash
mvn clean test -Dcucumber.filter.tags=@smoke
```

### Ausführen mit TestNG
Um Tests mit TestNG auszuführen:

```bash
mvn clean test -DsuiteXmlFile=testng.xml
```

### Ausführen mit JUnit
Um Tests mit JUnit auszuführen:

```bash
mvn clean test
```

## Konfiguration
Anpassen der Testeinstellungen, funktioniert ebenfalls zum großen Teil über eine property Datei:

### testautomation.properties

```properties
# Der Browser, der für die Testautomatisierung verwendet werden soll (mögliche Werte firefox, chrome, edge)
testautomation.browser=firefox

# Die Version des Browsers, der für die Tests verwendet wird
testautomation.browserVersion=128.4.0

# Die URL des Selenium Grid-Hubs, der verwendet wird, um die Tests remote auszuführen
testautomation.seleniumGridUrl=https://selenium.muenchen.de/wd/hub

# Gibt an, ob ein Proxy für die Tests verwendet werden soll (true/false)
testautomation.boolean.useProxy=true

# Die Adresse des Proxys, der für die Testverbindungen verwendet wird
testautomation.proxyAddress=10.158.0.77

# Der Port des Proxys, der für die Testverbindungen verwendet wird
testautomation.int.proxyPort=80

# Eine durch Kommas getrennte Liste von Domains, die den Proxy nicht verwenden sollen
testautomation.noProxy=mein-beispiel-dienst.muenchen.de

# Die Standardzeit in Millisekunden, die auf das Laden von Skripten und Seiten gewartet wird
testautomation.long.defaultScriptAndPageLoadTime=120000

# Die Standardzeit in Millisekunden für implizite Wartezeiten, die der WebDriver auf ein Element wartet, bevor er eine Ausnahme auslöst
testautomation.long.defaultImplicitWaitTime=250

# Die Standardzeit in Sekunden für explizite Wartezeiten, die der WebDriver auf eine bestimmte Bedingung wartet, bevor er eine Ausnahme auslöst
testautomation.int.defaultExplicitWaitTime=60

# Gibt an, ob der Browser im Inkognito/Private-Modus gestartet werden soll (true/false)
testautomation.boolean.useIncognitoMode=true

# Das Log-Level für die Testautomatisierung (mögliche Werte DEBUG, INFO, WARN, ERROR)
testautomation.logLevel=INFO

# Die Breite des Browserfensters in Pixeln, die für die Tests verwendet wird
testautomation.int.screenWidth=1920

# Die Höhe des Browserfensters in Pixeln, die für die Tests verwendet wird
testautomation.int.screenHeight=1080

# Der Verzeichnis-Pfad, in dem die Firefox-Erweiterungen für die Tests gespeichert sind
testautomation.firefoxExtensionDirectory=./src/test/resources/extensions/firefox/
```

### Weitere wichtige properties
Folgende properties sollten nur beim Aufruf des Tests übergeben werden:

```properties
#Passwort welches verwendet wird um zur Laufzeit Testdaten zu ver- und entschlüsseln 
taf.testDataEncryptionPassword=<Mein Testdaten Passwort>

#Auth Token des technischen Jira Nutzers, wird benötigt für die Kommunikation zu Jira Xray
auth_token=<Mein Jira Auth Token>

#LDAP Benutzername des technischen Nutzers, wird als Fallback für Jira Kommunikation verwendet, außerdem wird so der assignee für die Testausführungen ermittelt 
username=<LDAP Benutzername>

#LDAP Passwort des technischen Nutzers, wird als Fallback für Jira Kommunikation verwendet
password=<LDAP Passwort>
```
***Da es sich um Credentials handelt dürfen diese nie im repository abgelegt werden!***

Sieht dann auf der CLI so aus:
```bash
mvn clean test -Dtaf.testDataEncryptionPassword=<Mein Testdaten Passwort> -Dauth_token=<Mein Jira Auth Token> -Dusername=<LDAP Benutzername> -Dpassword=<LDAP Passwort>
```

Weitere Informationen zur Verschlüsselung finden Sie im [Confluence](https://confluence.muenchen.de/x/UQvbLw), außerdem sollten Sie sich die [Testdaten Verschlüsseler App](https://git.muenchen.de/test-tool-projects/agile-test-automation-framework/java/test-data-encryptor) ansehen. Damit können Sie Text und ganze Dateien einfach verschlüsseln und sicher mit ATAF verwenden. 

### Umgebungen und Systeme konfigurieren
Damit das Framework ordentlich funktioniert, muss mindestens eine Umgebung in Ihrem Code definiert werden.

### 1. Möglichkeit statischer Aufruf in der TestRunner Klasse:
### TestRunner Klasse
```java
import ataf.core.runner.BasicTestNGRunner;
import test.automation.framework.data.TestData;

public class TestRunner extends BasicTestNGRunner { 
    static {
        TestData.init();
    }
}
```

### TestData Klasse mit Test- und Integrationsumgebung
```java
import ataf.core.data.Environment;
import ataf.core.logging.ScenarioLogManager;

public class TestData {
    
    //Environments
    public static final Environment TEST_ENVIRONMENT = new Environment("Environment", "TEST");
    public static final Environment INTEGRATION_ENVIRONMENT = new Environment("Environment", "INT");

    public static void init() {
        ScenarioLogManager.getLogger().info(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>Start of initializing test data>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");

        ScenarioLogManager.getLogger().info("Adding systems for TEST");
        TEST_ENVIRONMENT.addSystem("Mein-Beispiel-System-1", "https://mein-beispiel-system-1-test.muenchen.de/");
        TEST_ENVIRONMENT.addSystem("Mein-Beispiel-System-2", "https://mein-beispiel-system-2-test.muenchen.de/");
        TEST_ENVIRONMENT.addSystem("Mein-Beispiel-System-3", "https://mein-beispiel-system-3-test.muenchen.de/");

        ScenarioLogManager.getLogger().info("Adding systems for INT");
        INTEGRATION_ENVIRONMENT.addSystem("Mein-Beispiel-System-1", "https://mein-beispiel-system-1-int.muenchen.de/");
        INTEGRATION_ENVIRONMENT.addSystem("Mein-Beispiel-System-2", "https://mein-beispiel-system-2-int.muenchen.de/");
        INTEGRATION_ENVIRONMENT.addSystem("Mein-Beispiel-System-3", "https://mein-beispiel-system-3-int.muenchen.de/");

        ScenarioLogManager.getLogger().info("<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<Finished initializing test data<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
    }
}
```

### TestData Klasse mit leerer Umgebung
Verwenden Sie keine Umgebungen in Ihrer Test-Strategie, weil Sie zum Beispiel nur auf den Produktivsystem testen, dann können Sie auch einen leeren Wert übergeben.

```java
import ataf.core.data.Environment;

public class TestData {
    public static final Environment NO_ENVIRONMENT = new Environment("Environment", "");

    public static void init() {
        // Required for init order of statics!
    }
}
```

### 2. Möglichkeit annotierte Methode:
Man kann sich den Testzyklus von TestNG zunutze machen in dem man die init() Methode mit ``@BeforeSuite(alwaysRun = true, dependsOnGroups = "beforeTestSuite")`` annotiert. Dieser Code wird nun unmittelbar nach ``ataf.core.runner.BasicTestNGRunner.beforeTestSuite`` ausgeführt. Zum Beispiel ist dies nützlich, wenn man innerhalb seiner TestData Klasse Property Werte benötigt. 

```java
import ataf.core.data.Environment;
import ataf.core.logging.ScenarioLogManager;

public class TestData {
    
    //Environments
    // define your environments here

    @BeforeSuite(alwaysRun = true, dependsOnGroups = "beforeTestSuite")
    public void init() {
        ScenarioLogManager.getLogger().info(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>Start of initializing test data>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");
        // initialize your environments with systems here         
        ScenarioLogManager.getLogger().info("<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<Finished initializing test data<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");
    }
}
```

## Berichterstattung

### Berichte erstellen
Nach der Testausführung werden Berichte im Verzeichnis target/surefire-reports generiert. Sie finden:
- HTML-Bericht: Eine detaillierte Übersicht über die Testausführung.
- JUnit XML-Bericht: XML-Format zur Integration in CI-Tools.

Außerdem falls Sie die Jira Integration verwenden, wird direkt Ihre Jira Xray Testausführung mit den Ergebnissen der Tests aktualisiert.

## Beitragen
Beiträge sind willkommen! Bitte lesen Sie die [CONTRIBUTING.md](https://git.muenchen.de/test-tool-projects/agile-test-automation-framework/java/test-automation-framework/-/blob/main/CONTRIBUTING.md?ref_type=heads) für Richtlinien, wie Sie loslegen können.

## Lizenz
Dieses Projekt steht unter der MIT-Lizenz - öffnen Sie die [LICENSE](https://git.muenchen.de/test-tool-projects/agile-test-automation-framework/java/test-automation-framework/-/blob/main/LICENSE?ref_type=heads) Datei für Details.

## Kontakt
Für Fragen, Probleme oder Vorschläge, wenden Sie sich bitte über unser [Jira](https://jira.muenchen.de/projects/ATAF/summary) an uns.