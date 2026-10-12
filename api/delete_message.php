<?php
// api/delete_message.php
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
    echo json_encode(['success' => false, 'message' => 'Falta el ID del mensaje']);
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM contact_messages WHERE id = ?');
    $stmt->execute([(int)$data['id']]);
    echo json_encode(['success' => true, 'message' => 'Mensaje eliminado']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar mensaje: ' . $e->getMessage()]);
}
?>
