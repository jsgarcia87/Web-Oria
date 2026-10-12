<?php
// api/db.php
// Configuración de la base de datos
$host = 'lshwjkmoria.mysql.db'; // Host de la base de datos
$db   = 'lshwjkmoria'; // Nombre de la base de datos
$user = 'lshwjkmoria'; // Usuario de MySQL
$pass = 'Oria2026Bd'; // Contraseña
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(["error" => "Error de conexión a la base de datos. Verifica db.php"]);
    exit;
}
?>
