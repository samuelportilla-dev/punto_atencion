<?php
/**
 * Instalador de El Punto de Atención
 * Configura la base de datos y tablas necesarias
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir configuración
require_once 'config/database.php';

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador - El Punto de Atención</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .installer-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 2rem;
        }
        .step {
            margin-bottom: 2rem;
        }
        .step-number {
            background: #7c3aed;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .step-title {
            font-size: 1.5rem;
            color: #374151;
            margin-bottom: 1rem;
        }
        .step-description {
            color: #6b7280;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-block;
            background: #7c3aed;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.3s ease;
            border: none;
            cursor: pointer;
            margin: 0.5rem;
        }
        .btn:hover {
            background: #6d28d9;
        }
        .btn-secondary {
            background: #6b7280;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .status {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .status.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .status.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .status.info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        .checklist {
            text-align: left;
            margin: 1rem 0;
        }
        .checklist-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .checklist-item:last-child {
            border-bottom: none;
        }
        .checklist-item.success {
            color: #059669;
        }
        .checklist-item.error {
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <h1>Instalador de El Punto de Atención</h1>
        
        <?php if ($error): ?>
            <div class="status error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="status success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($step == 1): ?>
            <!-- Paso 1: Verificación de requisitos -->
            <div class="step">
                <div class="step-number">1</div>
                <h2 class="step-title">Verificación de Requisitos</h2>
                <p class="step-description">Verificando que el servidor cumpla con los requisitos mínimos</p>
                
                <div class="checklist">
                    <?php
                    $requirements = [];
                    
                    // Verificar versión de PHP
                    $phpVersion = phpversion();
                    $requirements['PHP >= 7.4'] = version_compare($phpVersion, '7.4.0', '>=');
                    
                    // Verificar extensiones
                    $requirements['PDO MySQL'] = extension_loaded('pdo_mysql');
                    $requirements['PDO'] = extension_loaded('pdo');
                    $requirements['JSON'] = extension_loaded('json');
                    $requirements['MBString'] = extension_loaded('mbstring');
                    
                    // Verificar permisos de directorios
                    $requirements['Directorio logs escribible'] = is_writable('logs');
                    $requirements['Directorio uploads escribible'] = is_writable('uploads');
                    $requirements['Directorio cache escribible'] = is_writable('cache');
                    $requirements['Directorio backups escribible'] = is_writable('backups');
                    
                    $allRequirementsMet = true;
                    
                    foreach ($requirements as $requirement => $met) {
                        $class = $met ? 'success' : 'error';
                        $icon = $met ? '✅' : '❌';
                        echo "<div class='checklist-item $class'>$icon $requirement</div>";
                        
                        if (!$met) {
                            $allRequirementsMet = false;
                        }
                    }
                    ?>
                </div>
                
                <?php if ($allRequirementsMet): ?>
                    <a href="?step=2" class="btn">Continuar</a>
                <?php else: ?>
                    <div class="status error">
                        Por favor, corrija los requisitos que no se cumplen antes de continuar.
                    </div>
                    <a href="?step=1" class="btn">Verificar de nuevo</a>
                <?php endif; ?>
            </div>
            
        <?php elseif ($step == 2): ?>
            <!-- Paso 2: Configuración de base de datos -->
            <div class="step">
                <div class="step-number">2</div>
                <h2 class="step-title">Configuración de Base de Datos</h2>
                <p class="step-description">Configurando la conexión a la base de datos</p>
                
                <?php
                                 // Verificar conexión a MySQL
                 $mysqlConnected = false;
                 try {
                     // Usar configuración específica del hosting
                     $dsn = "mysql:host=localhost;charset=utf8mb4";
                     $pdo = new PDO($dsn, 'saportil_punto', '0000');
                     $mysqlConnected = true;
                 } catch (PDOException $e) {
                     $error = "Error conectando a MySQL: " . $e->getMessage();
                 }
                
                if ($mysqlConnected):
                    // Crear base de datos si no existe
                    if (!databaseExists()) {
                        if (createDatabaseIfNotExists()) {
                            $success = "Base de datos creada exitosamente";
                        } else {
                            $error = "Error creando la base de datos";
                        }
                    } else {
                        $success = "Base de datos ya existe";
                    }
                endif;
                ?>
                
                <div class="checklist">
                    <div class="checklist-item <?php echo $mysqlConnected ? 'success' : 'error'; ?>">
                        <?php echo $mysqlConnected ? '✅' : '❌'; ?> Conexión a MySQL
                    </div>
                    <div class="checklist-item <?php echo databaseExists() ? 'success' : 'error'; ?>">
                        <?php echo databaseExists() ? '✅' : '❌'; ?> Base de datos existe
                    </div>
                </div>
                
                <?php if ($mysqlConnected && databaseExists()): ?>
                    <a href="?step=3" class="btn">Continuar</a>
                <?php else: ?>
                    <a href="?step=2" class="btn">Reintentar</a>
                <?php endif; ?>
                
                <a href="?step=1" class="btn btn-secondary">Anterior</a>
            </div>
            
        <?php elseif ($step == 3): ?>
            <!-- Paso 3: Crear tablas -->
            <div class="step">
                <div class="step-number">3</div>
                <h2 class="step-title">Crear Tablas</h2>
                <p class="step-description">Creando las tablas necesarias en la base de datos</p>
                
                <?php
                if (isset($_POST['create_tables'])) {
                    try {
                        $pdo = createDatabaseConnection();
                        if ($pdo) {
                            // Leer y ejecutar el archivo SQL
                            $sqlFile = file_get_contents('database/el_punto.sql');
                            $statements = explode(';', $sqlFile);
                            
                            foreach ($statements as $statement) {
                                $statement = trim($statement);
                                if (!empty($statement)) {
                                    $pdo->exec($statement);
                                }
                            }
                            
                            $success = "Tablas creadas exitosamente";
                        } else {
                            $error = "Error conectando a la base de datos";
                        }
                    } catch (PDOException $e) {
                        $error = "Error creando tablas: " . $e->getMessage();
                    }
                }
                
                $tablesCreated = checkRequiredTables();
                ?>
                
                <div class="checklist">
                    <div class="checklist-item <?php echo $tablesCreated ? 'success' : 'error'; ?>">
                        <?php echo $tablesCreated ? '✅' : '❌'; ?> Tablas creadas
                    </div>
                </div>
                
                <?php if (!$tablesCreated): ?>
                    <form method="post">
                        <button type="submit" name="create_tables" class="btn">Crear Tablas</button>
                    </form>
                <?php else: ?>
                    <a href="?step=4" class="btn">Continuar</a>
                <?php endif; ?>
                
                <a href="?step=2" class="btn btn-secondary">Anterior</a>
            </div>
            
        <?php elseif ($step == 4): ?>
            <!-- Paso 4: Finalización -->
            <div class="step">
                <div class="step-number">4</div>
                <h2 class="step-title">Instalación Completada</h2>
                <p class="step-description">¡El Punto de Atención ha sido instalado exitosamente!</p>
                
                <div class="status success">
                    <h3>✅ Instalación Exitosa</h3>
                    <p>El sistema está listo para usar. Puedes acceder a la aplicación desde el enlace de abajo.</p>
                </div>
                
                <div class="checklist">
                    <div class="checklist-item success">✅ Base de datos configurada</div>
                    <div class="checklist-item success">✅ Tablas creadas</div>
                    <div class="checklist-item success">✅ Archivos de configuración listos</div>
                    <div class="checklist-item success">✅ Sistema operativo</div>
                </div>
                
                <a href="index.php" class="btn">Ir a la Aplicación</a>
                
                <div class="status info">
                    <strong>Importante:</strong> Por seguridad, elimina este archivo de instalación después de completar la instalación.
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
