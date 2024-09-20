# Contributing

This is section is only useful if you want to contribute on this project.

## Basic Guidelines

### Use an IDE

Please use an IDE. This will make development with the styling and other guidelines easier.
We recommend the IntelliJ IDE, the Community Edition is enough. Something like Visual Studio Code is
also possible, but not the best for Java.

### Always test your changes

For all of your changes at least start the service. If your changes have impact on the frontend,
please also start the frontend and make sure everything works as intended.

### Do not make large changes before discussing them first

If you either want to add a new feature or do some refactoring, please contact us via Mail.
[Thomas](mailto:thomas.fink@muenchen.de) or [Clemens](mailto:ex.schuetze@muenchen.de)

## Style Guidelines

We use automated Styling [spotless](https://github.com/diffplug/spotless). Spotless is configured with
the [google-java-format](https://github.com/google/google-java-format). The spotless check is running automatically with
maven verify and will fail if the style does not match. It is recommended to run ```mvn spotless:apply``` for every
push. We strongly recommend the google-java-format Plugin from the IntelliJ Marketplace for local styling.

---

# Mitwirken

Dieser Bereich ist nur für diejenigen, die sich an dem Projekt beteiligen möchten.

## Grundlegende Richtlinien

### Benutze eine IDE

Bitte nutze eine moderne IDE. Diese sorgt dafür, dass die Entwicklung in den Style- und sonstigen Richtlinien
vereinfacht wird. Wir empfehlen die IntelliJ IDE, hier reicht die Community Edition aus. Visual Studio Code kann auch
gerne genutzt
werden. Ist aber für Java Projekte nicht so sehr geeignet.

### Teste alle Änderungen

Für jede Änderung, die gemacht wird, sollte der Service gestartet werden. Wenn die Änderung auch das Frontend betrifft,
dann sollte alles zusammen gestartet werden, um sicherzustellen, dass alles so funktioniert wie beabsichtigt.

### Keine großen Änderungen ohne Absprache

Wenn du beabsichtigst ein neues größeres Feature oder ein Refactoring zu machen, dann kontaktiere uns bitte zuerst.
Am besten geht dies über Mail: [Thomas](mailto:thomas.fink@muenchen.de) oder [Clemens](mailto:ex.schuetze@muenchen.de)

## Style Richtlinien

Wir nutzen automatisches Styling mittels [spotless](https://github.com/diffplug/spotless). Spotless is
mit [google-java-format](https://github.com/google/google-java-format)
konfiguriert. Die Überprüfung auf die Stylerichtlinien wird bei jedem maven verify ausgeführt. Dieser Schritt schlägt
fehl, sollte das Styling nicht korrekt sein. Es ist zu empfehlen vor jedem push einmal ```mvn spotless:apply```
auszuführen. Dazu empfehlen wir das
google-java-format Plugin im IntelliJ Marktplatz, um hier schon die korrekte Formatierung zu nutzen.
