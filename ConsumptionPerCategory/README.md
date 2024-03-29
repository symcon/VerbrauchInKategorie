# Verbrauch in Kategorie
Berechnet den Verbrauch in Prozent pro frei wählbarer Kategorie nach angegebener Start- und Enddatum. Die Berechnung basiert auf der täglichen Aggregation.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Auswahl mehrere Variablen
* Freitext für Kategorie 

### 2. Voraussetzungen

- IP-Symcon ab Version 6.3

### 3. Software-Installation

* Über den Module Store das 'Verbrauch in Kategorie'-Modul installieren.

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'Verbrauch in Kategorie'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name                            | Beschreibung
------------------------------- | ------------------
Intervall                       | Intervall, in welchem die Berechnung ausgeführt wird. 0 = Deaktiviert
Quellen                         | Liste der Variablen und Kategorien

### 5. Statusvariablen

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

Name      | Typ     | Beschreibung
--------- | ------- | ------------
Kategorie | Float   | Pro Kategorie wird eine Variable angelegt, in welchem der Prozentuale Verbrauch angezeigt wird. Der Kategoriename wird intern auf A-Z, a-z, 0-9 und _ (Unterstrich) reduziert. Das bedeutet, dass die Kategorie "Test" und "Test €" ebenfalls auf "Test" kategorisiert werden.   
Startzeit | Integer | Datum von wann die Berechnung starten soll
Endzeit   | Integer | Datum bis wann die Berechnung gehen soll

### 7. PHP-Befehlsreferenz

`boolean VIK_CalculateConsumption(integer $InstanzID);`
Erklärung der Funktion.

Beispiel:
`VIK_CalculateConsumption(12345);`