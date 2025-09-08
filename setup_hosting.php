<?php
/**
 * Script de Configuración para Hosting Compartido
 * saportil.corsajetec.com
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Configuración para Hosting Compartido</h1>";
echo "<h2>saportil.corsajetec.com</h2>";

// Configuración específica del hosting
$config = [
    'host' => 'localhost',
    'database' => 'saportil_punto',
    'user' => 'saportil_punto',
    'password' => '0000'
];

echo "<h3>Configuración Aplicada:</h3>";
echo "<ul>";
echo "<li><strong>Host:</strong> " . $config['host'] . "</li>";
echo "<li><strong>Base de datos:</strong> " . $config['database'] . "</li>";
echo "<li><strong>Usuario:</strong> " . $config['user'] . "</li>";
echo "<li><strong>Contraseña:</strong> Configurada</li>";
echo "</ul>";

// Probar conexión
echo "<h3>Probando Conexión:</h3>";
$connectionResult = testConnection($config);

if ($connectionResult['success']) {
    echo "<p style='color: green;'>✅ Conexión exitosa a MySQL</p>";
    
    // Verificar si la base de datos existe
    echo "<h3>Verificando Base de Datos:</h3>";
    $dbExists = checkDatabaseExists($config);
    
    if ($dbExists) {
        echo "<p style='color: green;'>✅ Base de datos existe</p>";
        
        // Verificar tablas
        echo "<h3>Verificando Tablas:</h3>";
        $tablesResult = checkTables($config);
        
        if ($tablesResult['success']) {
            echo "<p style='color: green;'>✅ Todas las tablas existen</p>";
            echo "<h3>🎉 ¡Sistema Listo!</h3>";
            echo "<p>Tu aplicación está configurada correctamente.</p>";
            echo "<p><a href='index.php'>Ir a la aplicación</a></p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Algunas tablas faltan</p>";
            echo "<p>Creando tablas...</p>";
            
            $createResult = createTables($config);
            if ($createResult['success']) {
                echo "<p style='color: green;'>✅ Tablas creadas exitosamente</p>";
                echo "<p><a href='index.php'>Ir a la aplicación</a></p>";
            } else {
                echo "<p style='color: red;'>❌ Error creando tablas: " . $createResult['error'] . "</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ Base de datos no existe</p>";
        echo "<p>En hosting compartido, la base de datos debe ser creada desde el panel de control.</p>";
        echo "<p>Por favor, crea la base de datos 'saportil_punto' desde tu panel de hosting.</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Error de conexión: " . $connectionResult['error'] . "</p>";
    echo "<h3>Posibles Soluciones:</h3>";
    echo "<ul>";
    echo "<li>Verifica que la base de datos 'saportil_punto' exista</li>";
    echo "<li>Verifica que el usuario 'saportil_punto' tenga permisos</li>";
    echo "<li>Verifica que la contraseña sea correcta</li>";
    echo "<li>Contacta al soporte de tu hosting si el problema persiste</li>";
    echo "</ul>";
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

// Función para verificar si la base de datos existe
function checkDatabaseExists($config) {
    try {
        $dsn = "mysql:host=" . $config['host'] . ";charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $config['database'] . "'");
        return $stmt->rowCount() > 0;
        
    } catch (PDOException $e) {
        return false;
    }
}

// Función para verificar tablas
function checkTables($config) {
    try {
        $dsn = "mysql:host=" . $config['host'] . ";dbname=" . $config['database'] . ";charset=utf8mb4";
        $pdo = new PDO($dsn, $config['user'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
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
                return [
                    'success' => false,
                    'error' => "Tabla '$table' no encontrada"
                ];
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
            if (!empty($statement) && !str_starts_with($statement, '--') && !str_starts_with($statement, 'CREATE DATABASE') && !str_starts_with($statement, 'USE')) {
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
$configPath = 'config/database_hosting.php';

if (file_put_contents($configPath, $configContent)) {
    echo "<p style='color: green;'>✅ Archivo de configuración generado: $configPath</p>";
} else {
    echo "<p style='color: red;'>❌ Error generando archivo de configuración</p>";
}

// Función para generar archivo de configuración
function generateConfigFile($config) {
    return '<?php
/**
 * Configuración de Base de Datos - Hosting Compartido
 * saportil.corsajetec.com
 */

// Configuración específica para el hosting
define(\'DB_HOST\', \'' . $config['host'] . '\');
define(\'DB_NAME\', \'' . $config['database'] . '\');
define(\'DB_USER\', \'' . $config['user'] . '\');
define(\'DB_PASS\', \'' . $config['password'] . '\');

// Configuración de conexión
define(\'DB_CHARSET\', \'utf8mb4\');
define(\'DB_COLLATE\', \'utf8mb4_unicode_ci\');

// Configuración de timeouts para hosting compartido
define(\'DB_CONNECT_TIMEOUT\', 30);
define(\'DB_READ_TIMEOUT\', 60);

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

// Verificar conexión al cargar el archivo
if (!function_exists(\'DEBUG_MODE\')) {
    define(\'DEBUG_MODE\', true);
}
?>';
}
?>
