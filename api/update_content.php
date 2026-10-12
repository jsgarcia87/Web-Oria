<?php
// api/update_content.php
session_start();
header('Content-Type: application/json');
require 'db.php';

// Verificar si está logueado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['content_value'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    exit;
}

try {
    // Si no existe lo inserta, si existe lo actualiza
    $stmt = $pdo->prepare('
        INSERT INTO web_content (id, content_value, page) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE content_value = ?
    ');
    
    $page = isset($data['page']) ? $data['page'] : 'general';
    
    $stmt->execute([
        $data['id'], 
        $data['content_value'], 
        $page,
        $data['content_value'] // valor para el update
    ]);

    echo json_encode(['success' => true, 'message' => 'Guardado correctamente']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar en base de datos']);
}
?>
