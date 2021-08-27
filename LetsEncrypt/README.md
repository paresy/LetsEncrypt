# LetsEncrypt
Erstellt kostenlose SSL Zertifikate für eine WebServer Instanz über Let's Encrypt.

### Inhaltsverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [WebFront](#6-webfront)
7. [PHP-Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

* Diese Modul erlaubt das einfach Anforderung und Aktualisieren von SSL-Zertifiakten über Let's Encrypt und einer WebServer Instanz. Dabei wird das Zertifiakt per Knopfdruck angefordert und validiert, sodass der IP-Symcon Dienst nur noch neu gestartet werden muss, um das neue Zertifikat "Live" zu schalten. Wichtig ist, dass die gewünschte Domain korrekt auf das IP-Symcon System und den Port 443 weitergeleitet wird, da dies zur korrekten Verifikation seitens Let's Encrypt erforderlich ist 

### 2. Vorraussetzungen

- IP-Symcon ab Version 5.4

### 3. Software-Installation

* Über den Module Store das 'LetsEncrypt'-Modul installieren.
* Alternativ über das Module Control folgende URL hinzufügen: https://github.com/paresy/LetsEncrypt

### 4. Einrichten der Instanzen in IP-Symcon

 Unter 'Instanz hinzufügen' kann das 'LetsEncrypt'-Modul mithilfe des Schnellfilters gefunden werden.  
	- Weitere Informationen zum Hinzufügen von Instanzen in der [Dokumentation der Instanzen](https://www.symcon.de/service/dokumentation/konzepte/instanzen/#Instanz_hinzufügen)

__Konfigurationsseite__:

Name           | Beschreibung
-------------- | ------------------
E-Mail Adresse | E-Mail Adresse zur Kommunikation bei Fehlern oder bei einem drohenden Ablaufen des Zertifikats
Domain         | Vollständige Domain (z.B. www.example.com) für welche das SSL Zertifiakt angefordert werden soll
Web Server     | ID der WebServer Instanz, in der das Zertifikat konfiguriert wird

### 5. Statusvariablen und Profile

Die Statusvariablen/Kategorien werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

#### Statusvariablen

Es werden keine Statusvariablen erstellt.

#### Profile

Es werden keine Profile erstellt. 

### 6. WebFront

Die Funktionalität, die das Modul im WebFront bietet.

### 7. PHP-Befehlsreferenz

`string FetchCertificate(integer $InstanzID);`
Fordert ein neues Zertifikat an, validiert dieses und konfiguriert den angegeben WebServer.

Beispiel:
`echo FetchCertificate(12345);`