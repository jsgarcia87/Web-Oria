<?php
// api/contact.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
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

$nombre = htmlspecialchars(trim($data['Nombre'] ?? ''));
$empresa = htmlspecialchars(trim($data['Empresa'] ?? ''));
$email = filter_var(trim($data['Email'] ?? ''), FILTER_SANITIZE_EMAIL);
$telefono = htmlspecialchars(trim($data['Telefono'] ?? ''));
$tema = htmlspecialchars(trim($data['Tema'] ?? ''));

if (!$nombre || !$email) {
    echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
    exit;
}

// Obtener configuración
$settings = [];
try {
    $stmt = $pdo->query('SELECT setting_key, setting_value FROM settings');
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {}

$to_email = $settings['form_email_to'] ?? 'jgomez.mapa@gmail.com';
$reply_subject = $settings['form_reply_subject'] ?? 'Hemos recibido tu mensaje';
$reply_message = $settings['form_reply_message'] ?? 'Gracias por contactar con nosotros.';

// --- 1. ENVIAR EMAIL AL ADMINISTRADOR ---
$admin_subject = "Nueva solicitud de cafe: $tema";
$admin_body = "Se ha recibido un nuevo formulario desde la web.\n\n"
            . "Nombre: $nombre\n"
            . "Empresa: $empresa\n"
            . "Email: $email\n"
            . "Telefono: $telefono\n"
            . "Tema: $tema\n";

$headers = "From: web@oriaestrategia.com\r\n"
         . "Reply-To: $email\r\n"
         . "X-Mailer: PHP/" . phpversion();

$admin_sent = mail($to_email, $admin_subject, $admin_body, $headers);

// --- GUARDAR EN BASE DE DATOS (Seguridad total aunque falle el email) ---
try {
    $stmt_save = $pdo->prepare('INSERT INTO contact_messages (nombre, empresa, email, telefono, tema, mail_sent) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt_save->execute([$nombre, $empresa, $email, $telefono, $tema, $admin_sent ? 1 : 0]);
} catch (Exception $e) { }

// --- 2. ENVIAR AUTO-RESPUESTA AL CLIENTE ---
if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $client_headers = "From: $to_email\r\n"
                    . "Reply-To: $to_email\r\n"
                    . "X-Mailer: PHP/" . phpversion();
    mail($email, $reply_subject, $reply_message, $client_headers);
}

echo json_encode(['success' => true, 'mail_sent' => $admin_sent]);
?>
