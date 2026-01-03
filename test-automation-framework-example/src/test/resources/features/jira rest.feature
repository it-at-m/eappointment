# language: de
Funktionalität: Jira REST

    # Zeigt die Funktionsweise der Jira REST API

  @jira-rest
  Szenario: Vorgangsdaten auslesen
    Angenommen man hat sich erfolgreich mit Access Token authentifiziert.
    Wenn man nun über die Jira REST API die Daten zu Vorgang "MZM-2288" anfragt.
    Dann sollte man eine Datei mit Namen "MZM-2288.json" erhalten haben.

  @jira-rest
  Szenario: Auslesen von Übergangsdaten
    Angenommen man hat sich erfolgreich mit Access Token authentifiziert.
    Wenn man nun über die Jira REST API die Status-Übergänge zu Vorgang "MZM-2288" anfragt.
    Dann sollte man eine Datei mit Namen "MZM-2288_transition.json" erhalten haben.