<?php
/**
 * Script para limpiar el proyecto antes de subir a CWP
 * Elimina archivos temporales y de prueba
 */

echo "<h1>🧹 Limpiando Proyecto para CWP</h1>";

// Archivos y carpetas a eliminar
$archivos_a_eliminar = [
    'convert_svg_to_jpg.php',
    'create_images.py',
    'create_simple_jpg_images.php',
    'demo_imagenes_servicios.html',
    'execute_sql_safe.php',
    'fix_permissions.php',
    'generar_imagenes_jpg.html',
    'generate_jpg_images.html',
    'imagenes_reales_servicios.php',
    'update_servicios_paths.php',
    'test_connection.php',
    'test_dashboard_fix.php',
    'test_error.php',
    'test_historial_fix.php',
    'test_minimal.php',
    'test_servicios.php',
    'test_simple.php',
    'contactos_el_punto.txt',
    'create_servicios_table.sql',
    'postgresql_fix.md',
    'README_INSTALACION.md',
    'README.md'
];

$carpetas_a_limpiar = [
    'backups',
    'cache',
    'logs'
];

echo "<h2>📁 Eliminando archivos temporales...</h2>";

foreach ($archivos_a_eliminar as $archivo) {
    if (file_exists($archivo)) {
        if (unlink($archivo)) {
            echo "✅ Eliminado: $archivo<br>";
        } else {
            echo "❌ Error eliminando: $archivo<br>";
        }
    } else {
        echo "⚠️ No existe: $archivo<br>";
    }
}

echo "<h2>📂 Limpiando carpetas temporales...</h2>";

foreach ($carpetas_a_limpiar as $carpeta) {
    if (is_dir($carpeta)) {
        // Eliminar contenido de la carpeta
        $archivos = glob($carpeta . '/*');
        foreach ($archivos as $archivo) {
            if (is_file($archivo)) {
                unlink($archivo);
            }
        }
        echo "✅ Limpiada: $carpeta<br>";
    }
}

echo "<h2>🔧 Creando archivo .htaccess optimizado para CWP...</h2>";

$htaccess_content = '# El Punto - Sistema de Gestión
# Optimizado para CWP

# Habilitar mod_rewrite
RewriteEngine On

# Redirigir a HTTPS (opcional)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Página de inicio
DirectoryIndex index.php index.html

# Proteger archivos sensibles
<Files "config.php">
    Order Allow,Deny
    Deny from all
</Files>

<Files "*.sql">
    Order Allow,Deny
    Deny from all
</Files>

# Proteger directorios
<Directory "config">
    Order Allow,Deny
    Deny from all
</Directory>

<Directory "database">
    Order Allow,Deny
    Deny from all
</Directory>

<Directory "includes">
    Order Allow,Deny
    Deny from all
</Directory>

# Configuración de PHP para CWP
<IfModule mod_php7.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value memory_limit 128M
    php_value max_execution_time 30
    php_value max_input_vars 3000
</IfModule>

# Compresión GZIP
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Cache de archivos estáticos
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
</IfModule>

# Páginas de error personalizadas
ErrorDocument 404 /404.html
ErrorDocument 403 /403.html
ErrorDocument 500 /500.html

# Seguridad adicional
ServerSignature Off
Options -Indexes

# Headers de seguridad
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>';

if (file_put_contents('.htaccess', $htaccess_content)) {
    echo "✅ Archivo .htaccess creado correctamente<br>";
} else {
    echo "❌ Error creando .htaccess<br>";
}

echo "<h2>📋 Creando archivo de instrucciones para CWP...</h2>";

$instrucciones = '# INSTRUCCIONES PARA CWP (Control Web Panel)

## 📁 Estructura del Proyecto
```
punto_atencion/
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
├── config/
├── includes/
├── pages/
├── uploads/
├── index.php
└── .htaccess
```

## 🗄️ Configuración de Base de Datos

1. **Crear base de datos en CWP:**
   - Nombre: `saportil_punto`
   - Usuario: `tu_usuario`
   - Contraseña: `tu_contraseña`

2. **Importar estructura:**
   - Usar el archivo: `database/el_punto.sql`
   - Importar desde phpMyAdmin en CWP

3. **Configurar conexión:**
   - Editar: `config/database.php`
   - Actualizar credenciales de la base de datos

## ⚙️ Configuración de PHP en CWP

1. **Versión de PHP:** 7.4 o superior
2. **Extensiones requeridas:**
   - PDO
   - PDO_MySQL
   - GD (para imágenes)
   - JSON
   - Session

3. **Límites recomendados:**
   - upload_max_filesize: 10M
   - post_max_size: 10M
   - memory_limit: 128M
   - max_execution_time: 30

## 🔐 Permisos de Archivos

1. **Carpetas (755):**
   - assets/
   - uploads/
   - cache/
   - logs/

2. **Archivos PHP (644):**
   - Todos los archivos .php

3. **Archivos de configuración (600):**
   - config/database.php

## 🚀 Pasos de Instalación

1. **Subir archivos:**
   - Comprimir solo la carpeta `punto_atencion`
   - Subir ZIP a CWP
   - Extraer en el directorio público

2. **Configurar base de datos:**
   - Crear base de datos
   - Importar estructura
   - Actualizar credenciales

3. **Verificar permisos:**
   - Ejecutar: `chmod -R 755 punto_atencion/`
   - Ejecutar: `chmod 600 config/database.php`

4. **Probar instalación:**
   - Visitar: `https://tudominio.com/punto_atencion/`
   - Verificar que no hay errores

## 🐛 Solución de Problemas Comunes

### Error de conexión a base de datos:
- Verificar credenciales en `config/database.php`
- Verificar que la base de datos existe
- Verificar permisos del usuario de BD

### Error 500:
- Verificar permisos de archivos
- Verificar logs de error en CWP
- Verificar versión de PHP

### Archivos no se actualizan:
- Limpiar cache del navegador
- Verificar permisos de escritura
- Verificar que el ZIP se extrajo correctamente

## 📞 Soporte
Si tienes problemas, verifica:
1. Logs de error de CWP
2. Logs de PHP
3. Permisos de archivos
4. Configuración de base de datos
';

if (file_put_contents('INSTRUCCIONES_CWP.md', $instrucciones)) {
    echo "✅ Archivo de instrucciones creado<br>";
} else {
    echo "❌ Error creando instrucciones<br>";
}

echo "<h2>✅ Limpieza completada</h2>";
echo "<p>El proyecto está listo para subir a CWP. Sigue las instrucciones en INSTRUCCIONES_CWP.md</p>";
?>
