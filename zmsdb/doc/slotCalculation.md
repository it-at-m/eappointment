
# Berechnung von freien Slots


## Tabellen

### slot

Die Tabelle "slot" enthält alle Zeitschlitze mit der Anzahl der möglichen
Termine für diesen Zeitschlitz.

### slot_hiera

Die Tabelle "slot_hiera" enthält eine Relation der Zeitschlitze zueinander.
Dabei wird über ein Feld "ancestorLevel" angegeben, wieviele Zeitschlitze
dieser von einem anderen Zeitschlitz entfernt ist.
Wenn ein Termin mehr als ein Zeitschlitz benötigt, müssen die folgenden
Zeitschlitze mit gebucht werden. Mittels des Feldes "ancestorID" lassen sich so
die folgenden Zeitschlitze über einen Index ermitteln. Eine Berechnung entfällt
und ermöglicht so einen schnellen Query. Über das Feld "slotID" kann dann
geprüft werden, ob der folgende Zeitschlitz noch frei ist.

### slot_process

Die Tabelle "slot_process" verknüpft einen gebuchten process mit einer slotID.
Auf diese Weise kann ohne weitere Berechnung die Anzahl der process auf einen
slot summiert werden um zu prüfen, ob noch weitere process hinzugefügt werden
können.

### calendarscopes

Dies ist eine temporäre Tabelle, welche die für eine Abfrage zu
berücksichtigenden scopes enthält. Dazu werden die Anzahl der slots für einen
Termin erfasst. Dieser Wert kann für jeden scope abweichen.

## Optimierte Abfrage freier Termine

Für eine Anfrage wird zuerst eine temporäre Tabelle "calendarscopes" erstellt.
Für diese Tabelle wird ein JOIN mit der slot Tabelle hergestellt. Dieser JOIN
erfolgt über scopeID und das Datum, wofür ein INDEX in der Tabelle bereitsteht.
Weiterhin wird ein JOIN auf die Tabelle "slot_hiera" durchgeführt. Über das
Feld "ancestorLevel" kann man so die benötigten Slots einstellen. Somit werden
auch alle Folge-Slots für einen längeren Termin mit ausgewählt.
Als letzten JOIN wird die Tabelle "slot_process" mit einbezogen. Diese
beinhaltet die Information der bereits durch einen process belegten Slots. Die
Summe dieser Tabelle stellt die belegten slots für einen slot Eintrag aus den
Öffnungszeiten dar.
Über eine Hierarchie von Subqueries kann man diese Ergebnisse nun in mehreren
Stufen gruppieren und kann so ein reduziertes Ergebnis auf einzelne Tage
erhalten. Das ist wichtig, da das Datenvolumen bei durchaus einmal 50.000
gebuchten Terminen für einen Abfrage recht hoch ist. Dieses Volumen sollte
nicht über ein Netzwerk übertragen werden.

Eine weitere Optimierung ist über den Status der Slots möglich. Mittels eines
Updates lässt sich der Status eines Slots auf "full" setzen. Somit kann man die
Menge der Slots eingrenzen, wenn man nach freien Terminen sucht. (Wie sieht es
aus mit belegten Tagen, werden diese dann noch rot angezeigt oder weiß?)

## Synchronisierung der Daten

Die Slot-Tabellen sind keine nativen Daten. Diese stellen eine Optimierung in
Form einer Vorberechnung der verfügbaren Zeitschlitze dar.
Bei einer Änderung in den folgenden Tabellen müssen auch die Slot-Tabellen
aktualisiert werden:

* buerger (Matching mit slots)
* feiertage (der entsprechende Tag muss neu berechnet werden)
* oeffnungszeit (die entsprechende Öffnungszeit muss neu berechnet werden)
* standort (Einstellungen zur Vorausbuchung wirken sich auf Öffnungszeiten aus)

Zudem muss jede Stunde geprüft werden, ob nicht neue Termine freigegeben werden
können.

### buerger

Die Tabelle "buerger" verfügt über ein Feld "updateTimestamp" genauso wie die
Tabelle "slot_hiera". Von der "buerger"-Tabelle aus kann ein LEFT JOIN auf
"slot_hiera" erzeugt werden. Ist der Wert in slot_hiera NULL oder ist der
updateTimestamp kleiner als in "buerger" muss die Tabelle aktualisiert werden.
Dieser Test muss einmal die Minute durchgeführt werden.

### feiertage

Freie Tage können entweder global gelten oder für eine bestimmte Behörde. Gilt
der Freie Tag global und in der Slot-Tabelle ist ein Eintrag zu diesem Tag
vorhanden, dann müssen alle Öffnungszeiten zu diesem Tag erneut aufgebaut
werden. Gilt der freie Tag nur für eine Behörde, können die Standorte der
Behörde dazu verwendet werden, nur die Slots dieser Behörde auszuwählen. Das
schränkt die Anzahl der Öffnungszeiten, die aktualisiert werden müssen, ein.

### oeffnungszeit

Wenn eine Öffnungszeit geändert wird, müssen alle Slots mit dieser ID erst
einmal in den Status "cancelled" versetzt werden. Beim Update wird für jeden
Slot geschaut ob dieser bereits existiert. Ist ein existenter Slot gültig, wird
dieser auf "free" gesetzt.
Bevor dieser aufwändige Prozess gestartet wird, sollte per Start- und
Ende-Datum geprüft werden, ob die Öffnungszeit überhaupt Slots freigeben wird.

### standort

Wenn ein Standort verändert wird, sollten alle Öffnungszeiten des Standortes
aktualisiert werden.

### Stündliche Aktualisierung

Öffnungszeiten sollen für einen Tag nicht um Mitternacht freigegeben werden,
sondern zu der Beginn-Zeit, in der die Öffnungszeit gilt. Daher soll einmal
stündlich geprüft werden, ob eine Öffnungszeit neue Slots freigeben könnte. Das
bedeuet, dass der Zeitraum, in der in der Zukunft ein Termin gebucht werden
kann mit einem Tag zusammenfällt, an dem die Öffnungszeit gültig ist. Wenn das
der Fall ist und die Öffnungszeit ein Start-Datum hat, welches der aktuellen
Stunde entspricht, wird für die Öffnungszeit eine neue Slot-Berechnung
durchgeführt.

## Zusammenspiel mit dem ZMS1

Durch die minütlichen Synchronisierungen mit den Terminen kann es zu kleineren
Überbuchungen kommen. Daher sollte im Idealfall die Buchung über das Internet
komplett über das ZMS2 erfolgen. Im ZMS2 kann man dafür sorgen, dass die
Tabelle "slot_hiera" bei einer Aktualisierung eines Termins mit geschrieben
wird. Im Tresen des ZMS1 sind Überbuchungen auch so möglich. Dort spielt die
Abweichung von bis zu einer Minute keine relevante
Rolle.
