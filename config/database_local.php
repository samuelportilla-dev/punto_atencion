<?php
/**
 * Configuración de Base de Datos - Ejemplos para diferentes servidores
 * Copia este archivo y renómbralo según tu servidor
 */

// ========================================
// CONFIGURACIÓN PARA XAMPP (Windows)
// ========================================
if (file_exists('C:/xampp/mysql/bin/mysql.exe')) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'el_punto_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');  // XAMPP por defecto no tiene contraseña
}

// ========================================
// CONFIGURACIÓN PARA WAMP (Windows)
// ========================================
elseif (file_exists('C:/wamp64/bin/mysql/mysql8.0.31/bin/mysql.exe')) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'el_punto_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');  // WAMP por defecto no tiene contraseña
}

// ========================================
// CONFIGURACIÓN PARA MAMP (Mac/Windows)
// ========================================
elseif (file_exists('/Applications/MAMP/Library/bin/mysql')) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'el_punto_db');
    define('DB_USER', 'root');
    define('DB_PASS', 'root');  // MAMP usa 'root' como contraseña
}

// ========================================
// CONFIGURACIÓN PARA SERVIDOR LINUX
// ========================================
elseif (PHP_OS === 'Linux') {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'el_punto_db');
    define('DB_USER', 'root');
    define('DB_PASS', '');  // Cambia por tu contraseña de MySQL
}

// ========================================
// CONFIGURACIÓN PARA HOSTING COMPARTIDO
// ========================================
else {
    // Ejemplo para hosting compartido
    define('DB_HOST', 'localhost');  // O la IP del servidor MySQL
    define('DB_NAME', 'tu_usuario_el_punto_db');  // Normalmente incluye tu usuario
    define('DB_USER', 'tu_usuario_mysql');        // Usuario de MySQL del hosting
    define('DB_PASS', 'tu_contraseña_mysql');     // Contraseña de MySQL del hosting
}

// ========================================
// CONFIGURACIÓN DE CONEXIÓN
// ========================================
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// Configuración de timeouts
define('DB_CONNECT_TIMEOUT', 10);
define('DB_READ_TIMEOUT', 30);

// Función para crear conexión PDO con manejo de errores
function createDatabaseConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATE,
            PDO::ATTR_TIMEOUT => DB_CONNECT_TIMEOUT,
            PDO::MYSQL_ATTR_READ_TIMEOUT => DB_READ_TIMEOUT
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        error_log("Error de conexión a BD: " . $e->getMessage());
        return null;
    }
}

// Función para verificar si la base de datos existe
function databaseExists() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . DB_NAME . "'");
        return $stmt->rowCount() > 0;
        
    } catch (PDOException $e) {
        error_log("Error verificando existencia de BD: " . $e->getMessage());
        return false;
    }
}

// Función para crear la base de datos si no existe
function createDatabaseIfNotExists() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        
        $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE;
        $pdo->exec($sql);
        
        return true;
        
    } catch (PDOException $e) {
        error_log("Error creando BD: " . $e->getMessage());
        return false;
    }
}

// Función para verificar si las tablas necesarias existen
function checkRequiredTables() {
    try {
        $pdo = createDatabaseConnection();
        if (!$pdo) return false;
        
        $requiredTables = [
            'usuarios',
            'servicios', 
            'mesas',
            'pedidos',
            'detalle_pedidos',
            'contactos',
            'configuracion'
        ];
        
        foreach ($requiredTables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() == 0) {
                error_log("Tabla requerida no encontrada: $table");
                return false;
            }
        }
        
        return true;
        
    } catch (PDOException $e) {
        error_log("Error verificando tablas: " . $e->getMessage());
        return false;
    }
}

// Verificar conexión al cargar el archivo
if (!function_exists('DEBUG_MODE')) {
    define('DEBUG_MODE', true);
}

// Intentar crear la base de datos si no existe
if (!databaseExists()) {
    if (DEBUG_MODE) {
        error_log("Base de datos no existe, intentando crearla...");
    }
    createDatabaseIfNotExists();
}
?>
