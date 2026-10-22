<?php
// api/contact.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
require 'db.php';

// Crear tabla settings si no existe
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT NOT NULL
    )");
    
    // Insertar defaults si está vacía
    $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
            ('form_email_to', 'jgomez.mapa@gmail.com'),
            ('form_reply_subject', 'Hemos recibido tu solicitud de café - ORIA'),
            ('form_reply_message', 'Hola,\n\nGracias por querer tomarte un café sin prisas con nosotros. Hemos recibido tu solicitud y nos pondremos en contacto contigo lo antes posible para agendarlo.\n\nUn saludo,\nEl equipo de ORIA')
        ");
    }

    // Crear tabla contact_messages si no existe para guardar todos los contactos
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
} catch (Exception $e) { }

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Sin datos']);
    exit;
}

// Aceptar claves en mayúscula y minúscula para máxima compatibilidad
$nombre = htmlspecialchars(trim($data['Nombre'] ?? $data['nombre'] ?? ''));
$empresa = htmlspecialchars(trim($data['Empresa'] ?? $data['empresa'] ?? ''));
$email = filter_var(trim($data['Email'] ?? $data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$telefono = htmlspecialchars(trim($data['Telefono'] ?? $data['telefono'] ?? ''));
$tema = htmlspecialchars(trim($data['Tema'] ?? $data['tema'] ?? ''));

if (!$nombre || !$email) {
    echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios (nombre y email)']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El formato del email no es válido']);
    exit;
}

// Obtener configuración guardada desde el panel
$settings = [];
try {
    $stmt = $pdo->query('SELECT setting_key, setting_value FROM settings');
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {}

$to_email = $settings['form_email_to'] ?? 'jgomez.mapa@gmail.com';
$reply_subject = $settings['form_reply_subject'] ?? 'Hemos recibido tu solicitud de café - ORIA';
$reply_message = $settings['form_reply_message'] ?? "Hola,\n\nGracias por querer tomarte un café sin prisas con nosotros. Hemos recibido tu solicitud y nos pondremos en contacto contigo lo antes posible para agendarlo.\n\nUn saludo,\nEl equipo de ORIA";

// --- PASO 1: GUARDAR SIEMPRE PRIMERO EN LA BASE DE DATOS ---
// Así el contacto nunca se pierde aunque el servidor de correo falle o se demore
$message_id = null;
$db_saved = false;
try {
    $stmt_save = $pdo->prepare('INSERT INTO contact_messages (nombre, empresa, email, telefono, tema, mail_sent) VALUES (?, ?, ?, ?, ?, 0)');
    $stmt_save->execute([$nombre, $empresa, $email, $telefono, $tema]);
    $message_id = $pdo->lastInsertId();
    $db_saved = true;
} catch (Exception $e) {
    error_log("Error guardando mensaje en BD: " . $e->getMessage());
}

// Remitente del sistema en el propio dominio (esencial para pasar SPF y DMARC en hosting OVH)
$system_sender = 'web@oriaestrategia.com';

// --- PASO 2: ENVIAR EMAIL DE NOTIFICACIÓN AL ADMINISTRADOR ---
$admin_subject = "Nueva solicitud de café: " . ($tema ? $tema : 'General');
$admin_subject_encoded = "=?UTF-8?B?" . base64_encode($admin_subject) . "?=";

$admin_body = "Se ha recibido una nueva solicitud de café desde la web:\n\n"
            . "--------------------------------------------------\n"
            . "Nombre:   $nombre\n"
            . "Empresa:  " . ($empresa ? $empresa : 'No especificada') . "\n"
            . "Email:    $email\n"
            . "Teléfono: " . ($telefono ? $telefono : 'No especificado') . "\n"
            . "Tema:     " . ($tema ? $tema : 'No especificado') . "\n"
            . "Fecha:    " . date('d/m/Y H:i:s') . "\n"
            . "--------------------------------------------------\n\n"
            . "Puedes responder directamente a este correo para escribir al cliente.";

$admin_headers = "From: ORIA Web <" . $system_sender . ">\r\n"
               . "Reply-To: " . $nombre . " <" . $email . ">\r\n"
               . "MIME-Version: 1.0\r\n"
               . "Content-Type: text/plain; charset=UTF-8\r\n"
               . "Content-Transfer-Encoding: 8bit\r\n"
               . "X-Mailer: PHP/" . phpversion();

// Intentar envío con parámetro envelope-sender (-f) para OVH
$admin_sent = @mail($to_email, $admin_subject_encoded, $admin_body, $admin_headers, "-f" . $system_sender);
if (!$admin_sent) {
    // Reintento sin parámetro adicional por si el servidor restringe el 5º parámetro
    $admin_sent = @mail($to_email, $admin_subject_encoded, $admin_body, $admin_headers);
}

// Si el correo al admin se envió con éxito, actualizar estado en la base de datos
if ($admin_sent && $message_id) {
    try {
        $stmt_upd = $pdo->prepare('UPDATE contact_messages SET mail_sent = 1 WHERE id = ?');
        $stmt_upd->execute([$message_id]);
    } catch (Exception $e) {}
}

// --- PASO 3: ENVIAR AUTO-RESPUESTA AL CLIENTE ---
// IMPORTANTE: El remitente 'From' debe ser del dominio (@oriaestrategia.com).
// Si se pusiera From: jgomez.mapa@gmail.com desde un servidor OVH, Gmail/Outlook lo rechaza
// de inmediato por política estricta DMARC (p=reject). Usamos Reply-To para que si el cliente
// responde, le llegue directamente a Jorge.
$client_sent = false;
if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $reply_subject_encoded = "=?UTF-8?B?" . base64_encode($reply_subject) . "?=";
    $client_headers = "From: ORIA <" . $system_sender . ">\r\n"
                    . "Reply-To: " . $to_email . "\r\n"
                    . "MIME-Version: 1.0\r\n"
                    . "Content-Type: text/plain; charset=UTF-8\r\n"
                    . "Content-Transfer-Encoding: 8bit\r\n"
                    . "X-Mailer: PHP/" . phpversion();

    $client_sent = @mail($email, $reply_subject_encoded, $reply_message, $client_headers, "-f" . $system_sender);
    if (!$client_sent) {
        $client_sent = @mail($email, $reply_subject_encoded, $reply_message, $client_headers);
    }
}

echo json_encode([
    'success' => true,
    'db_saved' => $db_saved,
    'message_id' => $message_id,
    'mail_sent' => $admin_sent,
    'client_reply_sent' => $client_sent
]);
?>
