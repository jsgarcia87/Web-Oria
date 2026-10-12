<?php
// api/get_content.php
header('Content-Type: application/json');
require 'db.php';

$page = isset($_GET['page']) ? $_GET['page'] : '';

try {
    // Obtener la lista de páginas únicas para el filtro
    $stmt_pages = $pdo->query("SELECT DISTINCT page FROM web_content WHERE page != '' ORDER BY page ASC");
    $pages_list = $stmt_pages->fetchAll(PDO::FETCH_COLUMN);

    if ($page) {
        $stmt = $pdo->prepare('SELECT id, content_value, page FROM web_content WHERE page = ?');
        $stmt->execute([$page]);
    } else {
        $stmt = $pdo->query('SELECT id, content_value, page FROM web_content');
    }

    $results = $stmt->fetchAll();
    $response = [];
    $details = [];
    
    foreach ($results as $row) {
        // Formato original para que funcione la web pública (js/content-loader.js)
        $response[$row['id']] = $row['content_value'];
        // Formato ampliado para el panel de administración
        $details[$row['id']] = [
            'value' => $row['content_value'],
            'page' => $row['page']
        ];
    }

    // Obtener extensiones públicas configuradas (Google Analytics, Clarity, scripts personalizados)
    $extensions = [
        'google_analytics' => '',
        'clarity' => '',
        'custom_head' => '',
        'custom_body_open' => '',
        'custom_body' => ''
    ];
    try {
        $stmt_ext = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'ext_%'");
        while ($row = $stmt_ext->fetch()) {
            $key = str_replace('ext_', '', $row['setting_key']);
            $extensions[$key] = $row['setting_value'];
        }
    } catch (Exception $e) { }

    echo json_encode([
        'success' => true, 
        'data' => $response, 
        'details' => $details,
        'pages' => $pages_list,
        'extensions' => $extensions
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al obtener contenidos']);
}
?>
