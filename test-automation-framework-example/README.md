# Beispiel Projekt für ATAF
Dieses Projekt soll anhand eines einfachen Beispiels zeigen wie Sie ATAF in Ihr Testautomatisierungsprojekt integrieren können.

Bevor Sie weiterlesen sollten Sie die [ATAF Dokumentation](https://git.muenchen.de/test-tool-projects/agile-test-automation-framework/java/test-automation-framework/-/blob/main/README.md?ref_type=heads) gelesen haben.

## Zum Inhalt
- Das Beispiel Projekt verwendet die Komponenten **core** und **web**.
- Als Testframework wird TestNG verwendet. In diesem Fall **BasicTestNGRunner**.
- Ein einfaches Cucumber Szenario samt der [Steps](https://cucumber.io/docs/cucumber/api/?lang=java#steps) ist mit **WiLMA Tour starten** ([MZM-2289](https://jira.muenchen.de/browse/MZM-2289)) bereits umgesetzt.
- Außerdem wird gezeigt wie das bewährte [Page-Object-Pattern](https://docs.digital.ai/bundle/TE/page/design_patterns_-_page_object_model.html) angewendet wird.
- Mit dabei sind auch zwei ([MZM-2315](https://jira.muenchen.de/browse/MZM-2315)) und [MZM-2316](https://jira.muenchen.de/browse/MZM-2316)) Cucumber Szenarien, die zeigen wie man mit der JiraClient Klasse die Jira REST API benutzt. Das ist nützlich, wenn man die Jira Integration effektiv nutzen möchte.

## Wie ausführen?
Ich verwende Maven und in diesem Fall das surefire Plugin. Wie Sie es ansteuern können Sie selbst entscheiden. Ich bevorzuge die CLI. Deshalb folgende Befehle:

Achtung! Passen Sie die Zeichenfolgen ```<Mein Jira Auth Token>```, ```<LDAP Benutzername>``` und ```<LDAP Passwort>``` bei Übernahme unbedingt an! Jira Access Token können Sie über Ihr [Profil in Jira](https://jira.muenchen.de/secure/ViewProfile.jspa?selectedTab=com.atlassian.pats.pats-plugin:jira-user-personal-access-tokens) erstellen. 

### Cucumber Szenarien ausführen (lokale feature Dateien)
```bash
mvn clean test "-Dcucumber.filter.tags=@wilma or @jira-rest" -Dtaf.testDataEncryptionPassword=TESTTEST -Dauth_token=<Mein Jira Auth Token> -Dsso.username=<LDAP Benutzername> -Dsso.password=<LDAP Passwort> -DforkCount=0 -DreuseForks=false
```
Dies führt alle Cucumber Szenarien unter ```src/test/resources/features```, die das tag **@wilma** oder **@jira-rest** enthalten aus. Damit der Test funktioniert müssen Sie gültige LDAP Credentials eingeben. Oder alternativ in der property-Datei **testautomation.properties** die property **testautomation.boolean.useIncognitoMode** auf **false** setzen. 

### Cucumber Szenarien ausführen (Remote)
```bash
mvn clean test -Dtaf.testDataEncryptionPassword=TESTTEST -Dauth_token=<Mein Jira Auth Token> -Dsso.username=<LDAP Benutzername> -Dsso.password=<LDAP Passwort> -Djira.boolean.createTestExecution=true -Djira.testPlanKey=MZM-2290 -Djira.issueKeys=MZM-2289,MZM-2315,MZM-2316 -Djira.filterId=37600 -DforkCount=0 -DreuseForks=false
```
Lädt die Cucumber Szenarien **WiLMA Tour starten** ([MZM-2289](https://jira.muenchen.de/browse/MZM-2289)), **Vorgangsdaten auslesen** ([MZM-2315](https://jira.muenchen.de/browse/MZM-2315)) und **Auslesen von Übergangsdaten** [MZM-2316](https://jira.muenchen.de/browse/MZM-2316)) aus Jira Xray herunter, erstellt eine Testausführung, führt diese auf dem [Selenium Grid](https://selenium.muenchen.de/) aus und schreibt dann das Ergebnis zurück in Jira.
Die Ergebnisse können dann unter Testausführungen im [Testplan MZM-2290](https://jira.muenchen.de/browse/MZM-2290) angesehen werden.

## Code-Formatierung mit Maven Spotless
Dieses Projekt verwendet das Maven-Plugin Spotless, um die automatische Code-Formatierung und Einhaltung eines einheitlichen Code-Stils sicherzustellen.

### Warum Spotless?
- Einheitliche Code-Formatierung für alle Entwickler
- Verbesserte Lesbarkeit und Codequalität
- Automatische Prüfung im CI-Prozess

### Nutzung
Code formatieren (lokal):
```bash
mvn spotless:apply
```
> Formatiert den gesamten Code nach dem definierten Style (z. B. Google Java Format).

Code-Style prüfen (lokal oder im CI):
```bash
mvn spotless:check
```
> Prüft, ob der aktuelle Code dem definierten Style entspricht. Bei Abweichungen schlägt der Check fehl.

### Konfiguration
Die Konfiguration des Code-Styles befindet sich in der Datei ```pom.xml``` unter dem Plugin-Abschnitt für Spotless. Änderungen am Style können dort zentral angepasst werden. Nähere Information zum verwendeten Code-Style finden Sie unter [itm-java-codeformat](https://github.com/it-at-m/itm-java-codeformat).

### Hinweise
- Bitte vor jedem Commit den Code mit mvn spotless:apply formatieren, um unnötige Formatierungs-Diskussionen im Review zu vermeiden.
- Die Spotless-Prüfung ist auch Teil des CI-Prozesses und kann dazu führen, dass Builds fehlschlagen, wenn der Style nicht eingehalten wird.