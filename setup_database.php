<?php
/**
 * Script de Configuración Automática de Base de Datos
 * Detecta automáticamente tu entorno y configura la base de datos
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Configuración Automática de Base de Datos</h1>";

// Detectar el entorno
$environment = detectEnvironment();
echo "<h2>Entorno Detectado: " . $environment['name'] . "</h2>";

// Configurar base de datos según el entorno
$config = getDatabaseConfig($environment);
echo "<h3>Configuración Aplicada:</h3>";
echo "<ul>";
echo "<li><strong>Host:</strong> " . $config['host'] . "</li>";
echo "<li><strong>Base de datos:</strong> " . $config['database'] . "</li>";
echo "<li><strong>Usuario:</strong> " . $config['user'] . "</li>";
echo "<li><strong>Contraseña:</strong> " . ($config['password'] ? 'Configurada' : 'Sin contraseña') . "</li>";
echo "</ul>";

// Probar conexión
echo "<h3>Probando Conexión:</h3>";
$connectionResult = testConnection($config);

if ($connectionResult['success']) {
    echo "<p style='color: green;'>✅ Conexión exitosa a MySQL</p>";
    
    // Crear base de datos si no existe
    echo "<h3>Creando Base de Datos:</h3>";
    $dbResult = createDatabase($config);
    
    if ($dbResult['success']) {
        echo "<p style='color: green;'>✅ Base de datos creada/verificada</p>";
        
        // Crear tablas
        echo "<h3>Creando Tablas:</h3>";
        $tablesResult = createTables($config);
        
        if ($tablesResult['success']) {
            echo "<p style='color: green;'>✅ Tablas creadas exitosamente</p>";
            echo "<h3>🎉 ¡Configuración Completada!</h3>";
            echo "<p>Tu base de datos está lista para usar.</p>";
            echo "<p><a href='index.php'>Ir a la aplicación</a></p>";
        } else {
            echo "<p style='color: red;'>❌ Error creando tablas: " . $tablesResult['error'] . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Error creando base de datos: " . $dbResult['error'] . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Error de conexión: " . $connectionResult['error'] . "</p>";
    echo "<h3>Solución:</h3>";
    echo "<ul>";
    echo "<li>Verifica que MySQL esté ejecutándose</li>";
    echo "<li>Verifica las credenciales de MySQL</li>";
    echo "<li>Si usas XAMPP/WAMP, asegúrate de que MySQL esté iniciado</li>";
    echo "</ul>";
}

// Función para detectar el entorno
function detectEnvironment() {
    $environment = [
        'name' => 'Desconocido',
        'type' => 'unknown'
    ];
    
    // Detectar XAMPP
    if (file_exists('C:/xampp/mysql/bin/mysql.exe') || 
        file_exists('C:/xampp/mysql/bin/mysqld.exe')) {
        $environment = [
            'name' => 'XAMPP (Windows)',
            'type' => 'xampp'
        ];
    }
    // Detectar WAMP
    elseif (file_exists('C:/wamp64/bin/mysql/') || 
            file_exists('C:/wamp/bin/mysql/')) {
        $environment = [
            'name' => 'WAMP (Windows)',
            'type' => 'wamp'
        ];
    }
    // Detectar MAMP
    elseif (file_exists('/Applications/MAMP/Library/bin/mysql') || 
            file_exists('C:/MAMP/bin/mysql/')) {
        $environment = [
            'name' => 'MAMP (Mac/Windows)',
            'type' => 'mamp'
        ];
    }
    // Detectar Linux
    elseif (PHP_OS === 'Linux') {
        $environment = [
            'name' => 'Linux Server',
            'type' => 'linux'
        ];
    }
    
    return $environment;
}

// Función para obtener configuración según el entorno
function getDatabaseConfig($environment) {
    // Configuración específica para hosting compartido
    $config = [
        'host' => 'localhost',
        'database' => 'saportil_punto',
        'user' => 'saportil_punto',
        'password' => '0000'
    ];
    
    // Detectar si es hosting compartido
    if (strpos($_SERVER['HTTP_HOST'] ?? '', 'corsajetec.com') !== false) {
        // Configuración para hosting compartido
        $config = [
            'host' => 'localhost',
            'database' => 'saportil_punto',
            'user' => 'saportil_punto',
            'password' => '0000'
        ];
    } else {
        // Configuración local para desarrollo
        switch ($environment['type']) {
            case 'xampp':
            case 'wamp':
                $config['password'] = ''; // Sin contraseña por defecto
                break;
                
            case 'mamp':
                $config['password'] = 'root'; // MAMP usa 'root' como contraseña
                break;
                
            case 'linux':
                $config['password'] = ''; // Cambiar según tu configuración
                break;
                
            default:
                // Configuración genérica
                break;
        }
    }
    
    return $config;
}

// Función para probar conexión
function testConnection($config) {
    try {
        $dsn = "mysql:host=" . $config['host'] . ";charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        return ['success' => true];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Función para crear base de datos
function createDatabase($config) {
    try {
        $dsn = "mysql:host=" . $config['host'] . ";charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $sql = "CREATE DATABASE IF NOT EXISTS " . $config['database'] . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $pdo->exec($sql);
        
        return ['success' => true];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Función para crear tablas
function createTables($config) {
    try {
        $dsn = "mysql:host=" . $config['host'] . ";dbname=" . $config['database'] . ";charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Leer y ejecutar el archivo SQL
        $sqlFile = file_get_contents('database/el_punto.sql');
        $statements = explode(';', $sqlFile);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !str_starts_with($statement, '--')) {
                $pdo->exec($statement);
            }
        }
        
        return ['success' => true];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Generar archivo de configuración
echo "<h3>Generando Archivo de Configuración:</h3>";
$configContent = generateConfigFile($config);
$configPath = 'config/database.php';

if (file_put_contents($configPath, $configContent)) {
    echo "<p style='color: green;'>✅ Archivo de configuración generado: $configPath</p>";
} else {
    echo "<p style='color: red;'>❌ Error generando archivo de configuración</p>";
}

// Función para generar archivo de configuración
function generateConfigFile($config) {
    return '<?php
/**
 * Configuración de Base de Datos - El Punto de Atención
 * Generado automáticamente por setup_database.php
 */

// Configuración de la base de datos
define(\'DB_HOST\', \'' . $config['host'] . '\');
define(\'DB_NAME\', \'' . $config['database'] . '\');
define(\'DB_USER\', \'' . $config['user'] . '\');
define(\'DB_PASS\', \'' . $config['password'] . '\');

// Configuración de conexión
define(\'DB_CHARSET\', \'utf8mb4\');
define(\'DB_COLLATE\', \'utf8mb4_unicode_ci\');

// Configuración de timeouts
define(\'DB_CONNECT_TIMEOUT\', 10);
define(\'DB_READ_TIMEOUT\', 30);

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
        
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = \'" . DB_NAME . "\'");
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
            \'usuarios\',
            \'servicios\', 
            \'mesas\',
            \'pedidos\',
            \'detalle_pedidos\',
            \'contactos\',
            \'configuracion\'
        ];
        
        foreach ($requiredTables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE \'$table\'");
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
if (!function_exists(\'DEBUG_MODE\')) {
    define(\'DEBUG_MODE\', true);
}

// Intentar crear la base de datos si no existe
if (!databaseExists()) {
    if (DEBUG_MODE) {
        error_log("Base de datos no existe, intentando crearla...");
    }
    createDatabaseIfNotExists();
}
?>';
}
?>
