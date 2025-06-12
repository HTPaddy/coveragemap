Scroll down for the english version of this README
--------------------------------------------------------------------------------------------------

# 🗺️ Geofence Map

Ein responsives, konfigurierbares Webtool zum Anzeigen von Geofences aus einer API – mit Unterstützung für:
- 🌓 Darkmode
- 📊 Statistik-Anzeige aus MariaDB
- 📍 Standortwahl (mehrere Orte direkt ansteuerbar) 
- 🔲 Ein-/Ausblendung einzelner Gebiete
- 📱 Touch- und Mobile-Unterstützung
- für die Anzeige eurer Gebiete benötigt Ihr Koji: [📍 Koji](https://github.com/TurtIeSocks/Koji)
- bitte beachtet das bei überlagernde Geofences, nur die Oberste klickbar ist

## 📊 Statistik

Zeigt für den aktuellen Tag:
- Anzahl gescannter **100% Pokémon**
- Anzahl gescannter **Shiny Pokémon**

Datenquelle: `pokemon_hundo_stats` und `pokemon_shiny_stats` mit Feldern `date` und `count`.
Die Stats der CoverageMap sind nur kompatibel mit [📍 Golbat](https://github.com/UnownHash/Golbat)

## 📱 Mobile Features

- **Doppeltipp** auf die Karte zoomt hinein
- Sidebar & Stats-Menü **automatisch klappbar**
- Standortwahl über Dropdown-Menü im Header

## 🌓 Darkmode

Wechselt zwischen Light- und Dark-Style:
- angepasstes Karten-Theme
- Header & Panels werden entsprechend dunkler

## 🧪 Lokales Testen

Einfach lokal mit PHP starten:

```bash
php -S localhost:8000
```

```Docker:
Dann aufrufen: http://localhost:8000/ -> http://localhost:80 
``` 

Danke an [ReuschelCGN](https://github.com/ReuschelCGN) für die Docker files

------------------------------------------------------------------------------------------------

 🗺️ Geofence Map

A responsive, configurable web tool for displaying geofences from an API – with support for:
- 🌓 Dark mode  
- 📊 Statistics display from MariaDB  
- 📍 Location selection (navigate to multiple places directly)  
- 🔲 Toggle individual areas on/off  
- 📱 Touch and mobile support  
- you also need Koji for your areas: [📍 Koji](https://github.com/TurtIeSocks/Koji)
- if you have overlapping geofences only the top one will be clickable

## 📊 Statistics

Displays for the current day:
- Number of scanned **100% Pokémon**  
- Number of scanned **Shiny Pokémon**

**Data source:** `pokemon_hundo_stats` and `pokemon_shiny_stats` tables with `date` and `count` fields.  
The stats of the CoverageMap are only compatible with [📍 Golbat](https://github.com/UnownHash/Golbat)

## 📱 Mobile Features

- **Double-tap** on the map to zoom in  
- Sidebar & stats menu **automatically collapsible**  
- Location selection via dropdown menu in the header  

## 🌓 Dark Mode

Switches between light and dark styles:
- Adjusted map theme  
- Header & panels adapt to the selected mode  

## 🧪 Local Testing

Easily start it locally with PHP:

```bash
php -S localhost:8000

for Docker you use
http://localhost:8000/ -> http://localhost:80
```

Thanks to [ReuschelCGN](https://github.com/ReuschelCGN) for the docker files
Main idea of this comes from [VersX](https://github.com/versx)
