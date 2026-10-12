<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

try {
    $stmt = $pdo->query('SELECT id, username FROM admin_users ORDER BY id ASC');
    $users = $stmt->fetchAll();
    
    // Add a flag to indicate the current logged-in user
    foreach ($users as &$u) {
        $u['is_current'] = ($u['id'] == $_SESSION['admin_id']);
    }

    echo json_encode(['success' => true, 'data' => $users]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener usuarios']);
}
?>
