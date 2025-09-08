<?php
/**
 * Configuración de Base de Datos - Hosting Compartido
 * saportil.corsajetec.com
 */

// Configuración específica para el hosting
define('DB_HOST', 'localhost');
define('DB_NAME', 'saportil_punto');
define('DB_USER', 'saportil_punto');
define('DB_PASS', '0000');

// Configuración de conexión
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// Configuración de timeouts para hosting compartido
define('DB_CONNECT_TIMEOUT', 30);
define('DB_READ_TIMEOUT', 60);

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

// Función para obtener información de la base de datos
function getDatabaseInfo() {
    try {
        $pdo = createDatabaseConnection();
        if (!$pdo) {
            return ['error' => 'No se pudo conectar a la base de datos'];
        }
        
        $info = [];
        
        // Información del servidor
        $info['server_version'] = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        $info['client_version'] = $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION);
        $info['connection_status'] = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
        
        // Tamaño de la base de datos
        $stmt = $pdo->query("
            SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
            FROM information_schema.tables 
            WHERE table_schema = '" . DB_NAME . "'
        ");
        $info['database_size'] = $stmt->fetchColumn();
        
        // Número de tablas
        $stmt = $pdo->query("
            SELECT COUNT(*) FROM information_schema.tables 
            WHERE table_schema = '" . DB_NAME . "'
        ");
        $info['table_count'] = $stmt->fetchColumn();
        
        return $info;
        
    } catch (PDOException $e) {
        return ['error' => 'Error obteniendo información: ' . $e->getMessage()];
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
