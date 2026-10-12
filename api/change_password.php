<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['id']) || empty($data['new_password'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

try {
    $hash = password_hash($data['new_password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
    $stmt->execute([$hash, $data['id']]);
    echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar contraseña']);
}
?>
