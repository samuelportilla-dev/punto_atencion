# 🚀 GUÍA COMPLETA PARA SUBIR A CWP

## ❌ **PROBLEMAS COMUNES CON CWP**

### **1. Archivos No Se Actualizan**
- **Causa**: CWP tiene cache agresivo
- **Solución**: 
  - Limpiar cache del navegador (Ctrl+F5)
  - Esperar 5-10 minutos
  - Verificar que el ZIP se extrajo correctamente

### **2. Error 500**
- **Causa**: Permisos incorrectos o PHP mal configurado
- **Solución**:
  - Verificar permisos: `chmod -R 755 punto_atencion/`
  - Verificar versión de PHP (7.4+)
  - Revisar logs de error en CWP

### **3. Base de Datos No Conecta**
- **Causa**: Credenciales incorrectas
- **Solución**:
  - Verificar usuario y contraseña en `config/database.php`
  - Verificar que la base de datos existe
  - Verificar permisos del usuario de BD

## 🧹 **PASOS PARA LIMPIAR EL PROYECTO**

### **Archivos a Eliminar ANTES de subir:**
```
❌ convert_svg_to_jpg.php
❌ create_images.py
❌ create_simple_jpg_images.php
❌ demo_imagenes_servicios.html
❌ execute_sql_safe.php
❌ fix_permissions.php
❌ generar_imagenes_jpg.html
❌ generate_jpg_images.html
❌ imagenes_reales_servicios.php
❌ update_servicios_paths.php
❌ test_connection.php
❌ test_dashboard_fix.php
❌ test_error.php
❌ test_historial_fix.php
❌ test_minimal.php
❌ test_servicios.php
❌ test_simple.php
❌ contactos_el_punto.txt
❌ create_servicios_table.sql
❌ postgresql_fix.md
❌ README_INSTALACION.md
❌ README.md
```

### **Estructura FINAL para CWP:**
```
✅ punto_atencion/
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
├── config/
│   ├── config.php
│   ├── database.php
│   ├── database_hosting.php
│   └── database_local.php
├── database/
│   └── el_punto.sql
├── includes/
│   ├── db.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   └── logout.php
├── pages/
│   ├── contacto.php
│   ├── galeria.php
│   ├── historial.php
│   ├── inicio.php
│   ├── login.php
│   ├── mesas.php
│   ├── nosotros.php
│   ├── pedidos.php
│   ├── register.php
│   ├── reportes.php
│   ├── servicios.php
│   └── usuarios.php
├── uploads/
├── index.php
└── .htaccess
```

## 📦 **PROCESO DE SUBIDA A CWP**

### **Paso 1: Preparar el ZIP**
1. **Eliminar archivos temporales** (usar la lista de arriba)
2. **Crear ZIP** solo con la carpeta `punto_atencion`
3. **Verificar** que el ZIP no tenga archivos ocultos

### **Paso 2: Subir a CWP**
1. **Acceder a CWP** → File Manager
2. **Navegar** al directorio público (public_html)
3. **Subir ZIP** y extraer
4. **Verificar** que se extrajo correctamente

### **Paso 3: Configurar Base de Datos**
1. **Crear BD** en CWP:
   - Nombre: `saportil_punto`
   - Usuario: `tu_usuario`
   - Contraseña: `tu_contraseña`

2. **Importar estructura**:
   - Usar `database/el_punto.sql`
   - Importar desde phpMyAdmin

3. **Configurar conexión**:
   - Editar `config/database.php`
   - Actualizar credenciales

### **Paso 4: Configurar Permisos**
```bash
# En CWP Terminal o SSH:
chmod -R 755 punto_atencion/
chmod 600 punto_atencion/config/database.php
chmod 755 punto_atencion/uploads/
```

### **Paso 5: Configurar PHP**
En CWP → PHP Settings:
- **Versión**: 7.4 o superior
- **upload_max_filesize**: 10M
- **post_max_size**: 10M
- **memory_limit**: 128M
- **max_execution_time**: 30

## 🔧 **CONFIGURACIÓN DE .htaccess**

Crear archivo `.htaccess` en la raíz:
```apache
# El Punto - Sistema de Gestión
RewriteEngine On

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

# Configuración de PHP
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
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/javascript
</IfModule>

# Cache de archivos estáticos
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
</IfModule>

# Páginas de error
ErrorDocument 404 /404.html
ErrorDocument 403 /403.html
ErrorDocument 500 /500.html

# Seguridad
ServerSignature Off
Options -Indexes
```

## 🐛 **SOLUCIÓN DE PROBLEMAS**

### **Error: "No se puede conectar a la base de datos"**
1. Verificar credenciales en `config/database.php`
2. Verificar que la BD existe en CWP
3. Verificar permisos del usuario de BD
4. Verificar que MySQL está funcionando

### **Error 500: "Internal Server Error"**
1. Verificar logs de error en CWP
2. Verificar permisos de archivos
3. Verificar versión de PHP
4. Verificar sintaxis de .htaccess

### **Archivos no se actualizan**
1. Limpiar cache del navegador (Ctrl+F5)
2. Esperar 5-10 minutos
3. Verificar que el ZIP se extrajo correctamente
4. Verificar permisos de escritura

### **Página en blanco**
1. Verificar logs de PHP en CWP
2. Verificar que `index.php` existe
3. Verificar permisos de archivos
4. Verificar configuración de PHP

## ✅ **VERIFICACIÓN FINAL**

1. **Visitar**: `https://tudominio.com/punto_atencion/`
2. **Verificar**: No hay errores
3. **Probar**: Login y funciones básicas
4. **Verificar**: Base de datos conecta
5. **Probar**: Crear pedido y ver en historial

## 📞 **SOPORTE ADICIONAL**

Si sigues teniendo problemas:
1. **Revisar logs** de error en CWP
2. **Verificar** configuración de PHP
3. **Verificar** permisos de archivos
4. **Verificar** configuración de base de datos
5. **Contactar** soporte de CWP si es necesario

---

**¡Con esta guía deberías poder subir el proyecto a CWP sin problemas!** 🚀
