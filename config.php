<?php
return [
    'page_title' => 'Coverage Map',
    #'welcome_message' => '', // not used yet
    'api_url' => 'your koi api url for geofence loading',
    'bearer_token' => 'xxx',
    'logo_url' => 'header logo',
    'tile_url_light' => 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager_labels_under/{z}/{x}/{y}{r}.png',
    'tile_url_dark' => 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
    #'exclude_areas' => ['excluded areas'],
    'default_lat' => xxx,
	'default_lng' => xxx,
	'default_zoom' => 10,
# if you have multiple locations put them in here
	'locations' => [
  ['name' => 'xxx', 'lat' => xxx, 'lng' => xxx],
  ['name' => 'xxx', 'lat' => xxx, 'lng' => xxx],
],
    'db_host' => '127.0.0.1',
    'db_port' => 3306,
    'db_name' => 'your db name',
    'db_user' => 'user',
    'db_pass' => 'pass'
];
