<?php
$config = require 'config.php';

$title = $config['page_title'] ?? 'Geofence Map';
$logo = $config['logo_url'] ?? '';
$exclude = array_map('strtolower', $config['exclude_areas'] ?? []);

$ch = curl_init($config['api_url']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $config['bearer_token']]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$features = [];
if ($httpCode === 200 && $response) {
  $json = json_decode($response, true);
  $features = array_filter($json['data']['features'] ?? [], function($f) use ($exclude) {
    $name = strtolower($f['properties']['name'] ?? '');
    return !in_array($name, $exclude);
  });
  $features = array_values($features);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
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
  max-height: 0;
  overflow: hidden;
  opacity: 0;
  transform: scaleY(0.95);
  transform-origin: top;
  transition: all 0.25s ease;
  position: absolute;
  top: 55px;      /* exakt unter dem Header */
  right: 10px;    /* optional: Abstand vom Rand */
  background: white;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  z-index: 9999;
  min-width: 150px;
  border-radius: 4px;
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
  right: 0;
  width: 300px;
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
      width: 100%;
      padding: 5px;
      margin-bottom: 10px;
    }

    ul { list-style: none; padding: 0; margin: 0; }
    li { margin-bottom: 5px; cursor: pointer; }

    .tooltip-label {
      background: rgba(0, 0, 0, 0.7);
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
    <div>
      <?php if ($logo): ?>
        <img src="<?= htmlspecialchars($logo) ?>" alt="Logo" />
      <?php else: ?>
        <strong><?= htmlspecialchars($title) ?></strong>
      <?php endif; ?>
    </div>
      <?php if (!empty($config['locations'])): ?>
    <div class="dropdown">
  <button id="location-toggle" class="menu-toggle">📍 Select City</button>
  <div id="location-menu" class="dropdown-menu">
    <?php foreach ($config['locations'] as $loc): ?>
      <div class="dropdown-item" data-lat="<?= $loc['lat'] ?>" data-lng="<?= $loc['lng'] ?>">
        <?= htmlspecialchars($loc['name']) ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
  <?php endif; ?>
    <div class="header-buttons">
      <button class="menu-toggle" id="stat-btn" onclick="toggleStats(this)">📊</button>
      <button class="menu-toggle" id="dark-btn" onclick="toggleDark(this)">🌙</button>
      <button class="menu-toggle" id="menu-btn" onclick="toggleSidebar(this)">☰</button>
    </div>
  </header>

  <div class="panel" id="stats-panel">
    <h3>Daily Statistics</h3>
    <div id="stat-time">⏳ Loading...</div>
    <div><strong>💯 Pokemon:</strong> <span id="stat-hundo">-</span></div>
    <div><strong>✨ Pokemon:</strong> <span id="stat-shiny">-</span></div>
  </div>

  <div class="sidebar" id="sidebar">
    <h3>Gebiete</h3>
    <input type="text" id="search" placeholder="Suchen..." />
    <div class="slider-toggle-wrapper">
  <label class="switch">
    <input type="checkbox" id="toggle-all" checked>
    <span class="slider round"></span>
  </label>
  <span id="toggle-all-label">Show all areas</span>
</div>
    <ul id="geofence-list"></ul>
  </div>

  <div id="map"></div>
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

    const features = <?= json_encode(array_values($features)) ?>;
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
            document.getElementById("stat-hundo").textContent = data.hundo;
            document.getElementById("stat-shiny").textContent = data.shiny;
          })
          .catch(() => {
            document.getElementById("stat-time").textContent = "Fehler beim Laden";
            document.getElementById("stat-hundo").textContent = "-";
            document.getElementById("stat-shiny").textContent = "-";
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
    let allVisible = true;

document.getElementById('toggle-all').addEventListener('click', () => {
  allVisible = !allVisible;
  features.forEach((f, i) => {
    const cb = document.getElementById('cb' + i);
    if (cb) cb.checked = allVisible;
  });
  drawAllVisible();
  document.getElementById('toggle-all').textContent = allVisible ? 'disable all areas' : 'enable all areas';
});

document.getElementById('location-toggle')?.addEventListener('click', () => {
  document.getElementById('location-menu').classList.toggle('show');
});

document.querySelectorAll('.dropdown-item').forEach(item => {
  item.addEventListener('click', () => {
    const lat = parseFloat(item.dataset.lat);
    const lng = parseFloat(item.dataset.lng);
    map.setView([lat, lng], 11);
    document.getElementById('location-menu').classList.remove('show');
  });
});

// Optional: Schließen bei Klick außerhalb
document.addEventListener('click', e => {
  if (!e.target.closest('.dropdown')) {
    document.getElementById('location-menu')?.classList.remove('show');
  }
});

let tapTimeout;

map.getContainer().addEventListener('touchend', function(e) {
  // Nur 1 Finger, keine Bewegung
  if (e.touches.length === 0 && e.changedTouches.length === 1) {
    if (tapTimeout) {
      clearTimeout(tapTimeout);
    }

    tapTimeout = setTimeout(() => {
      const zoom = map.getZoom();
      map.setZoom(zoom + 1);
    }, 150);
  }
});
  </script>
</body>
</html>
