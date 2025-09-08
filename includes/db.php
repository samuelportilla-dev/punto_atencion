<?php
/**
 * Conexión a la Base de Datos
 * Sistema El Punto
 */

// Incluir configuración de base de datos
// Usar configuración específica del hosting si estamos en producción
$hostingConfig = __DIR__ . '/../config/database_hosting.php';
$localConfig = __DIR__ . '/../config/database.php';

if (strpos($_SERVER['HTTP_HOST'] ?? '', 'corsajetec.com') !== false && file_exists($hostingConfig)) {
    require_once $hostingConfig;
} elseif (file_exists($localConfig)) {
    require_once $localConfig;
} else {
    // Fallback: intentar cargar cualquier configuración disponible
    if (file_exists($hostingConfig)) {
        require_once $hostingConfig;
    } elseif (file_exists($localConfig)) {
        require_once $localConfig;
    } else {
        // Configuración mínima de emergencia solo si no están definidas
        if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
        if (!defined('DB_NAME')) define('DB_NAME', 'el_punto');
        if (!defined('DB_USER')) define('DB_USER', 'root');
        if (!defined('DB_PASS')) define('DB_PASS', '');
        if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');
        if (!defined('DB_COLLATE')) define('DB_COLLATE', 'utf8mb4_unicode_ci');
        if (!defined('DEBUG_MODE')) define('DEBUG_MODE', true);
        
        if (!function_exists('createDatabaseConnection')) {
            function createDatabaseConnection() {
                try {
                    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                    $options = [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ];
                    return new PDO($dsn, DB_USER, DB_PASS, $options);
                } catch (PDOException $e) {
                    error_log("Error de conexión a BD: " . $e->getMessage());
                    return null;
                }
            }
        }
    }
}

class Database {
    private static $instance = null;
    private $pdo;
    private $connected = false;
    
    private function __construct() {
        try {
            // Usar la función de configuración de base de datos
            $this->pdo = createDatabaseConnection();
            if ($this->pdo) {
                $this->connected = true;
            } else {
                $this->connected = false;
            }
        } catch (PDOException $e) {
            $this->connected = false;
            if (DEBUG_MODE) {
                error_log("Error de conexión a BD: " . $e->getMessage());
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function isConnected() {
        return $this->connected;
    }
    
    // Método para ejecutar consultas directamente
    public function query($sql, $params = []) {
        if (!$this->connected) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt === false) {
                return false;
            }
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                error_log("Error en consulta: " . $e->getMessage());
                error_log("SQL: " . $sql);
                error_log("Parámetros: " . print_r($params, true));
            }
            return false;
        }
    }
    
    // Método prepare para compatibilidad
    public function prepare($sql) {
        if (!$this->connected) {
            return false;
        }
        
        try {
            return $this->pdo->prepare($sql);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                error_log("Error preparando consulta: " . $e->getMessage());
                error_log("SQL: " . $sql);
            }
            return false;
        }
    }
    
    // Método para obtener el último ID insertado
    public function lastInsertId() {
        if (!$this->connected) {
            return false;
        }
        
        try {
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                error_log("Error obteniendo último ID: " . $e->getMessage());
            }
            return false;
        }
    }
    
    // Prevenir clonación
    private function __clone() {}
    
    // Prevenir deserialización
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Crear instancia global
$db = Database::getInstance();
$pdo = $db->getConnection();

// Función para ejecutar consultas con manejo de errores
function executeQuery($sql, $params = []) {
    global $db;
    
    if (!$db->isConnected()) {
        if (DEBUG_MODE) {
            error_log("No se puede ejecutar consulta: Base de datos no conectada");
        }
        return false;
    }
    
    try {
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            error_log("Error en consulta: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Parámetros: " . print_r($params, true));
        }
        return false;
    }
}

// Función para obtener una fila
function getRow($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetch() : false;
}

// Función para obtener múltiples filas
function getRows($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

// Función para obtener un valor único
function getValue($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetchColumn() : false;
}

// Función para insertar datos
function insertData($table, $data) {
    global $db;
    
    if (!$db->isConnected()) {
        if (DEBUG_MODE) {
            error_log("No se puede insertar datos: Base de datos no conectada");
        }
        return false;
    }
    
    try {
        $pdo = $db->getConnection();
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            error_log("Error al insertar datos: " . $e->getMessage());
        }
        return false;
    }
}

// Función para actualizar datos
function updateData($table, $data, $where, $whereParams = []) {
    global $db;
    
    if (!$db->isConnected()) {
        if (DEBUG_MODE) {
            error_log("No se puede actualizar datos: Base de datos no conectada");
        }
        return false;
    }
    
    try {
        $pdo = $db->getConnection();
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "$column = :$column";
        }
        
        $sql = "UPDATE $table SET " . implode(', ', $setClause) . " WHERE $where";
        $stmt = $pdo->prepare($sql);
        
        $allParams = array_merge($data, $whereParams);
        $stmt->execute($allParams);
        
        return $stmt->rowCount();
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            error_log("Error al actualizar datos: " . $e->getMessage());
        }
        return false;
    }
}

// Función para eliminar datos
function deleteData($table, $where, $params = []) {
    global $db;
    
    if (!$db->isConnected()) {
        if (DEBUG_MODE) {
            error_log("No se puede eliminar datos: Base de datos no conectada");
        }
        return false;
    }
    
    try {
        $pdo = $db->getConnection();
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            error_log("Error al eliminar datos: " . $e->getMessage());
        }
        return false;
    }
}

// Función para verificar si una tabla existe
function tableExists($tableName) {
    global $db;
    
    if (!$db->isConnected()) {
        return false;
    }
    
    try {
        $pdo = $db->getConnection();
        $sql = "SHOW TABLES LIKE ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tableName]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// Función para obtener el número total de filas
function getRowCount($table, $where = '1', $params = []) {
    global $db;
    
    if (!$db->isConnected()) {
        return 0;
    }
    
    try {
        $pdo = $db->getConnection();
        $sql = "SELECT COUNT(*) FROM $table WHERE $where";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            error_log("Error al contar filas: " . $e->getMessage());
        }
        return 0;
    }
}

// Función para obtener datos con paginación
function getPaginatedData($sql, $page = 1, $perPage = 10, $params = []) {
    global $db;
    
    if (!$db->isConnected()) {
        return ['data' => [], 'total' => 0, 'pages' => 0, 'current_page' => $page];
    }
    
    try {
        $pdo = $db->getConnection();
        // Obtener total de registros
        $countSql = "SELECT COUNT(*) FROM ($sql) as count_table";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Calcular paginación
        $offset = ($page - 1) * $perPage;
        $totalPages = ceil($total / $perPage);
        
        // Obtener datos de la página actual
        $dataSql = $sql . " LIMIT $perPage OFFSET $offset";
        $dataStmt = $pdo->prepare($dataSql);
        $dataStmt->execute($params);
        $data = $dataStmt->fetchAll();
        
        return [
            'data' => $data,
            'total' => $total,
            'pages' => $totalPages,
            'current_page' => $page,
            'per_page' => $perPage
        ];
    } catch (PDOException $e) {
        if (DEBUG_MODE) {
            error_log("Error en paginación: " . $e->getMessage());
        }
        return ['data' => [], 'total' => 0, 'pages' => 0, 'current_page' => $page];
    }
}

// Función para ejecutar transacciones
function executeTransaction($callbacks) {
    global $db;
    
    if (!$db->isConnected()) {
        if (DEBUG_MODE) {
            error_log("No se puede ejecutar transacción: Base de datos no conectada");
        }
        return false;
    }
    
    try {
        $pdo = $db->getConnection();
        $pdo->beginTransaction();
        
        foreach ($callbacks as $callback) {
            if (!$callback()) {
                throw new Exception("Error en callback de transacción");
            }
        }
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        if (DEBUG_MODE) {
            error_log("Error en transacción: " . $e->getMessage());
        }
        return false;
    }
}

// Funciones utilitarias están definidas en includes/functions.php

// Función para formatear moneda está definida en includes/functions.php

// Función para formatear fecha está definida en includes/functions.php

// Función para calcular tiempo transcurrido está definida en includes/functions.php

// Función para verificar conexión
function testConnection() {
    try {
        global $db;
        return $db->isConnected();
    } catch (PDOException $e) {
        return false;
    }
}

// Función para hacer backup de la base de datos
function backupDatabase($filename = null) {
    if (!$filename) {
        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    }
    
    $backupPath = BACKUP_PATH . $filename;
    
    if (!is_dir(BACKUP_PATH)) {
        mkdir(BACKUP_PATH, 0755, true);
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

// Función para limpiar backups antiguos
function cleanOldBackups() {
    $files = glob(BACKUP_PATH . 'backup_*.sql');
    $cutoff = time() - (BACKUP_RETENTION_DAYS * 24 * 60 * 60);
    
    foreach ($files as $file) {
        if (filemtime($file) < $cutoff) {
            unlink($file);
        }
    }
}

// Función para obtener información de la base de datos
function getDatabaseInfo() {
    global $db;
    
    if (!$db->isConnected()) {
        return ['error' => 'Base de datos no conectada'];
    }
    
    $info = [];
    
    try {
        $pdo = $db->getConnection();
        
        // Información del servidor
        $info['server_version'] = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
        $info['client_version'] = $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION);
        $info['connection_status'] = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
        
        // Tamaño de la base de datos
        $sql = "SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb'
                FROM information_schema.tables 
                WHERE table_schema = ?";
        $info['database_size'] = getValue($sql, [DB_NAME]);
        
        // Número de tablas
        $sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = ?";
        $info['table_count'] = getValue($sql, [DB_NAME]);
        
        return $info;
    } catch (Exception $e) {
        return ['error' => 'Error al obtener información: ' . $e->getMessage()];
    }
}

// Verificar conexión al cargar el archivo
if (!testConnection()) {
    if (DEBUG_MODE) {
        error_log("Error: No se pudo conectar a la base de datos");
    }
}

// Incluir funciones auxiliares después de definir todas las constantes
require_once __DIR__ . '/functions.php';

// Registrar actividad de conexión si es posible
try {
    if (function_exists('logActivity')) {
        logActivity(0, "Conexión a base de datos establecida", "INFO");
    }
} catch (Exception $e) {
    // Ignorar errores de logging
}
?>
