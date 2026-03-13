#language: de
Funktionalität: ZMS-Citizen-View Service Finder

  @web @zmscitizenview @ZMSKVR-1124 @executeLocally
  Szenario: Service Finder ist auf der Startseite sichtbar
    # Same URL as app fetch via gateway; screenshot proves browser can load JSON from gateway
    Wenn Sie im Browser zur Gateway-URL offices-and-services navigieren.
    Wenn Sie zur Webseite der zmscitizenview navigieren.
    Dann wird der Service Finder auf der Startseite der zmscitizenview angezeigt.

