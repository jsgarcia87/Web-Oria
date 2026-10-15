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

    $period = isset($_GET['period']) ? trim($_GET['period']) : '7d';
    $startDate = null;
    $endDate = null;

    if ($period === '7d') {
        $startDate = date('Y-m-d', strtotime('-6 days'));
        $endDate = date('Y-m-d');
    } elseif ($period === '14d') {
        $startDate = date('Y-m-d', strtotime('-13 days'));
        $endDate = date('Y-m-d');
    } elseif ($period === '30d') {
        $startDate = date('Y-m-d', strtotime('-29 days'));
        $endDate = date('Y-m-d');
    } elseif ($period === 'month') {
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-d');
    } elseif ($period === 'all') {
        $startDate = null;
        $endDate = null;
    } elseif ($period === 'custom') {
        $customStart = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
        $customEnd = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $customStart)) {
            $startDate = $customStart;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $customEnd)) {
            $endDate = $customEnd;
        }
        if ($startDate && $endDate && $startDate > $endDate) {
            $tmp = $startDate;
            $startDate = $endDate;
            $endDate = $tmp;
        }
    } else {
        $period = '7d';
        $startDate = date('Y-m-d', strtotime('-6 days'));
        $endDate = date('Y-m-d');
    }

    $whereParts = [];
    $params = [];

    if ($startDate) {
        $whereParts[] = 'created_at >= ?';
        $params[] = $startDate . ' 00:00:00';
    }
    if ($endDate) {
        $whereParts[] = 'created_at <= ?';
        $params[] = $endDate . ' 23:59:59';
    }

    $whereClause = !empty($whereParts) ? 'WHERE ' . implode(' AND ', $whereParts) : '';

    // Stats Globales
    $stmtGlobal = $pdo->prepare("
        SELECT 
            COUNT(*) as total_visits, 
            COALESCE(AVG(time_spent), 0) as avg_time, 
            COALESCE(SUM(is_bounce), 0) as total_bounces 
        FROM analytics 
        $whereClause
    ");
    $stmtGlobal->execute($params);
    $global = $stmtGlobal->fetch(PDO::FETCH_ASSOC);

    // Stats Diarias
    $stmtDaily = $pdo->prepare("
        SELECT 
            DATE(created_at) as date, 
            COALESCE(AVG(time_spent), 0) as avg_time, 
            COUNT(*) as visits, 
            COALESCE(SUM(is_bounce), 0) as bounces 
        FROM analytics 
        $whereClause 
        GROUP BY DATE(created_at) 
        ORDER BY date ASC
    ");
    $stmtDaily->execute($params);
    $dailyRows = $stmtDaily->fetchAll(PDO::FETCH_ASSOC);

    // Rellenar días intermedios con 0 para que la gráfica represente una línea de tiempo continua
    $daily = [];
    if ($startDate && $endDate) {
        $startTs = strtotime($startDate);
        $endTs = strtotime($endDate);
        $diffDays = round(($endTs - $startTs) / 86400);

        if ($diffDays >= 0 && $diffDays <= 120) {
            $byDate = [];
            foreach ($dailyRows as $r) {
                $byDate[$r['date']] = $r;
            }
            for ($t = $startTs; $t <= $endTs; $t += 86400) {
                $dStr = date('Y-m-d', $t);
                if (isset($byDate[$dStr])) {
                    $daily[] = $byDate[$dStr];
                } else {
                    $daily[] = [
                        'date' => $dStr,
                        'avg_time' => 0,
                        'visits' => 0,
                        'bounces' => 0
                    ];
                }
            }
        } else {
            $daily = $dailyRows;
        }
    } else {
        $daily = $dailyRows;
    }

    // Stats por página
    $stmtPages = $pdo->prepare("
        SELECT 
            page, 
            COALESCE(AVG(time_spent), 0) as avg_time, 
            COUNT(*) as visits 
        FROM analytics 
        $whereClause 
        GROUP BY page 
        ORDER BY visits DESC 
        LIMIT 10
    ");
    $stmtPages->execute($params);
    $pages = $stmtPages->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true, 
        'global' => $global,
        'daily' => $daily,
        'pages' => $pages,
        'filter' => [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener analíticas: ' . $e->getMessage()]);
}
?>
