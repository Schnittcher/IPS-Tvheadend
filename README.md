# IPS-Tvheadend
Mit diesem Modul ist es möglich, einen Tvheadend Server in IPS zu überwachen.


**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang)  
2. [Installation](#2-installation) 

## 1. Funktionsumfang 
* Starten und Herunterfahren des Tvheadend Servers
* Ansicht der Verbindungen
* Ansicht der Subscriptions
* Ansicht der nächsten Aufnahme
    * Auf welchem Kanl wird aufgenommen
    * Titel der Aufnahme
    * Start- und Endzeit der Aufnahme
 
## 2. Installation

### Einrichtung in IP-Symcon
Github Repository in IP-Symcon über **Kerninstanzen -> Modules -> Hinzufügen** einrichten

`https://github.com/Schnittcher/IPS-Tvheadend.git` 

### Einrichtung der Instanzen

#### IPS-Tvheadend
Die Tvheadend Instanz wird im Objektbaum erzeugt.

Feld | Erklärung
------------ | -------------
IP-Adresse der TVH Server | Hier die IP-Adresse des Tvheadend Servers eintragen
Port TVH Webinterfaces | Hier wird der Port vom Webinterface angegeben, Default ist 9981
Mac Adresse des TVH Servers | Hier wird die MAC Adresse des Tvheadend Server eingetragen, damit dieser per WOL gestartet werden kann.
Broadcast Adresse des Netzwerkes |Hier wird die Broadcast Adresse des Netzwerkes eingegeben, damit der Tvheadend Server per WOL gestartet werden kann.
Root Benutzer des Servers | Root Benutzer des Servers, auf dem Tvheadend installiert ist, darüber kann der Server heruntergefahren werden.
Root Passwort des Servers | Root Passwort des Servers, auf dem Tvheadend installiert ist, darüber kann der Server heruntergefahren werden.
Admin Benutzer für das Webinterface | Admin Benutzer für das TVH Webinterface.
Admin Passwort für das Webinterface | Admin Passwort für das TVH Webinterface.
Vorlaufzeit Aufname | Zeit, die auf die Startzeit der Aufnahme addiert wird.
Nachlaufzeit Aufname | Zeit, die auf die Endzeit der Aufnahme addiert wird.
IntervalBox | Hier kann de Zeit eingestellt werden, wie oft das Modul die Daten vom Tvheadend Server abfragt.

## Sonstiges

phpseclib from Jim Wigginton terrafrost@php.net