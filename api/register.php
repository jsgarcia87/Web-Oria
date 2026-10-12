<?php
// api/register.php
session_start();
header('Content-Type: application/json');
require 'db.php';

// Solo un administrador logueado puede crear a otro
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['username']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    exit;
}

try {
    // Comprobar si existe
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM admin_users WHERE username = ?');
    $stmt->execute([$data['username']]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'El usuario ya existe']);
        exit;
    }

    // Insertar usuario
    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $insert = $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
    $insert->execute([$data['username'], $hash]);
    
    echo json_encode(['success' => true, 'message' => 'Usuario registrado correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al registrar en base de datos']);
}
?>
