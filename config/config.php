<?php
/**
 * Configuración del Sistema El Punto
 * Archivo de configuración principal
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'saportil_punto');
define('DB_USER', 'saportil_punto');
define('DB_PASS', '0000');

// Configuración del sitio
define('SITE_NAME', 'El Punto');
define('SITE_URL', 'https://saportil.corsajetec.com/punto_atencion');
define('SITE_DESCRIPTION', 'Sistema de Intercomunicación Cocina & Meseros');

// Configuración de sesión
define('SESSION_NAME', 'elpunto_session');
define('SESSION_LIFETIME', 3600); // 1 hora

// Configuración de zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de errores (cambiar a FALSE en producción)
define('DEBUG_MODE', true);

// Configuración de moneda
if (!defined('CURRENCY')) {
    define('CURRENCY', 'USD');
}
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', '$');
}

// Configuración de impuestos
define('TAX_RATE', 0.10); // 10%

// Configuración de archivos
define('UPLOAD_PATH', '../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Configuración de paginación
define('ITEMS_PER_PAGE', 10);

// Configuración de seguridad
define('PASSWORD_MIN_LENGTH', 8);
define('LOGIN_MAX_ATTEMPTS', 3);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutos

// Configuración de email (para futuras funcionalidades)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'tu-email@gmail.com');
define('SMTP_PASS', 'tu-password');

// Configuración de notificaciones
define('NOTIFICATION_SOUND', true);
define('AUTO_REFRESH_INTERVAL', 30000); // 30 segundos

// Configuración de reportes
define('REPORTS_PATH', '../reports/');
define('EXPORT_FORMATS', ['pdf', 'excel', 'csv']);

// Configuración de backup
define('BACKUP_PATH', '../backups/');
define('BACKUP_RETENTION_DAYS', 30);

// Configuración de logs
define('LOG_PATH', '../logs/');
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR

// Configuración de caché
define('CACHE_ENABLED', true);
define('CACHE_PATH', '../cache/');
define('CACHE_LIFETIME', 3600); // 1 hora

// Configuración de API (para futuras integraciones)
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100); // requests per hour

// Configuración de idioma
define('DEFAULT_LANGUAGE', 'es');
define('AVAILABLE_LANGUAGES', ['es', 'en']);

// Configuración de temas
define('DEFAULT_THEME', 'default');
define('AVAILABLE_THEMES', ['default', 'dark', 'light']);

// Configuración de módulos
define('MODULES_ENABLED', [
    'pedidos' => true,
    'mesas' => true,
    'servicios' => true,
    'usuarios' => true,
    'reportes' => true,
    'configuracion' => true
]);

// Configuración de permisos por rol
define('PERMISSIONS', [
    'Mesero' => [
        'ver_pedidos' => true,
        'crear_pedidos' => true,
        'ver_mesas' => true,
        'ver_servicios' => true,
        'ver_historial' => true
    ],
    'Cocinero' => [
        'ver_pedidos' => true,
        'actualizar_estado_pedidos' => true,
        'ver_servicios' => true,
        'ver_mesas' => true
    ],
    'Administrador' => [
        'ver_pedidos' => true,
        'crear_pedidos' => true,
        'ver_mesas' => true,
        'ver_servicios' => true,
        'ver_historial' => true,
        'gestionar_usuarios' => true,
        'gestionar_configuracion' => true,
        'ver_reportes' => true
    ]
]);

// Configuración de estados de pedidos
define('PEDIDO_ESTADOS', [
    'pendiente' => 'Pendiente',
    'en_proceso' => 'En Cocina',
    'listo' => 'Listo',
    'entregado' => 'Entregado',
    'cancelado' => 'Cancelado'
]);

// Configuración de estados de mesas
define('MESA_ESTADOS', [
    'disponible' => 'Disponible',
    'ocupada' => 'Ocupada',
    'reservada' => 'Reservada',
    'limpieza' => 'Limpieza'
]);

// Configuración de categorías de servicios
define('SERVICIO_CATEGORIAS', [
    'Entrada' => 'Entrada',
    'Plato Principal' => 'Plato Principal',
    'Postre' => 'Postre',
    'Bebida' => 'Bebida',
    'Sistema' => 'Sistema'
]);

// Configuración de roles de usuario
define('USER_ROLES', [
    'Mesero' => 'Mesero',
    'Cocinero' => 'Cocinero',
    'Administrador' => 'Administrador'
]);

// Configuración de estados de contacto
define('CONTACTO_ESTADOS', [
    'Nuevo' => 'Nuevo',
    'Leído' => 'Leído',
    'Respondido' => 'Respondido'
]);

// Función para formatear moneda está definida en includes/functions.php

// Función para formatear fecha está definida en includes/functions.php

// Función para generar token CSRF está definida en includes/functions.php

// Función para verificar token CSRF está definida en includes/functions.php

// Función para sanitizar entrada (definida en includes/db.php)

// Funciones utilitarias están definidas en includes/functions.php

// Función para calcular total con impuestos
function calculateTotalWithTax($subtotal) {
    return $subtotal + ($subtotal * TAX_RATE);
}

// Función para verificar permisos está definida en includes/functions.php

// Función para registrar logs está definida en includes/functions.php

// Función para redireccionar
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Función para mostrar mensajes
function showMessage($message, $type = 'info') {
    $_SESSION['message'] = [
        'text' => $message,
        'type' => $type
    ];
}

// Función para obtener mensaje está definida en includes/functions.php

// Configuración de errores
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configuración de caracteres
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

// Configuración de límites de memoria y tiempo
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300); // 5 minutos

// Configuración de headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Configuración de caché para archivos estáticos
if (isset($_GET['static'])) {
    header('Cache-Control: public, max-age=31536000');
    header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
}

// Función para verificar la conexión a la base de datos
function checkDatabaseConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return true;
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            error_log("Error de conexión a BD: " . $e->getMessage());
        }
        return false;
    }
}

// Función para obtener datos de ejemplo cuando la BD no está disponible
function getFallbackData($type) {
    switch ($type) {
        case 'stats':
            return [
                'total_pedidos' => 25,
                'pedidos_pendientes' => 8,
                'pedidos_cocina' => 5,
                'pedidos_listos' => 12,
                'ventas_hoy' => 450.75,
                'mesas_ocupadas' => 6
            ];
        case 'servicios':
            return [
                [
                    'id' => 1,
                    'nombre' => 'Ensalada César',
                    'descripcion' => 'Lechuga romana, crutones, parmesano',
                    'precio' => 8.50,
                    'categoria' => 'Entrada',
                    'disponible' => 1,
                    'imagen' => 'ensalada-cesar.jpg'
                ],
                [
                    'id' => 2,
                    'nombre' => 'Pasta Carbonara',
                    'descripcion' => 'Pasta fresca con salsa cremosa',
                    'precio' => 15.50,
                    'categoria' => 'Plato Principal',
                    'disponible' => 1,
                    'imagen' => 'pasta-carbonara.jpg'
                ]
            ];
        case 'mesas':
            return [
                [
                    'id' => 1,
                    'numero' => 1,
                    'capacidad' => 4,
                    'estado' => 'disponible',
                    'ubicacion' => 'Terraza'
                ],
                [
                    'id' => 2,
                    'numero' => 2,
                    'capacidad' => 6,
                    'estado' => 'ocupada',
                    'ubicacion' => 'Interior'
                ]
            ];
        default:
            return [];
    }
}

// Verificar conexión al cargar la configuración
if (!checkDatabaseConnection() && DEBUG_MODE) {
    error_log("ADVERTENCIA: No se puede conectar a la base de datos. El sistema funcionará con datos de ejemplo.");
}
?>
