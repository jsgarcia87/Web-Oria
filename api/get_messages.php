<?php
// api/get_messages.php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

try {
    // Asegurar que la tabla existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        empresa VARCHAR(255) DEFAULT '',
        email VARCHAR(255) NOT NULL,
        telefono VARCHAR(50) DEFAULT '',
        tema VARCHAR(255) DEFAULT '',
        mail_sent TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $pdo->query('SELECT id, nombre, empresa, email, telefono, tema, mail_sent, created_at FROM contact_messages ORDER BY created_at DESC');
    $messages = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $messages]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener mensajes: ' . $e->getMessage()]);
}
?>
