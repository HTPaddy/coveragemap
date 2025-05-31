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

    $stmt1 = $pdo->prepare("SELECT SUM(count) as total FROM pokemon_hundo_stats WHERE date = CURDATE()");
$stmt1->execute();
$hundo = $stmt1->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

$stmt2 = $pdo->prepare("SELECT SUM(count) as total FROM pokemon_shiny_stats WHERE date = CURDATE()");
$stmt2->execute();
$shiny = $stmt2->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    echo json_encode([
    'date' => date('d.m.Y'),
    'time' => date('H:i:s'),
    'hundo' => $hundo,
    'shiny' => $shiny
]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
}
