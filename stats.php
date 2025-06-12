<?php
header('Content-Type: application/json');
$config = require 'config.php';

$host = $config['db_host'];
$port = $config['db_port'];
$db   = $config['db_name'];
$user = $config['db_user'];
$pass = $config['db_pass'];

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $area = isset($_GET['area']) ? $_GET['area'] : null;

    if ($area) {
        $stmt1 = $pdo->prepare("SELECT SUM(count) as total FROM pokemon_hundo_stats WHERE date = CURDATE() AND area = ?");
        $stmt1->execute([$area]);
        $hundo = $stmt1->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $stmt2 = $pdo->prepare("SELECT SUM(count) as total FROM pokemon_shiny_stats WHERE date = CURDATE() AND area = ?");
        $stmt2->execute([$area]);
        $shiny = $stmt2->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $stmt4 = $pdo->prepare("
          SELECT SUM(count) AS total
          FROM pokemon_stats
          WHERE DATE(date) = CURDATE()
            AND area = ?
        ");
        $stmt4->execute([$area]);
        $total_area = $stmt4->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $total_world = null; 
    } else {
        $stmt1 = $pdo->prepare("SELECT SUM(count) as total FROM pokemon_hundo_stats WHERE date = CURDATE()");
        $stmt1->execute();
        $hundo = $stmt1->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $stmt2 = $pdo->prepare("SELECT SUM(count) as total FROM pokemon_shiny_stats WHERE date = CURDATE()");
        $stmt2->execute();
        $shiny = $stmt2->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $stmt3 = $pdo->prepare("SELECT SUM(count) as total FROM pokemon_stats WHERE date = CURDATE() AND area = 'world'");
        $stmt3->execute();
        $total_world = $stmt3->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        $total_area = null; 
    }

$format = $config['date_format'] === 'US' ? 'm/d/Y' : 'd.m.Y';
$timeFormat = $config['time_format'] === '12h' ? 'h:i A' : 'H:i:s';

echo json_encode([
    'date' => date($format),
    'time' => date('H:i:s'),
        'hundo' => number_format($hundo),
        'shiny' => number_format($shiny),
        'total_world' => $total_world !== null ? number_format($total_world) : null,
        'total_area' => $total_area !== null ? number_format($total_area) : null
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB Fehler: ' . $e->getMessage()]);
}
?>