<?php
header('Content-Type: application/json');
$config = require 'config.php';

$format = $config['date_format'] === 'US' ? 'm/d/Y' : 'd.m.Y';
$timeFormat = $config['time_format'] === '12h' ? 'h:i A' : 'H:i:s';

$host = $config['db_host'];
$port = $config['db_port'];
$db   = $config['db_name'];
$user = $config['db_user'];
$pass = $config['db_pass'];

function fetchActiveHundos($golbatUrl, $secret) {
    $url = rtrim($golbatUrl, '/') . '/api/pokemon/v2/scan';

    $payload = json_encode([
        'min' => ['latitude' => -90, 'longitude' => -180],
        'max' => ['latitude' => 90, 'longitude' => 180],
        'filters' => [[
            'pokemon' => array_map(fn($id) => ['id' => $id], range(1, 1015)),
            'atk_iv' => ['min' => 15, 'max' => 15],
            'def_iv' => ['min' => 15, 'max' => 15],
            'sta_iv' => ['min' => 15, 'max' => 15],
        ]]
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-Golbat-Secret: ' . $secret
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    return is_array($data) ? count($data) : 0;
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $area = $_GET['area'] ?? 'world';

    if ($area) {
        $stmt1 = $pdo->prepare("SELECT SUM(count) as total FROM pokemon_hundo_stats WHERE date = CURDATE() AND area = ?");
        $stmt1->execute([$area]);
        $hundo = $stmt1->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $stmt2 = $pdo->prepare("SELECT SUM(count) as total FROM pokemon_shiny_stats WHERE date = CURDATE() AND area = ?");
        $stmt2->execute([$area]);
        $shiny = $stmt2->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $stmt3 = $pdo->prepare("SELECT SUM(count) FROM pokemon_stats WHERE DATE(date) = CURDATE() AND area = ?");
        $stmt3->execute([$area]);
        $total_area = $stmt3->fetch(PDO::FETCH_COLUMN) ?? 0;

        $stmt4 = $pdo->prepare("SELECT COUNT(DISTINCT pokemon_id) FROM pokemon_hundo_stats WHERE date = CURDATE() AND area = ?");
        $stmt4->execute([$area]);
        $distinct_hundo = $stmt4->fetch(PDO::FETCH_COLUMN) ?? 0;

        $stmt5 = $pdo->prepare("SELECT COUNT(DISTINCT pokemon_id) FROM pokemon_shiny_stats WHERE date = CURDATE() AND area = ?");
        $stmt5->execute([$area]);
        $distinct_shiny = $stmt5->fetch(PDO::FETCH_COLUMN) ?? 0;

        $total_world = null;
    } else {
        $stmt1 = $pdo->query("SELECT SUM(count) as total FROM pokemon_hundo_stats WHERE date = CURDATE()");
        $hundo = $stmt1->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $stmt2 = $pdo->query("SELECT SUM(count) as total FROM pokemon_shiny_stats WHERE date = CURDATE()");
        $shiny = $stmt2->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $stmt3 = $pdo->query("SELECT SUM(count) as total FROM pokemon_stats WHERE date = CURDATE() AND area = 'world'");
        $total_world = $stmt3->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $stmt4 = $pdo->query("SELECT COUNT(DISTINCT pokemon_id) FROM pokemon_hundo_stats WHERE date = CURDATE()");
        $distinct_hundo = $stmt4->fetch(PDO::FETCH_COLUMN) ?? 0;

        $stmt5 = $pdo->query("SELECT COUNT(DISTINCT pokemon_id) FROM pokemon_shiny_stats WHERE date = CURDATE()");
        $distinct_shiny = $stmt5->fetch(PDO::FETCH_COLUMN) ?? 0;

        $total_area = null;
    }

    // Aktive 100er aus Golbat API
    $active_hundos = 0;
    if (!empty($config['golbat_api_url']) && !empty($config['golbat_api_secret'])) {
        $active_hundos = fetchActiveHundos($config['golbat_api_url'], $config['golbat_api_secret']);
    }

    echo json_encode([
        'date' => date($format),
        'time' => date($timeFormat),
        'hundo' => number_format($hundo),
        'distinct_hundo' => $distinct_hundo,
        'shiny' => number_format($shiny),
        'distinct_shiny' => $distinct_shiny,
        'total_world' => $total_world !== null ? number_format($total_world) : null,
        'total_area' => $total_area !== null ? number_format($total_area) : null,
        'active_hundos' => number_format($active_hundos) // ⬅️ Das ist neu
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB Fehler: ' . $e->getMessage()]);
}
?>
