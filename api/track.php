<?php
// api/track.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
require 'db.php';

// Crear la tabla si no existe
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS analytics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page VARCHAR(255) NOT NULL,
        time_spent INT NOT NULL,
        is_bounce BOOLEAN NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {
    // Ignorar error de creación
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['page']) && isset($data['time_spent'])) {
    $page = $data['page'];
    $time_spent = (int) $data['time_spent'];
    // Consideramos rebote si está menos de 10 segundos
    $is_bounce = $time_spent < 10 ? 1 : 0;

    try {
        $stmt = $pdo->prepare('INSERT INTO analytics (page, time_spent, is_bounce) VALUES (?, ?, ?)');
        $stmt->execute([$page, $time_spent, $is_bounce]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
}
?>
