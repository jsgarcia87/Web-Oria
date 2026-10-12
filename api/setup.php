<?php
// api/setup.php
// Ejecuta este archivo una sola vez en el navegador (ej: tudominio.com/api/setup.php) para crear las tablas
require 'db.php';

try {
    // 1. Crear tabla de usuarios
    $sql_users = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL
    )";
    $pdo->exec($sql_users);

    // 2. Crear tabla de contenidos
    $sql_content = "CREATE TABLE IF NOT EXISTS web_content (
        id VARCHAR(100) PRIMARY KEY,
        content_value TEXT NOT NULL,
        page VARCHAR(50) NOT NULL
    )";
    $pdo->exec($sql_content);

    // 2.1 Crear tabla de mensajes de contacto
    $sql_messages = "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(255) NOT NULL,
        empresa VARCHAR(255) DEFAULT '',
        email VARCHAR(255) NOT NULL,
        telefono VARCHAR(50) DEFAULT '',
        tema VARCHAR(255) DEFAULT '',
        mail_sent TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql_messages);

    // 2.2 Crear tabla de configuración
    $sql_settings = "CREATE TABLE IF NOT EXISTS settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT NOT NULL
    )";
    $pdo->exec($sql_settings);

    // 3. Crear el usuario por defecto (admin / admin123)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = $pdo->prepare("INSERT INTO admin_users (username, password_hash) VALUES ('admin', ?)");
        $insert->execute([$hash]);
        echo "Usuario 'admin' creado con contraseña 'admin123'.<br>";
    }

    // 4. Insertar un contenido de prueba para la portada
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'titulo_portada'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('titulo_portada', 'Bienvenido a Web Oria', 'index')");
        $insert->execute();
        echo "Contenido de prueba insertado.<br>";
    }

        // Contenidos extraídos automáticamente
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-cookies_h1_1'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-cookies_h1_1', ?, 'politica-cookies')");
        $insert->execute(['POLÍTICA DE COOKIES']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-cookies_h2_2'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-cookies_h2_2', ?, 'politica-cookies')");
        $insert->execute(['1. ¿Qué son las cookies?']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-cookies_p_3'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-cookies_p_3', ?, 'politica-cookies')");
        $insert->execute(['Las cookies son pequeños archivos que pueden almacenarse en el dispositivo del usuario cuando visita una página web. Permiten, entre otras funciones, garantizar el funcionamiento de la web, recordar determinadas preferencias u obtener información sobre su utilización.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-cookies_h2_4'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-cookies_h2_4', ?, 'politica-cookies')");
        $insert->execute(['2. Cookies utilizadas']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-cookies_p_5'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-cookies_p_5', ?, 'politica-cookies')");
        $insert->execute(['El sitio web <a href="http://oriaestrategia.com/">oriaestrategia.com</a> podrá utilizar cookies propias y de terceros.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-cookies_p_6'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-cookies_p_6', ?, 'politica-cookies')");
        $insert->execute(['Dependiendo de las herramientas instaladas, podrán utilizarse cookies técnicas necesarias para el funcionamiento y seguridad de la web; cookies de preferencias; cookies de análisis destinadas a obtener información estadística sobre la utilización de la web; y, en su caso, cookies publicitarias destinadas a medir o personalizar acciones publicitarias.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-cookies_h2_7'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-cookies_h2_7', ?, 'politica-cookies')");
        $insert->execute(['3. Consentimiento']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-cookies_p_8'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-cookies_p_8', ?, 'politica-cookies')");
        $insert->execute(['Las cookies que no sean estrictamente necesarias únicamente se instalarán cuando el usuario haya prestado su consentimiento.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-cookies_p_9'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-cookies_p_9', ?, 'politica-cookies')");
        $insert->execute(['El usuario podrá aceptar, rechazar o configurar las cookies mediante el panel habilitado en la web.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-cookies_h2_10'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-cookies_h2_10', ?, 'politica-cookies')");
        $insert->execute(['4. Modificación del consentimiento']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-cookies_p_11'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-cookies_p_11', ?, 'politica-cookies')");
        $insert->execute(['El usuario podrá modificar o retirar posteriormente su consentimiento mediante el sistema de configuración de cookies disponible en el sitio web.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-cookies_h2_12'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-cookies_h2_12', ?, 'politica-cookies')");
        $insert->execute(['5. Cookies de terceros']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-cookies_p_13'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-cookies_p_13', ?, 'politica-cookies')");
        $insert->execute(['Cuando se utilicen servicios de terceros que impliquen la instalación de cookies, se facilitará información sobre dichos terceros, la finalidad de las cookies utilizadas y su duración.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-cookies_p_14'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-cookies_p_14', ?, 'politica-cookies')");
        $insert->execute(['Utilizamos cookies propias y de terceros para mejorar tu experiencia y analizar el tráfico de nuestra web. Al continuar navegando, aceptas nuestra <a href="politica-cookies.html">Política de Cookies</a>.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_h1_1'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_h1_1', ?, 'politica-privacidad')");
        $insert->execute(['POLÍTICA DE PRIVACIDAD']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_h2_2'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_h2_2', ?, 'politica-privacidad')");
        $insert->execute(['1. Responsable del tratamiento']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_h2_3'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_h2_3', ?, 'politica-privacidad')");
        $insert->execute(['2. Datos tratados']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_p_4'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_p_4', ?, 'politica-privacidad')");
        $insert->execute(['ORIA podrá tratar los datos personales que el usuario facilite a través de los formularios de contacto, correo electrónico u otros medios disponibles en la web.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_p_5'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_p_5', ?, 'politica-privacidad')");
        $insert->execute(['Estos datos pueden incluir nombre y apellidos, correo electrónico, teléfono, empresa y aquella información que el propio usuario facilite en su consulta.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_h2_6'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_h2_6', ?, 'politica-privacidad')");
        $insert->execute(['3. Finalidades del tratamiento']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_p_7'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_p_7', ?, 'politica-privacidad')");
        $insert->execute(['Los datos podrán ser tratados para atender consultas y solicitudes de información, gestionar el contacto con potenciales clientes, gestionar las relaciones profesionales o comerciales derivadas de la contratación de servicios y, cuando corresponda legalmente, realizar comunicaciones comerciales.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_h2_8'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_h2_8', ?, 'politica-privacidad')");
        $insert->execute(['4. Legitimación']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_p_9'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_p_9', ?, 'politica-privacidad')");
        $insert->execute(['La base jurídica del tratamiento dependerá de cada caso y podrá consistir en el consentimiento del interesado, la aplicación de medidas precontractuales solicitadas por este, la ejecución de una relación contractual o el cumplimiento de obligaciones legales.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_p_10'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_p_10', ?, 'politica-privacidad')");
        $insert->execute(['Cuando el tratamiento se base en el consentimiento, este podrá retirarse en cualquier momento.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_h2_11'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_h2_11', ?, 'politica-privacidad')");
        $insert->execute(['5. Conservación de los datos']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_p_12'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_p_12', ?, 'politica-privacidad')");
        $insert->execute(['Los datos personales se conservarán durante el tiempo necesario para cumplir la finalidad para la que fueron recogidos y, posteriormente, durante los plazos exigidos por la legislación aplicable.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_h2_13'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_h2_13', ?, 'politica-privacidad')");
        $insert->execute(['6. Destinatarios']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_p_14'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_p_14', ?, 'politica-privacidad')");
        $insert->execute(['Los datos no serán vendidos ni cedidos a terceros salvo obligación legal.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_p_15'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_p_15', ?, 'politica-privacidad')");
        $insert->execute(['Podrán tener acceso a ellos proveedores que presten servicios necesarios para el funcionamiento de ORIA, como servicios de alojamiento web, correo electrónico o herramientas tecnológicas, cuando sea necesario para la prestación de dichos servicios y conforme a la normativa aplicable.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_h2_16'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_h2_16', ?, 'politica-privacidad')");
        $insert->execute(['7. Derechos']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_p_17'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_p_17', ?, 'politica-privacidad')");
        $insert->execute(['El usuario podrá ejercer sus derechos de acceso, rectificación, supresión, oposición, limitación del tratamiento y portabilidad, cuando correspondan, escribiendo a: <a href="mailto:jorge@oriaestrategia.com">jorge@oriaestrategia.com</a>']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_p_18'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_p_18', ?, 'politica-privacidad')");
        $insert->execute(['Asimismo, podrá presentar una reclamación ante la Agencia Española de Protección de Datos.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_h2_19'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_h2_19', ?, 'politica-privacidad')");
        $insert->execute(['8. Seguridad']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_p_20'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_p_20', ?, 'politica-privacidad')");
        $insert->execute(['ORIA adoptará las medidas técnicas y organizativas adecuadas para proteger los datos personales frente a su pérdida, alteración, acceso o tratamiento no autorizado.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_h2_21'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_h2_21', ?, 'politica-privacidad')");
        $insert->execute(['9. Actualización de la política']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_p_22'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_p_22', ?, 'politica-privacidad')");
        $insert->execute(['Esta Política de Privacidad podrá modificarse cuando resulte necesario como consecuencia de cambios legales, técnicos o en los tratamientos de datos realizados.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'politica-privacidad_p_23'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('politica-privacidad_p_23', ?, 'politica-privacidad')");
        $insert->execute(['Utilizamos cookies propias y de terceros para mejorar tu experiencia y analizar el tráfico de nuestra web. Al continuar navegando, aceptas nuestra <a href="politica-cookies.html">Política de Cookies</a>.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_h1_1'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_h1_1', ?, 'index')");
        $insert->execute(['ORIA — Consultoría de Relevo Generacional y Evolución Organizativa en Empresas Familiares']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_h2_2'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_h2_2', ?, 'index')");
        $insert->execute(['Hola.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_p_3'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_p_3', ?, 'index')");
        $insert->execute(['No entiendo que hemos hecho mal en el mundillo de la empresa familiar para que nos vean como anticuados, conflictivos o poco profesionales...']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_p_4'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_p_4', ?, 'index')");
        $insert->execute(['Miro los rankings empresariales por provincias y siempre estamos ahí arriba. Empresas familiares generando la mayor parte de la riqueza y empleo del país, con historias empresariales que bien merecerían una serie de Netflix.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_p_5'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_p_5', ?, 'index')");
        $insert->execute(['El problema es que esa imagen de empresa aburrida nos pone en desventaja en una guerra por el talento cada vez más difícil. Y no hablamos solo de atraer profesionales de fuera...']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_p_6'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_p_6', ?, 'index')");
        $insert->execute(['Hablamos de conseguir que la siguiente generación de la familia quiera y sea capaz de continuar el proyecto. <span class="orange underline">Ese es el momento crítico en el que muchas familias empresarias terminan su historia.</span>']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_h2_7'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_h2_7', ?, 'index')");
        $insert->execute(['Y por eso existe ORIA']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_p_8'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_p_8', ?, 'index')");
        $insert->execute(['Porque venimos de empresa familiar y sabemos que la continuidad pasa por ser capaz de atraer a la siguiente generación.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_p_9'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_p_9', ?, 'index')");
        $insert->execute(['Ponemos orden al proceso de relevo generacional y construimos una empresa que la siguiente generación quiera liderar.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_h2_10'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_h2_10', ?, 'index')");
        $insert->execute(['PREPARANDO<br>LAS TAZAS...']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_p_11'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_p_11', ?, 'index')");
        $insert->execute(['Tranquilidad. Este primer<br>café invita la casa y no<br>compromete a nada.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_p_12'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_p_12', ?, 'index')");
        $insert->execute(['Jorge Gómez Salmerón']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_p_13'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_p_13', ?, 'index')");
        $insert->execute(['FUNDADOR']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_p_14'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_p_14', ?, 'index')");
        $insert->execute(['jorge@oriaestrategia.com']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_p_15'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_p_15', ?, 'index')");
        $insert->execute(['<a href="https://wa.me/34614694327" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center; gap: 0.5rem; color: inherit; text-decoration: none;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                    614 69 43 27
                                </a>']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_p_16'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_p_16', ?, 'index')");
        $insert->execute(['Si tienes cualquier duda puedes escribirme por WhatsApp.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'index_p_17'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('index_p_17', ?, 'index')");
        $insert->execute(['Utilizamos cookies propias y de terceros para mejorar tu experiencia y analizar el tráfico de nuestra web. Al continuar navegando, aceptas nuestra <a href="politica-cookies.html">Política de Cookies</a>.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = '404_h1_1'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('404_h1_1', ?, '404')");
        $insert->execute(['404']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = '404_h2_2'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('404_h2_2', ?, '404')");
        $insert->execute(['Página no encontrada']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = '404_p_3'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('404_p_3', ?, '404')");
        $insert->execute(['Lo sentimos, la página que estás buscando no existe o ha sido movida.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = '404_p_4'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('404_p_4', ?, '404')");
        $insert->execute(['Utilizamos cookies propias y de terceros para mejorar tu experiencia y analizar el tráfico de nuestra web. Al continuar navegando, aceptas nuestra <a href="politica-cookies.html">Política de Cookies</a>.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'trabajo_h1_1'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('trabajo_h1_1', ?, 'trabajo')");
        $insert->execute(['Así es como trabajo — Consultoría de Relevo Generacional en ORIA']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'trabajo_h2_2'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('trabajo_h2_2', ?, 'trabajo')");
        $insert->execute(['Mediante sesiones de consultoría a 200€ la hora.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'trabajo_p_3'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('trabajo_p_3', ?, 'trabajo')");
        $insert->execute(['La primera llamada es sin coste, que nos servirá para ver en que momento del proceso sucesorio estáis (Transición, sucesión o transmisión) y preparar la fase más importante: <span class="orange underline">El diagnóstico.</span>']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'trabajo_p_4'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('trabajo_p_4', ?, 'trabajo')");
        $insert->execute(['Ese será el objetivo de las primeras sesiones de consultoría: entender el estado de forma de la empresa y la familia.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'trabajo_p_5'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('trabajo_p_5', ?, 'trabajo')");
        $insert->execute(['Identificaremos bloqueos, conflictos y formas de funcionar que puedan poner en peligro la evolución de la familia empresaria.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'trabajo_p_6'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('trabajo_p_6', ?, 'trabajo')");
        $insert->execute(['Cuando tenga claro qué está pasando, os presentaré <span class="orange underline">el diagnóstico y un plan de acción.</span> Dependiendo de cada familia, trabajaremos uno o varios de estos ámbitos:']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'trabajo_h2_7'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('trabajo_h2_7', ?, 'trabajo')");
        $insert->execute(['Comunicación']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'trabajo_p_8'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('trabajo_p_8', ?, 'trabajo')");
        $insert->execute(['Evitar que los problemas de la empresa acaben en la comida del domingo y que los familiares acaben en la sala de reuniones, haciendo de mediador y traductor entre generaciones con distintas formas de ver las cosas.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'trabajo_h2_9'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('trabajo_h2_9', ?, 'trabajo')");
        $insert->execute(['Gobierno']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'trabajo_p_10'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('trabajo_p_10', ?, 'trabajo')");
        $insert->execute(['Rediseñar la estructura, profesionalizando los órganos de gobierno que sean necesarios y que las personas tengan claro su rol de accionista, consejero, directivo, profesional o familiar.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'trabajo_h2_11'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('trabajo_h2_11', ?, 'trabajo')");
        $insert->execute(['Sucesión']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'trabajo_p_12'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('trabajo_p_12', ?, 'trabajo')");
        $insert->execute(['Acordar las políticas sucesorias, diseñar el plan de carrera del sucesor y trabajar la delegación progresiva de responsabilidades y la salida paulatina del predecesor.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'trabajo_p_13'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('trabajo_p_13', ?, 'trabajo')");
        $insert->execute(['Utilizamos cookies propias y de terceros para mejorar tu experiencia y analizar el tráfico de nuestra web. Al continuar navegando, aceptas nuestra <a href="politica-cookies.html">Política de Cookies</a>.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'aviso-legal_h1_1'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('aviso-legal_h1_1', ?, 'aviso-legal')");
        $insert->execute(['AVISO LEGAL']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'aviso-legal_h2_2'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('aviso-legal_h2_2', ?, 'aviso-legal')");
        $insert->execute(['1. Datos identificativos']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'aviso-legal_p_3'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('aviso-legal_p_3', ?, 'aviso-legal')");
        $insert->execute(['En cumplimiento de lo establecido en la Ley 34/2002, de Servicios de la Sociedad de la Información y de Comercio Electrónico (LSSI-CE), se informa de los siguientes datos:']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'aviso-legal_p_4'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('aviso-legal_p_4', ?, 'aviso-legal')");
        $insert->execute(['La actividad desarrollada a través de ORIA está relacionada con servicios de consultoría y asesoramiento empresarial.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'aviso-legal_h2_5'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('aviso-legal_h2_5', ?, 'aviso-legal')");
        $insert->execute(['2. Objeto']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'aviso-legal_p_6'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('aviso-legal_p_6', ?, 'aviso-legal')");
        $insert->execute(['El presente sitio web tiene como finalidad proporcionar información sobre ORIA, sus servicios y contenidos relacionados con la empresa familiar, así como facilitar el contacto de personas interesadas en dichos servicios.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'aviso-legal_p_7'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('aviso-legal_p_7', ?, 'aviso-legal')");
        $insert->execute(['El acceso y utilización de la web atribuye la condición de usuario e implica la aceptación de las condiciones establecidas en este Aviso Legal.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'aviso-legal_h2_8'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('aviso-legal_h2_8', ?, 'aviso-legal')");
        $insert->execute(['3. Propiedad intelectual e industrial']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'aviso-legal_p_9'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('aviso-legal_p_9', ?, 'aviso-legal')");
        $insert->execute(['Los contenidos de este sitio web, incluyendo textos, fotografías, diseños, logotipos, elementos gráficos y demás materiales, están protegidos por la normativa aplicable en materia de propiedad intelectual e industrial.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'aviso-legal_p_10'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('aviso-legal_p_10', ?, 'aviso-legal')");
        $insert->execute(['No se permite su reproducción, distribución, modificación o utilización con fines comerciales sin autorización previa del titular, salvo en los casos permitidos legalmente.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'aviso-legal_h2_11'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('aviso-legal_h2_11', ?, 'aviso-legal')");
        $insert->execute(['4. Responsabilidad']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'aviso-legal_p_12'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('aviso-legal_p_12', ?, 'aviso-legal')");
        $insert->execute(['ORIA procura que la información publicada en la web sea correcta y esté actualizada, pero no garantiza la inexistencia de errores u omisiones.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'aviso-legal_p_13'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('aviso-legal_p_13', ?, 'aviso-legal')");
        $insert->execute(['El titular tampoco será responsable de los contenidos, servicios o funcionamiento de sitios web de terceros a los que pueda accederse mediante enlaces incluidos en esta web.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'aviso-legal_h2_14'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('aviso-legal_h2_14', ?, 'aviso-legal')");
        $insert->execute(['5. Legislación aplicable']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'aviso-legal_p_15'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('aviso-legal_p_15', ?, 'aviso-legal')");
        $insert->execute(['El presente Aviso Legal se rige por la legislación española.']);
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM web_content WHERE id = 'aviso-legal_p_16'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $insert = $pdo->prepare("INSERT INTO web_content (id, content_value, page) VALUES ('aviso-legal_p_16', ?, 'aviso-legal')");
        $insert->execute(['Utilizamos cookies propias y de terceros para mejorar tu experiencia y analizar el tráfico de nuestra web. Al continuar navegando, aceptas nuestra <a href="politica-cookies.html">Política de Cookies</a>.']);
    }
    echo "<b>¡Instalación completada!</b> Ya puedes borrar este archivo setup.php por seguridad.";

} catch (PDOException $e) {
    echo "Error en la instalación: " . $e->getMessage();
}
?>
