<?php
/**
 * Configuración de Base de Datos - El Punto de Atención
 * Archivo separado para manejo de conexiones a BD
 */

// Configuración de la base de datos
// Configuración para hosting compartido - saportil.corsajetec.com
define('DB_HOST', 'localhost');        // Servidor MySQL del hosting
define('DB_NAME', 'saportil_punto');   // Base de datos específica del hosting
define('DB_USER', 'saportil_punto');   // Usuario de MySQL del hosting
define('DB_PASS', '0000');             // Contraseña de MySQL del hosting

// Configuración de conexión
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

// Función para hacer backup de la base de datos
function backupDatabase($filename = null) {
    if (!$filename) {
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    }
    
    $backupPath = __DIR__ . '/../backups/' . $filename;
    
    // Crear directorio de backups si no existe
    if (!is_dir(__DIR__ . '/../backups/')) {
        mkdir(__DIR__ . '/../backups/', 0755, true);
    }
    
    $command = sprintf(
        'mysqldump --host=%s --user=%s --password=%s %s > %s',
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME,
        $backupPath
    );
    
    exec($command, $output, $return);
    
    return $return === 0;
}

// Función para restaurar base de datos desde backup
function restoreDatabase($backupFile) {
    $backupPath = __DIR__ . '/../backups/' . $backupFile;
    
    if (!file_exists($backupPath)) {
        return false;
    }
    
    $command = sprintf(
        'mysql --host=%s --user=%s --password=%s %s < %s',
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME,
        $backupPath
    );
    
    exec($command, $output, $return);
    
    return $return === 0;
}

// Función para limpiar backups antiguos
function cleanOldBackups($days = 30) {
    $backupDir = __DIR__ . '/../backups/';
    $files = glob($backupDir . 'backup_*.sql');
    $cutoff = time() - ($days * 24 * 60 * 60);
    
    foreach ($files as $file) {
        if (filemtime($file) < $cutoff) {
            unlink($file);
        }
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
