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
if (empty($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Falta el ID del usuario']);
    exit;
}

if ($data['id'] == $_SESSION['admin_id']) {
    echo json_encode(['success' => false, 'message' => 'Por seguridad, no puedes eliminar tu propia cuenta mientras estás conectado']);
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM admin_users WHERE id = ?');
    $stmt->execute([$data['id']]);
    echo json_encode(['success' => true, 'message' => 'Usuario eliminado correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar usuario']);
}
?>
