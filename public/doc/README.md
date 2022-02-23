# Wie funktioniert die Open Api Definition
## Version 2.0

* Unter /public/_test befindet sich die dist Variante von swagger-ui. Angepasst wurde hier der Zugriff auf die swagger.yaml im entsprechenden Pfad.

* Unter /public/doc befinden sich die schema aus zmsentities. Diese werden in der package.json per npm aus dem repo zmsentities geholt und ein symbolischer link verweist hier auf den entsprechenden Ordner unter node_modules/bo-zmsentities/schema/dereferenced

* Unter /bin befindet sich eine build_swagger.js Datei. Diese wird über ```npm run doc``` ausgeführt und validiert die vorhandene swagger.yaml Datei. Wenn diese valide ist werden aus der routing.php die open api annotionen gelesen und aus den yaml Dateien unter ./partials die restlichen Informationen wie Info, Definitions, Version und Tags zu einer vollständigen swagger.yaml zusammengestellt. 

* Um über redoc oder die open api dokumentation auf alle Pfade aufgelöst zugreifen zu können, muss aus der swagger.yaml eine aufgelöste swagger.json erstellt werden. Dies passiert über die swagger cli mit einem Aufruf von ```bin/doc```. Dieser Aufruf führt den oben genannten npm Befehl aus und erstellt folgend eine vollständige swagger.json.

* Wenn eine neue entity definition hinzukommen sollte muss hier die Referenz unter definitions gesetzt werden.