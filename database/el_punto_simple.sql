-- Base de datos para El Punto - Sistema de Intercomunicación Cocina & Meseros
-- Creado: 2024
-- Versión simplificada sin procedimientos almacenados ni triggers

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS saportil_punto;
USE saportil_punto;

-- Tabla de servicios/platos
CREATE TABLE IF NOT EXISTS servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    categoria ENUM('Entrada', 'Plato Principal', 'Postre', 'Bebida', 'Sistema') NOT NULL,
    disponible BOOLEAN DEFAULT TRUE,
    imagen VARCHAR(255),
    tiempo_preparacion INT DEFAULT 15, -- en minutos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de mesas
CREATE TABLE IF NOT EXISTS mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero INT NOT NULL UNIQUE,
    capacidad INT DEFAULT 4,
    estado ENUM('Disponible', 'Ocupada', 'Reservada', 'Mantenimiento') DEFAULT 'Disponible',
    ubicacion VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de usuarios (meseros, cocineros, administradores)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('Mesero', 'Cocinero', 'Administrador') NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(20) UNIQUE NOT NULL,
    mesa_id INT,
    mesero_id INT,
    estado ENUM('Pendiente', 'En Cocina', 'Listo', 'Entregado', 'Cancelado') DEFAULT 'Pendiente',
    total DECIMAL(10,2) DEFAULT 0,
    notas TEXT,
    hora_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hora_entrega TIMESTAMP NULL,
    FOREIGN KEY (mesa_id) REFERENCES mesas(id),
    FOREIGN KEY (mesero_id) REFERENCES usuarios(id)
);

-- Tabla de detalles de pedido
CREATE TABLE IF NOT EXISTS detalle_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT,
    servicio_id INT,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    notas TEXT,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios(id)
);

-- Tabla de contactos
CREATE TABLE IF NOT EXISTS contactos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    mensaje TEXT NOT NULL,
    estado ENUM('Nuevo', 'Leído', 'Respondido') DEFAULT 'Nuevo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de configuración del sistema
CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(50) UNIQUE NOT NULL,
    valor TEXT,
    descripcion TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de log de actividad
CREATE TABLE IF NOT EXISTS log_actividad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(100) NOT NULL,
    detalles TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario_fecha (usuario_id, fecha)
);

-- Insertar datos de ejemplo

-- Servicios/Platos
INSERT IGNORE INTO servicios (nombre, descripcion, precio, categoria, imagen) VALUES
-- Entradas
('Ensalada César', 'Lechuga romana, crutones, parmesano y aderezo César', 8.50, 'Entrada', 'ensalada-cesar.jpg'),
('Sopa del Día', 'Sopa casera preparada diariamente', 6.00, 'Entrada', 'sopa-dia.jpg'),
('Bruschetta', 'Pan tostado con tomate, albahaca y mozzarella', 7.00, 'Entrada', 'bruschetta.jpg'),

-- Platos Principales
('Pasta Carbonara', 'Pasta fresca con salsa cremosa, panceta y parmesano', 15.50, 'Plato Principal', 'pasta-carbonara.jpg'),
('Filete de Res', 'Filete de res a la parrilla con guarnición', 22.00, 'Plato Principal', 'filete-res.jpg'),
('Paella Valenciana', 'Arroz con mariscos y pollo', 18.50, 'Plato Principal', 'paella.jpg'),
('Pollo a la Plancha', 'Pechuga de pollo con hierbas y limón', 16.00, 'Plato Principal', 'pollo-plancha.jpg'),
('Salmón al Horno', 'Salmón fresco con vegetales', 20.00, 'Plato Principal', 'salmon-horno.jpg'),

-- Postres
('Tiramisú', 'Postre italiano clásico', 8.00, 'Postre', 'tiramisu.jpg'),
('Flan Casero', 'Flan de vainilla con caramelo', 6.50, 'Postre', 'flan.jpg'),
('Cheesecake', 'Tarta de queso con frutos rojos', 9.00, 'Postre', 'cheesecake.jpg'),

-- Bebidas
('Agua Mineral', 'Agua mineral 500ml', 2.50, 'Bebida', 'agua.jpg'),
('Refresco', 'Coca-Cola, Sprite o Fanta', 3.00, 'Bebida', 'refresco.jpg'),
('Cerveza', 'Cerveza nacional o importada', 4.50, 'Bebida', 'cerveza.jpg'),
('Vino Tinto', 'Copa de vino tinto de la casa', 6.00, 'Bebida', 'vino-tinto.jpg'),
('Café', 'Café americano o espresso', 3.50, 'Bebida', 'cafe.jpg'),

-- Servicios del Sistema
('Instalación Básica', 'Instalación del sistema El Punto en un restaurante', 500.00, 'Sistema', 'instalacion.jpg'),
('Capacitación Staff', 'Capacitación completa para el personal', 300.00, 'Sistema', 'capacitacion.jpg'),
('Mantenimiento Mensual', 'Mantenimiento y soporte técnico mensual', 150.00, 'Sistema', 'mantenimiento.jpg'),
('Actualización Premium', 'Actualización a versión premium con funciones avanzadas', 800.00, 'Sistema', 'premium.jpg');

-- Mesas
INSERT IGNORE INTO mesas (numero, capacidad, estado, ubicacion) VALUES
(1, 4, 'Disponible', 'Terraza'),
(2, 6, 'Disponible', 'Interior'),
(3, 4, 'Disponible', 'Interior'),
(4, 8, 'Disponible', 'Sala Privada'),
(5, 4, 'Disponible', 'Terraza'),
(6, 6, 'Disponible', 'Interior'),
(7, 4, 'Disponible', 'Interior'),
(8, 4, 'Disponible', 'Terraza'),
(9, 6, 'Disponible', 'Interior'),
(10, 4, 'Disponible', 'Interior'),
(11, 4, 'Disponible', 'Terraza'),
(12, 6, 'Disponible', 'Interior'),
(13, 4, 'Disponible', 'Interior'),
(14, 4, 'Disponible', 'Terraza'),
(15, 6, 'Disponible', 'Interior');

-- Usuarios de ejemplo
INSERT IGNORE INTO usuarios (nombre, email, password, rol) VALUES
('María González', 'maria@elpunto.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mesero'),
('Carlos Ruiz', 'carlos@elpunto.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Cocinero'),
('Ana Martínez', 'ana@elpunto.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mesero'),
('Luis Pérez', 'luis@elpunto.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Cocinero'),
('Admin El Punto', 'admin@elpunto.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador');

-- Pedidos de ejemplo
INSERT IGNORE INTO pedidos (numero_pedido, mesa_id, mesero_id, estado, total, notas) VALUES
('PED-001', 5, 1, 'Entregado', 28.50, 'Sin cebolla en la pasta'),
('PED-002', 3, 3, 'En Cocina', 32.00, 'Filete término medio'),
('PED-003', 8, 1, 'Listo', 45.50, 'Todo bien cocido'),
('PED-004', 2, 3, 'Pendiente', 18.00, 'Sin hielo en las bebidas');

-- Detalles de pedidos
INSERT IGNORE INTO detalle_pedidos (pedido_id, servicio_id, cantidad, precio_unitario, subtotal) VALUES
(1, 4, 1, 15.50, 15.50), -- Pasta Carbonara
(1, 12, 1, 3.00, 3.00),  -- Refresco
(1, 10, 1, 2.50, 2.50),  -- Agua
(1, 9, 1, 8.00, 8.00),   -- Tiramisú

(2, 5, 1, 22.00, 22.00), -- Filete de Res
(2, 11, 1, 4.50, 4.50),  -- Cerveza
(2, 10, 1, 2.50, 2.50),  -- Agua
(2, 8, 1, 6.50, 6.50),   -- Flan

(3, 6, 1, 18.50, 18.50), -- Paella
(3, 13, 1, 6.00, 6.00),  -- Vino Tinto
(3, 1, 1, 8.50, 8.50),   -- Ensalada César
(3, 9, 1, 8.00, 8.00),   -- Tiramisú
(3, 10, 1, 2.50, 2.50),  -- Agua

(4, 7, 1, 16.00, 16.00), -- Pollo a la Plancha
(4, 10, 1, 2.50, 2.50);  -- Agua

-- Configuración del sistema
INSERT IGNORE INTO configuracion (clave, valor, descripcion) VALUES
('nombre_restaurante', 'El Punto', 'Nombre del restaurante'),
('direccion', 'Calle Principal 123, Ciudad', 'Dirección del restaurante'),
('telefono', '+1 234 567 8900', 'Teléfono del restaurante'),
('email', 'info@elpunto.com', 'Email de contacto'),
('horario', 'Lunes a Domingo: 12:00 - 23:00', 'Horario de atención'),
('moneda', 'USD', 'Moneda del sistema'),
('impuesto', '10', 'Porcentaje de impuesto'),
('version_sistema', '1.0', 'Versión del sistema El Punto');

-- Crear índices para mejorar rendimiento
CREATE INDEX IF NOT EXISTS idx_pedidos_estado ON pedidos(estado);
CREATE INDEX IF NOT EXISTS idx_pedidos_fecha ON pedidos(hora_pedido);
CREATE INDEX IF NOT EXISTS idx_servicios_categoria ON servicios(categoria);
CREATE INDEX IF NOT EXISTS idx_mesas_estado ON mesas(estado);
CREATE INDEX IF NOT EXISTS idx_contactos_estado ON contactos(estado);

-- Crear vista para reportes
CREATE OR REPLACE VIEW vista_pedidos_completos AS
SELECT 
    p.id,
    p.numero_pedido,
    p.estado,
    p.total,
    p.hora_pedido,
    p.hora_entrega,
    m.numero as mesa_numero,
    u.nombre as mesero_nombre,
    COUNT(dp.id) as total_items
FROM pedidos p
LEFT JOIN mesas m ON p.mesa_id = m.id
LEFT JOIN usuarios u ON p.mesero_id = u.id
LEFT JOIN detalle_pedidos dp ON p.id = dp.pedido_id
GROUP BY p.id;

-- Mensaje de confirmación
SELECT 'Base de datos El Punto creada exitosamente!' as mensaje;
