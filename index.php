<?php
$config = require 'config.php';
$title = $config['page_title'] ?? 'Geofence Map';
$logo = $config['logo_url'] ?? '';
$locale = $config['locale'] ?? 'en';
$langPath = __DIR__ . "/lang/{$locale}.json";
$lang = file_exists($langPath) ? json_decode(file_get_contents($langPath), true) : [];

function loadCachedAreas($config) {
    $cacheFile = __DIR__ . '/areas_cache.json';
    $maxAge = 7 * 24 * 60 * 60; // reload once a week

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $maxAge)) {
        $json = json_decode(file_get_contents($cacheFile), true);
    } else {
        $ch = curl_init($config['api_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $config['bearer_token']]);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response, true);
        if (isset($json['data']['features'])) {
            file_put_contents($cacheFile, $response);
        } else {
            $json = ['data' => ['features' => []]];
        }
    }

    return array_values(array_filter(
        $json['data']['features'] ?? [],
        fn($f) => !in_array(strtolower($f['properties']['name'] ?? ''), array_map('strtolower', $config['exclude_areas'] ?? []))
    ));
}

$features = loadCachedAreas($config);
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <script>
    if (sessionStorage.getItem('popupSeen')) {
      document.documentElement.classList.add('popup-hidden');
    }
  </script>

  <style>
    .popup-hidden #popup-overlay {
      display: none !important;
    }
  </style>
  
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($title) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <style>
    html, body { margin: 0; height: 100%; font-family: sans-serif; transition: background 0.3s, color 0.3s; }
    #map { position: absolute; top: 60px; bottom: 0; width: 100%; }

header {
  position: relative; /* NEU */
  height: 60px;
      background: #f4f4f4;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 15px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 2000;
    }

    header img {
      height: 40px;
    }

    .header-buttons {
      display: flex;
      gap: 10px;
    }

.location-menu select {
  padding: 6px;
  border-radius: 4px;
  font-size: 14px;
  margin-left: 10px;
}

    .menu-toggle {
      background: transparent;
      color: #333;
      border: 2px solid transparent;
      padding: 8px 12px;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

.dark .dropdown .menu-toggle,
.dark .menu-toggle {
  color: #fff !important;
}

.dropdown {
  position: relative;
}

.dropdown-menu {
  position: absolute;
  top: 150%; /* exakt unter dem Button */
  left: 0;
  right: auto;
  z-index: 3000;
  background: white;
  padding: 10px;
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  display: none;
}

.dropdown-menu.show {
  display: block;
}

.dropdown-menu.show {
  max-height: 500px;
  opacity: 1;
  transform: scaleY(1);
}

.dropdown-menu .dropdown-item {
  padding: 10px;
  cursor: pointer;
  font-size: 14px;
}

.dropdown-menu .dropdown-item:hover {
  background-color: #f0f0f0;
}

.dark .dropdown-menu {
  background: #2a2a2a;
  color: #eee;
}

.dark .dropdown-menu .dropdown-item:hover {
  background: #444;
}

    .menu-toggle.active {
      background: #0077cc;
      color: white;
      border-color: #0077cc;
    }

    .panel, .sidebar {
      display: none;
    }
    .panel.open, .sidebar.open {
      display: block;
    }

    .panel, .sidebar {
      position: fixed;
      top: 60px;
      right: 0;
      width: 300px;
      background: white;
      padding: 15px;
      z-index: 1600;
      box-shadow: -2px 0 6px rgba(0,0,0,0.2);
    }

.sidebar {
  position: fixed;
  top: 60px;
  right: 1px;
  width: 250px;
  bottom: 0;
  overflow-y: auto;
  background: white;
  z-index: 1600;
  padding: 15px;
  box-shadow: -2px 0 6px rgba(0,0,0,0.2);
}

.dropdown-menu.show {
  display: block;
}

    .dark {
      background: #1e1e1e;
      color: #eee;
    }
    .dark header {
      background: #2a2a2a;
    }
    .dark .panel, .dark .sidebar {
      background: #2a2a2a;
      color: #eee;
    }

    input[type="text"] {
  width: calc(100% - 10px);
  padding: 5px;
      margin-bottom: 10px;
    }

    ul { list-style: none; padding: 0; margin: 0; }
    li { margin-bottom: 5px; cursor: pointer; }

    .tooltip-label {
      background: rgba(0, 0, 0, 0.9);
      color: white;
      padding: 3px 6px;
      border-radius: 3px;
      font-size: 12px;
      pointer-events: none;
    }
    
    .slider-toggle-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
}

.switch {
  position: relative;
  display: inline-block;
  width: 46px;
  height: 24px;
}
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #ccc;
  transition: .4s;
  border-radius: 24px;
}
.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}
input:checked + .slider {
  background-color: #0077cc;
}
input:checked + .slider:before {
  transform: translateX(22px);
}
  </style>
</head>
<body>
  <header>
    <div style="display: flex; align-items: center; gap: 15px;">
      <?php if ($logo): ?>
        <img src="<?= htmlspecialchars($logo) ?>" alt="Logo" />
      <?php else: ?>
        <strong><?= htmlspecialchars($title) ?></strong>
      <?php endif; ?>

      <?php if (!empty($config['locations'])): ?>
       <div class="dropdown" style="position: relative;">
<button id="location-toggle" class="menu-toggle">📍 <?= $lang['select_city'] ?? 'Select a City' ?></button>
  <div id="location-menu" class="dropdown-menu">
    <?php foreach ($config['locations'] as $loc): ?>
      <div class="dropdown-item"
       data-lat="<?= $loc['lat'] ?>"
       data-lng="<?= $loc['lng'] ?>"
       data-zoom="<?= $loc['zoom'] ?? 13 ?>">
    <?= htmlspecialchars($loc['name']) ?>
  </div>
    <?php endforeach; ?>
  </div>
</div>
      <?php endif; ?>
    </div>

    <div class="header-buttons">
      <button class="menu-toggle" id="stat-btn" onclick="toggleStats(this)">📊</button>
      <button class="menu-toggle" id="dark-btn" onclick="toggleDark(this)">🌙</button>
      <button class="menu-toggle" id="menu-btn" onclick="toggleSidebar(this)">☰</button>
    </div>
  </header>
  <div class="panel" id="stats-panel">
   <h3><?= $lang['daily_stats'] ?? 'Daily Stats' ?></h3>
    <div id="stat-time">⏳ Loading...</div>
     <div><strong>📈 <?= $lang['total_scanned'] ?? 'Total Scanned' ?>:</strong> <span id="stat-total">-</span></div>
    <div>
  <strong>💯 Pokémon</strong><br>
  <span id="stat-hundo">-</span>
</div>
    <div><strong>✨ Shinys</strong><br>
     <span id="stat-shiny">-</span></div>
  </div>

  <div class="sidebar" id="sidebar">
    <h3><?= $lang['areas'] ?? 'Areas' ?></h3>
    <input type="text" id="search" placeholder="Suchen..." />
    <div class="slider-toggle-wrapper">
      <label class="switch">
        <input type="checkbox" id="toggle-all" checked>
        <span class="slider"></span>
      </label>
      <span id="toggle-all-label"><?= $lang['show_all_areas'] ?? 'Show all areas' ?></span>
    </div>
    <ul id="geofence-list"></ul>
  </div>
<script>
  const lang = <?= json_encode($lang) ?>;
</script>
  <div id="map"></div>

   <!-- Jetzt korrekt platziert -->

  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script>
    const map = L.map('map').setView(
      [<?= $config['default_lat'] ?? 51.0 ?>, <?= $config['default_lng'] ?? 10.0 ?>],
      <?= $config['default_zoom'] ?? 6 ?>
    );
    const tileLight = "<?= $config['tile_url_light'] ?>";
    const tileDark = "<?= $config['tile_url_dark'] ?>";
    let isDark = false;
    let tileLayer = L.tileLayer(tileLight, { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);

    const features = <?= json_encode($features) ?>;
    const geoLayers = [], allDrawColors = [], allLi = [];
    const list = document.getElementById('geofence-list');

    function toggleStats(btn) {
      const panel = document.getElementById('stats-panel');
      const sidebar = document.getElementById('sidebar');
      const show = !panel.classList.contains('open');
      panel.classList.toggle('open', show);
      sidebar.classList.remove('open');
      document.getElementById('menu-btn').classList.remove('active');
      btn.classList.toggle('active', show);

      if (show) {
        fetch('stats.php')
          .then(res => res.json())
          .then(data => {
            document.getElementById("stat-time").textContent = `📅 ${data.date} ⏰ ${data.time}`;
document.getElementById("stat-hundo").textContent = `${data.distinct_hundo} ${lang.species || 'Spezies'} | ${data.hundo} ${lang.total || 'Total'}`;
document.getElementById("stat-shiny").textContent = `${data.distinct_shiny} ${lang.species || 'Spezies'} | ${data.shiny} ${lang.total || 'Total'}`;
document.getElementById("stat-total").textContent = data.total_world;
document.getElementById("stat-distinct-shiny").textContent = data.distinct_shiny;
          });
      }
    }
    

    function toggleDark(btn) {
      isDark = !isDark;
      document.body.classList.toggle('dark', isDark);
      map.removeLayer(tileLayer);
      tileLayer = L.tileLayer(isDark ? tileDark : tileLight, {
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);
      btn.classList.toggle('active', isDark);
    }

    function toggleSidebar(btn) {
      const sidebar = document.getElementById('sidebar');
      const panel = document.getElementById('stats-panel');
      const show = !sidebar.classList.contains('open');
      sidebar.classList.toggle('open', show);
      panel.classList.remove('open');
      document.getElementById('stat-btn').classList.remove('active');
      btn.classList.toggle('active', show);
    }

    function clearGeofences() {
      geoLayers.forEach(l => map.removeLayer(l));
      geoLayers.length = 0;
    }

    function drawFeature(f, color, i) {
      const layer = L.geoJSON(f, {
        style: { color, fillColor: color, fillOpacity: 0.1, weight: 2 }
      }).addTo(map);
      const tooltip = L.tooltip({ permanent: false, direction: 'top', className: 'tooltip-label', sticky: true }).setContent(f.properties.name);
      layer.on('mouseover', () => layer.bindTooltip(tooltip).openTooltip());
      layer.on('mouseout', () => layer.closeTooltip());
      layer.on('click', () => {
  fetch(`stats.php?area=${encodeURIComponent(f.properties.name)}`)
    .then(res => res.json())
    .then(data => {
      const content = `
        <strong>${f.properties.name}</strong><br>
        💯  ${data.hundo}<br>
        📊  ${data.total_area}
      `;
      layer.unbindTooltip(); 
      layer.bindTooltip(content, {
        permanent: false,
        direction: 'top',
        className: 'tooltip-label',
        sticky: true
      }).openTooltip(); 
    });
});
      geoLayers[i] = layer;
    }

    function drawAllVisible() {
      clearGeofences();
      features.forEach((f, i) => {
        const cb = document.getElementById('cb' + i);
        if (cb?.checked) drawFeature(f, allDrawColors[i], i);
      });
    }

    features.forEach((f, i) => {
      const color = '#' + Math.floor(Math.random() * 16777215).toString(16).padStart(6, '0');
      allDrawColors[i] = color;
      drawFeature(f, color, i);
      const li = document.createElement('li');
      li.innerHTML = '<label><input type="checkbox" checked id="cb'+i+'" /> ' + f.properties.name + '</label>';
      list.appendChild(li);
      allLi[i] = li;
      document.getElementById('cb'+i).addEventListener('change', drawAllVisible);
      li.addEventListener('click', () => map.fitBounds(geoLayers[i].getBounds()));
    });

    document.getElementById('search').addEventListener('input', function() {
      const value = this.value.toLowerCase();
      list.innerHTML = '';
      features.forEach((f, i) => {
        if (f.properties.name.toLowerCase().includes(value)) {
          list.appendChild(allLi[i]);
        }
      });
    });

    document.getElementById('toggle-all')?.addEventListener('change', function () {
      const check = this.checked;
      features.forEach((f, i) => {
        const cb = document.getElementById('cb'+i);
        if (cb) cb.checked = check;
      });
      drawAllVisible();
    });

    let lastTap = 0;
    map.getContainer().addEventListener('touchend', function (e) {
      const currentTime = new Date().getTime();
      const tapLength = currentTime - lastTap;
      if (tapLength < 300 && tapLength > 0) {
        map.zoomIn();
        e.preventDefault();
      }
      lastTap = currentTime;
    });
    // Dropdown-Menü für Stadtwahl
const locationToggle = document.getElementById('location-toggle');
const locationMenu = document.getElementById('location-menu');

if (locationToggle && locationMenu) {
  locationToggle.addEventListener('click', () => {
    locationMenu.style.display = locationMenu.style.display === 'block' ? 'none' : 'block';
  });

  document.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', () => {
      const lat = parseFloat(item.dataset.lat);
      const lng = parseFloat(item.dataset.lng);
      const zoom = parseInt(item.dataset.zoom) || 13; // Fallback-Zoom

    map.setView([lat, lng], zoom);
    locationMenu.style.display = 'none';
  });
});

  // Menü schließen, wenn man außerhalb klickt
  document.addEventListener('click', (e) => {
    if (!locationMenu.contains(e.target) && !locationToggle.contains(e.target)) {
      locationMenu.style.display = 'none';
    }
  });
}
  </script>
  <script>
    window.addEventListener('DOMContentLoaded', () => {
      const popup = document.getElementById('popup-overlay');
      const confirmButton = document.getElementById('popup-confirm');

      console.log("Confirm Button gefunden:", confirmButton);

      if (sessionStorage.getItem('popupSeen')) {
        if (popup) popup.remove();
      } else {
        if (confirmButton) {
          confirmButton.addEventListener('click', () => {
            console.log("Button geklickt – Popup wird entfernt");
            sessionStorage.setItem('popupSeen', 'true');
            popup.remove();
          });
        } else {
          console.warn("popup-confirm nicht gefunden!");
        }
      }
    });
  </script>
<?php include 'popup.php'; ?>
<script>
  // Sicherheit: sobald DOM "readystate" >= interactive, führe Code aus
  function initPopup() {
    const popup = document.getElementById('popup-overlay');
    const confirm = document.getElementById('popup-confirm');
    if (!popup || !confirm) return console.warn("popup nicht gefunden");

    if (sessionStorage.getItem('popupSeen')) {
      popup.remove();
    } else {
      confirm.addEventListener('click', () => {
        sessionStorage.setItem('popupSeen','true');
        popup.remove();
      });
    }
  }
  if (document.readyState !== 'loading') {
    initPopup();
  } else {
    document.addEventListener('DOMContentLoaded', initPopup);
  }
</script>

</body>
</html>