# 🗺️ Geofence Map

Ein responsives, konfigurierbares Webtool zum Anzeigen von Geofences aus einer API – mit Unterstützung für:
- 🌓 Darkmode
- 📊 Statistik-Anzeige aus MySQL
- 📍 Standortwahl (mehrere Orte direkt ansteuerbar) 
- 🔲 Ein-/Ausblendung einzelner Gebiete
- 📱 Touch- und Mobile-Unterstützung

## 📊 Statistik

Zeigt für den aktuellen Tag:
- Anzahl gescannter **100% Pokémon**
- Anzahl gescannter **Shiny Pokémon**

Datenquelle: `pokemon_hundo_stats` und `pokemon_shiny_stats` mit Feldern `date` und `count`.
Diese CoverageMap ist nur kompatibel [📍 Golbat](https://github.com/UnownHash/Golbat)

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

Dann aufrufen: [http://localhost:8000](http://localhost:8000)

## 📩 Feedback oder Fragen?

Einfach in Discord oder direkt melden – 😊