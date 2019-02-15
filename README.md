[![Version](https://img.shields.io/badge/Symcon-PHPModul-red.svg)](https://www.symcon.de/service/dokumentation/entwicklerbereich/sdk-tools/sdk-php/)
![Version](https://img.shields.io/badge/Symcon%20Version-4.3%20%3E-blue.svg)
[![License](https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-green.svg)](https://creativecommons.org/licenses/by-nc-sa/4.0/)
[![StyleCI](https://styleci.io/repos/142878476/shield?style=flat)](https://styleci.io/repos/142878476)


# IPS-Tvheadend
Mit diesem Modul ist es möglich, einen Tvheadend Server in IPS zu überwachen.


**Inhaltsverzeichnis**

1. [Funktionsumfang](#1-funktionsumfang)  
2. [Installation](#2-installation)
3. [Spenden](#3-spenden) 
4. [Lizenzen](#4-lizenzen)  

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

## 3. Spenden

Dieses Modul ist für die nicht kommzerielle Nutzung kostenlos, Schenkungen als Unterstützung für den Autor werden hier akzeptiert:    

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EK4JRP87XLSHW" target="_blank"><img src="https://www.paypalobjects.com/de_DE/DE/i/btn/btn_donate_LG.gif" border="0" /></a>

## 4. Lizenzen
IPS-Modul:
[CC BY-NC-SA 4.0](https://creativecommons.org/licenses/by-nc-sa/4.0/)

phpseclib from Jim Wigginton <terrafrost@php.net>  
[MIT License](http://www.opensource.org/licenses/mit-license.html)  
Link: [http://phpseclib.sourceforge.net](http://phpseclib.sourceforge.net)  