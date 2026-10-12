<?php
// api/export_messages.php
session_start();
require 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    die('Acceso denegado. Inicie sesión en el panel.');
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

    $stmt = $pdo->query('SELECT id, created_at, nombre, empresa, email, telefono, tema, mail_sent FROM contact_messages ORDER BY created_at DESC');
    $messages = $stmt->fetchAll();

    $filename = "contactos_oria_" . date('Y-m-d_His') . ".csv";

    // Cabeceras HTTP para forzar descarga como archivo CSV de Excel
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // Añadir BOM UTF-8 para que Excel reconozca tildes y caracteres en español correctamente
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Cabeceras de columnas (separadas por punto y coma para Excel en español)
    fputcsv($output, ['ID', 'Fecha y Hora', 'Nombre', 'Empresa', 'Email', 'Teléfono', 'Tema', 'Estado de Correo'], ';');

    foreach ($messages as $m) {
        fputcsv($output, [
            $m['id'],
            $m['created_at'],
            $m['nombre'],
            $m['empresa'],
            $m['email'],
            $m['telefono'],
            $m['tema'],
            $m['mail_sent'] ? 'Enviado por email' : 'Guardado en BD'
        ], ';');
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    die('Error al exportar los mensajes: ' . $e->getMessage());
}
?>
