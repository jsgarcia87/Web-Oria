<?php
// api/get_analytics.php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

try {
    // Asegurarse de que la tabla existe por si el admin entra antes de que haya visitas
    $pdo->exec("CREATE TABLE IF NOT EXISTS analytics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page VARCHAR(255) NOT NULL,
        time_spent INT NOT NULL,
        is_bounce BOOLEAN NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Stats Globales
    $stmt = $pdo->query('SELECT COUNT(*) as total_visits, AVG(time_spent) as avg_time, SUM(is_bounce) as total_bounces FROM analytics');
    $global = $stmt->fetch();

    // Stats Diarias (Últimos 7 días activos)
    $stmt2 = $pdo->query('
        SELECT DATE(created_at) as date, AVG(time_spent) as avg_time, COUNT(*) as visits, SUM(is_bounce) as bounces 
        FROM analytics 
        GROUP BY DATE(created_at) 
        ORDER BY date DESC 
        LIMIT 7
    ');
    $daily = $stmt2->fetchAll();

    // Stats por página
    $stmt3 = $pdo->query('
        SELECT page, AVG(time_spent) as avg_time, COUNT(*) as visits 
        FROM analytics 
        GROUP BY page 
        ORDER BY visits DESC 
        LIMIT 5
    ');
    $pages = $stmt3->fetchAll();

    echo json_encode([
        'success' => true, 
        'global' => $global,
        'daily' => array_reverse($daily),
        'pages' => $pages
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener analíticas']);
}
?>
