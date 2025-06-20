<?php
return [
    'page_title' => 'Coverage Map',
    'api_url' => 'http://{koji_url}/api/v1/geofence/feature-collection/{dragonite_project}',
    'golbat_api_url' => 'http://127.0.0.1:port',
    'golbat_api_secret' => 'your_secret',
    'bearer_token' => 'koji_secret',
    'logo_url' => 'your_logo_url',
    'tile_url_light' => 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png',
    'tile_url_dark' => 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
//  'exclude_areas' => ['areas_you_want_to_exclude_from_display'],
    'default_lat' => 00.000000,
    'default_lng' => 00.00000,
    'default_zoom' => 10,
    'date_format' => 'EU', // or 'US'
    'time_format' => '24h', // or '12h'
    'locale' => 'de', // or 'en'
//  if you have multiple locations, you can make them visible via menu with this config
//  'locations' => [    
//  ['name' => 'City1', 'lat' => 52.52, 'lng' => 13.405, 'zoom' => 10],
//  ['name' => 'City2', 'lat' => 52.133333333333, 'lng' => 11.61666666666756, 'zoom' => 10],
//  ['name' => 'City3', 'lat' => 48.4011, 'lng' => 9.9876, 'zoom' => 10],
    'db_host' => '127.0.0.1',
    'db_port' => 3306,
    'db_name' => 'golbatdb',
    'db_user' => 'db_user',
    'db_pass' => 'your_pass'
];
