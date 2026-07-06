<?php
$host     = "localhost";
$user     = "postgres";
$password = "123";
$dbname   = "eventpro_db";

try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die(json_encode(["status" => "error", "message" => "Connection Failed: " . $e->getMessage()]));
}

// Mongike API Configuration
define('MONGIKE_API_KEY', 'mk_2c1a5fc7fbfbbf01072bbcc06e93a6cde61b213124fe8b4f');
?>