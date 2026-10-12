<?php
// api/get_settings.php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    exit;
}

try {
    // Check if table exists, if not return defaults (contact.php creates it, but admin might visit first)
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT NOT NULL
    )");
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
            ('form_email_to', 'info@tu-dominio.com'),
            ('form_reply_subject', 'Hemos recibido tu mensaje - ORIA'),
            ('form_reply_message', 'Hola,\n\nGracias por querer tomarte un café sin prisas con nosotros. Hemos recibido tu mensaje y nos pondremos en contacto contigo lo antes posible para agendarlo.\n\nUn saludo,\nEl equipo de ORIA')
        ");
    }

    $stmt = $pdo->query('SELECT setting_key, setting_value FROM settings');
    $results = $stmt->fetchAll();
    
    $response = [];
    foreach ($results as $row) {
        $response[$row['setting_key']] = $row['setting_value'];
    }

    echo json_encode(['success' => true, 'data' => $response]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
