<?php
/**
 * Header común para todas las páginas
 * El Punto de Atención - Plataforma Web Integral
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

// Obtener configuración del sitio
$siteName = 'El Punto de Atención';
$siteDescription = 'Plataforma Web Integral para la Transformación Digital Empresarial';

// Intentar obtener configuración de la base de datos si está disponible
try {
    $dbConfig = getConfig('nombre_empresa');
    if ($dbConfig) {
        $siteName = $dbConfig;
    }
    
    $dbDesc = getConfig('descripcion');
    if ($dbDesc) {
        $siteDescription = $dbDesc;
    }
} catch (Exception $e) {
    // Usar valores por defecto si hay error
}

// Verificar si el usuario está logueado
$isLoggedIn = isset($_SESSION['user_id']);
$currentUser = $isLoggedIn ? [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'],
    'role' => $_SESSION['user_role']
] : null;

// Obtener mensajes de sesión
$message = getMessage();

// Determinar la ruta base para los enlaces
$isIndexPage = basename($_SERVER['PHP_SELF']) === 'index.php';
$basePath = $isIndexPage ? '' : '../';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $siteName; ?> - <?php echo $siteDescription; ?></title>
    <meta name="description" content="<?php echo $siteDescription; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo $basePath; ?>assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Meta tags adicionales -->
    <meta name="author" content="El Punto de Atención">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="<?php echo $siteName; ?>">
    <meta property="og:description" content="<?php echo $siteDescription; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo SITE_URL; ?>">
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    
    <!-- Configuración de notificaciones -->
    <script>
        const NOTIFICATION_SOUND = <?php echo NOTIFICATION_SOUND ? 'true' : 'false'; ?>;
        const AUTO_REFRESH_INTERVAL = <?php echo AUTO_REFRESH_INTERVAL; ?>;
        const SITE_URL = '<?php echo SITE_URL; ?>';
    </script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="header-left">
                <div class="logo">
                    <a href="<?php echo $basePath; ?>index.php">
                        <div class="logo-icon" style="width: 32px; height: 32px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #7c3aed; font-weight: bold; font-size: 1.2rem; margin-right: 0.5rem;">PA</div>
                        <span><?php echo $siteName; ?></span>
                        <small style="font-size: 0.8rem; opacity: 0.8; margin-left: 0.5rem;">"Conectando tu éxito digital"</small>
                    </a>
                </div>
            </div>
            
            <div class="header-center">
                <nav class="main-nav">
                    <ul class="nav-list">
                        <li><a href="<?php echo $basePath; ?>index.php" class="nav-link"><i class="fas fa-home"></i> Inicio</a></li>
                        <li><a href="<?php echo $basePath; ?>pages/nosotros.php" class="nav-link"><i class="fas fa-info-circle"></i> Nosotros</a></li>
                        <li><a href="<?php echo $basePath; ?>pages/servicios.php" class="nav-link"><i class="fas fa-cogs"></i> Servicios Digitales</a></li>
                        <li><a href="<?php echo $basePath; ?>pages/pedidos.php" class="nav-link"><i class="fas fa-tasks"></i> Proyectos</a></li>
                        <li><a href="<?php echo $basePath; ?>pages/historial.php" class="nav-link"><i class="fas fa-trophy"></i> Casos de Éxito</a></li>
                        <li><a href="<?php echo $basePath; ?>pages/galeria.php" class="nav-link"><i class="fas fa-briefcase"></i> Portafolio</a></li>
                        <li><a href="<?php echo $basePath; ?>pages/mesas.php" class="nav-link"><i class="fas fa-puzzle-piece"></i> Soluciones</a></li>
                        <li><a href="<?php echo $basePath; ?>pages/contacto.php" class="nav-link"><i class="fas fa-envelope"></i> Contacto</a></li>
                    </ul>
                </nav>
            </div>
            
            <div class="header-right">
                <?php if ($isLoggedIn): ?>
                    <div class="user-menu">
                        <div class="user-info">
                            <span class="user-name"><?php echo $currentUser['name']; ?></span>
                            <span class="user-role"><?php echo $currentUser['role']; ?></span>
                        </div>
                        <div class="user-actions">
                            <a href="<?php echo $basePath; ?>pages/inicio.php" class="btn btn-secondary">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                            <a href="<?php echo $basePath; ?>includes/logout.php" class="btn btn-danger">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="<?php echo $basePath; ?>pages/login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </a>
                        <a href="<?php echo $basePath; ?>pages/register.php" class="btn btn-outline">
                            <i class="fas fa-user-plus"></i> Registrarse
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Mensajes de sesión -->
    <?php if ($message): ?>
        <div class="message-container">
            <div class="message message-<?php echo $message['type']; ?>">
                <span class="message-text"><?php echo $message['text']; ?></span>
                <button class="message-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Contenido principal -->
    <main class="main-content">
        <!-- Breadcrumb -->
        <?php if (!$isIndexPage): ?>
            <nav class="breadcrumb">
                <div class="breadcrumb-container">
                    <a href="<?php echo $basePath; ?>index.php" class="breadcrumb-item">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                    <span class="breadcrumb-separator">/</span>
                    <span class="breadcrumb-current">
                        <?php
                        $currentPage = basename($_SERVER['PHP_SELF'], '.php');
                        $pageTitles = [
                            'nosotros' => 'Nosotros',
                            'servicios' => 'Servicios Digitales',
                            'pedidos' => 'Proyectos',
                            'historial' => 'Casos de Éxito',
                            'galeria' => 'Portafolio',
                            'mesas' => 'Soluciones',
                            'contacto' => 'Contacto',
                            'login' => 'Iniciar Sesión',
                            'register' => 'Registrarse',
                            'inicio' => 'Dashboard'
                        ];
                        echo $pageTitles[$currentPage] ?? ucfirst($currentPage);
                        ?>
                    </span>
                </div>
            </nav>
        <?php endif; ?>
