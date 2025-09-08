<?php
/**
 * Funciones auxiliares del sistema - El Punto
 * Funciones comunes utilizadas en todo el sistema
 */

/**
 * Obtener configuración del sitio
 */
function getConfig($key) {
    try {
        $db = Database::getInstance();
        if (!$db->isConnected()) {
            return null;
        }
        $stmt = $db->query("SELECT valor FROM configuracion WHERE clave = ?", [$key]);
        if ($stmt === false) {
            return null;
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['valor'] : null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Obtener servicios por categoría
 */
function getServicesByCategory($category = null) {
    try {
        $db = Database::getInstance();
        if (!$db->isConnected()) {
            return [];
        }
        
        if ($category) {
            $stmt = $db->query("SELECT * FROM servicios WHERE categoria = ? AND disponible = 1 ORDER BY nombre", [$category]);
            if ($stmt === false) {
                return [];
            }
        } else {
            $stmt = $db->query("SELECT * FROM servicios WHERE disponible = 1 ORDER BY categoria, nombre");
            if ($stmt === false) {
                return [];
            }
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Obtener filas de la base de datos
 */
function getRows($query, $params = []) {
    try {
        $db = Database::getInstance();
        if (!$db->isConnected()) {
            return [];
        }
        
        $stmt = $db->query($query, $params);
        if ($stmt === false) {
            return [];
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Obtener un valor único de la base de datos
 */
function getValue($query, $params = []) {
    try {
        $db = Database::getInstance();
        if (!$db->isConnected()) {
            return 0;
        }
        
        $stmt = $db->query($query, $params);
        if ($stmt === false) {
            return 0;
        }
        
        $result = $stmt->fetch(PDO::FETCH_NUM);
        return $result ? $result[0] : 0;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Formatear moneda
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Obtener estadísticas del dashboard
 */
function getStats() {
    try {
        $db = Database::getInstance();
        if (!$db->isConnected()) {
            return [
                'total_pedidos' => 0,
                'pedidos_pendientes' => 0,
                'pedidos_cocina' => 0,
                'pedidos_listos' => 0,
                'ventas_hoy' => 0,
                'mesas_ocupadas' => 0
            ];
        }
        
        // Total de pedidos
        $stmt = $db->query("SELECT COUNT(*) as total FROM pedidos");
        if ($stmt === false) {
            $total_pedidos = 0;
        } else {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_pedidos = $result ? $result['total'] : 0;
        }
        
        // Pedidos pendientes
        $stmt = $db->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'Pendiente'");
        if ($stmt === false) {
            $pedidos_pendientes = 0;
        } else {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $pedidos_pendientes = $result ? $result['total'] : 0;
        }
        
        // Pedidos en cocina
        $stmt = $db->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'En Cocina'");
        if ($stmt === false) {
            $pedidos_cocina = 0;
        } else {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $pedidos_cocina = $result ? $result['total'] : 0;
        }
        
        // Pedidos listos
        $stmt = $db->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'Listo'");
        if ($stmt === false) {
            $pedidos_listos = 0;
        } else {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $pedidos_listos = $result ? $result['total'] : 0;
        }
        
        // Ventas de hoy
        $stmt = $db->query("SELECT SUM(total) as total FROM pedidos WHERE estado = 'Entregado' AND DATE(hora_pedido) = CURDATE()");
        if ($stmt === false) {
            $ventas_hoy = 0;
        } else {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $ventas_hoy = $result ? ($result['total'] ?: 0) : 0;
        }
        
        // Mesas ocupadas
        $stmt = $db->query("SELECT COUNT(*) as total FROM mesas WHERE estado = 'Ocupada'");
        if ($stmt === false) {
            $mesas_ocupadas = 0;
        } else {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $mesas_ocupadas = $result ? $result['total'] : 0;
        }
        
        // Servicios activos
        $stmt = $db->query("SELECT COUNT(*) as total FROM servicios WHERE disponible = 1");
        if ($stmt === false) {
            $total_servicios = 0;
        } else {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_servicios = $result ? $result['total'] : 0;
        }
        
        return [
            'total_pedidos' => $total_pedidos,
            'pedidos_pendientes' => $pedidos_pendientes,
            'pedidos_cocina' => $pedidos_cocina,
            'pedidos_listos' => $pedidos_listos,
            'ventas_hoy' => $ventas_hoy,
            'mesas_ocupadas' => $mesas_ocupadas,
            'total_servicios' => $total_servicios
        ];
    } catch (Exception $e) {
        // Retornar datos de ejemplo en caso de error
        return [
            'total_pedidos' => 25,
            'pedidos_pendientes' => 8,
            'pedidos_cocina' => 5,
            'pedidos_listos' => 12,
            'ventas_hoy' => 450.75,
            'mesas_ocupadas' => 6,
            'total_servicios' => 15
        ];
    }
}

/**
 * Obtener mensaje de sesión
 */
function getMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}

/**
 * Establecer mensaje de sesión
 */
function setMessage($text, $type = 'info') {
    $_SESSION['message'] = [
        'text' => $text,
        'type' => $type
    ];
}

/**
 * Generar token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Formatear moneda
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Formatear fecha
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

/**
 * Obtener tiempo transcurrido
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) {
        return 'Hace un momento';
    } elseif ($time < 3600) {
        $minutes = round($time / 60);
        return "Hace $minutes minuto" . ($minutes > 1 ? 's' : '');
    } elseif ($time < 86400) {
        $hours = round($time / 3600);
        return "Hace $hours hora" . ($hours > 1 ? 's' : '');
    } else {
        $days = round($time / 86400);
        return "Hace $days día" . ($days > 1 ? 's' : '');
    }
}

/**
 * Sanitizar entrada de usuario
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validar email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Generar número de pedido único
 */
function generateOrderNumber() {
    $prefix = 'P';
    $date = date('Ymd');
    $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    return $prefix . $date . $random;
}

/**
 * Obtener notificaciones del usuario
 */
function getNotifications($userId, $limit = 10) {
    try {
        $db = Database::getInstance();
        if (!$db->isConnected()) {
            return [];
        }
        
        $stmt = $db->query("
            SELECT * FROM notificaciones 
            WHERE usuario_id = ? OR usuario_id IS NULL 
            ORDER BY fecha_creacion DESC 
            LIMIT ?
        ");
        
        if ($stmt === false) {
            return [];
        }
        
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Marcar notificación como leída
 */
function markNotificationAsRead($notificationId) {
    try {
        $db = Database::getInstance();
        if (!$db->isConnected()) {
            return false;
        }
        
        $stmt = $db->query("UPDATE notificaciones SET leida = 1 WHERE id = ?");
        if ($stmt === false) {
            return false;
        }
        
        return $stmt->execute([$notificationId]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Crear notificación
 */
function createNotification($userId, $titulo, $mensaje, $tipo = 'info') {
    try {
        $db = Database::getInstance();
        if (!$db->isConnected()) {
            return false;
        }
        
        $stmt = $db->query("
            INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo, fecha_creacion) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        if ($stmt === false) {
            return false;
        }
        
        return $stmt->execute([$userId, $titulo, $mensaje, $tipo]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Verificar permisos del usuario
 */
function hasPermission($userId, $permission) {
    try {
        $db = Database::getInstance();
        if (!$db->isConnected()) {
            return false;
        }
        
        $stmt = $db->query("SELECT rol FROM usuarios WHERE id = ?");
        if ($stmt === false) {
            return false;
        }
        
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) return false;
        
        $role = $user['rol'];
        
        // Definir permisos por rol
        $permissions = [
            'Administrador' => ['all'],
            'Mesero' => ['view_orders', 'create_orders', 'view_tables', 'view_menu'],
            'Cocinero' => ['view_orders', 'update_order_status', 'view_menu']
        ];
        
        if (!isset($permissions[$role])) return false;
        
        return in_array('all', $permissions[$role]) || in_array($permission, $permissions[$role]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Log de actividad
 */
function logActivity($userId, $action, $details = '') {
    try {
        $db = Database::getInstance();
        if (!$db->isConnected()) {
            return false;
        }
        
        // Verificar si la tabla log_actividad existe
        $checkTable = $db->query("SHOW TABLES LIKE 'log_actividad'");
        if ($checkTable === false || $checkTable->rowCount() == 0) {
            // La tabla no existe, crear un log simple en archivo
            $logMessage = date('Y-m-d H:i:s') . " - User: $userId, Action: $action, Details: $details\n";
            error_log($logMessage, 3, __DIR__ . '/../logs/activity.log');
            return true;
        }
        
        $stmt = $db->query("
            INSERT INTO log_actividad (usuario_id, accion, detalles, fecha) 
            VALUES (?, ?, ?, NOW())
        ");
        
        if ($stmt === false) {
            return false;
        }
        
        return $stmt->execute([$userId, $action, $details]);
    } catch (Exception $e) {
        // En caso de error, crear log en archivo
        $logMessage = date('Y-m-d H:i:s') . " - User: $userId, Action: $action, Details: $details\n";
        error_log($logMessage, 3, __DIR__ . '/../logs/activity.log');
        return true;
    }
}

/**
 * Obtener estadísticas de ventas por período
 */
function getSalesStats($period = 'today') {
    try {
        $db = Database::getInstance();
        if (!$db->isConnected()) {
            return [
                'total_orders' => 0,
                'total_sales' => 0,
                'avg_order_value' => 0
            ];
        }
        
        switch ($period) {
            case 'today':
                $dateFilter = "DATE(hora_pedido) = CURDATE()";
                break;
            case 'week':
                $dateFilter = "hora_pedido >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $dateFilter = "hora_pedido >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                break;
            default:
                $dateFilter = "DATE(hora_pedido) = CURDATE()";
        }
        
        $stmt = $db->query("
            SELECT 
                COUNT(*) as total_orders,
                SUM(total) as total_sales,
                AVG(total) as avg_order_value
            FROM pedidos 
            WHERE estado = 'Entregado' AND $dateFilter
        ");
        
        if ($stmt === false) {
            return [
                'total_orders' => 0,
                'total_sales' => 0,
                'avg_order_value' => 0
            ];
        }
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [
            'total_orders' => 0,
            'total_sales' => 0,
            'avg_order_value' => 0
        ];
    }
}

/**
 * Obtener productos más vendidos
 */
function getTopProducts($limit = 5) {
    try {
        $db = Database::getInstance();
        if (!$db->isConnected()) {
            return [];
        }
        
        $stmt = $db->query("
            SELECT 
                s.nombre,
                s.categoria,
                COUNT(dp.servicio_id) as veces_vendido,
                SUM(dp.cantidad) as cantidad_total
            FROM detalle_pedidos dp
            JOIN servicios s ON dp.servicio_id = s.id
            JOIN pedidos p ON dp.pedido_id = p.id
            WHERE p.estado = 'Entregado'
            GROUP BY dp.servicio_id
            ORDER BY veces_vendido DESC
            LIMIT ?
        ");
        
        if ($stmt === false) {
            return [];
        }
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Obtener mesas disponibles
 */
function getAvailableTables() {
    try {
        $db = Database::getInstance();
        if (!$db->isConnected()) {
            return [];
        }
        
        $stmt = $db->query("SELECT * FROM mesas WHERE estado = 'Disponible' ORDER BY numero");
        if ($stmt === false) {
            return [];
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Obtener usuarios activos
 */
function getActiveUsers() {
    try {
        $db = Database::getInstance();
        if (!$db->isConnected()) {
            return [];
        }
        
        $stmt = $db->query("SELECT id, nombre, rol FROM usuarios WHERE activo = 1 ORDER BY nombre");
        if ($stmt === false) {
            return [];
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Obtener pedidos recientes
 */
function getRecentOrders($limit = 5) {
    try {
        $db = Database::getInstance();
        if (!$db->isConnected()) {
            return [];
        }
        
        $stmt = $db->query("
            SELECT 
                p.id,
                p.numero_pedido,
                p.estado,
                p.hora_pedido,
                p.total,
                m.numero as mesa_numero,
                u.nombre as mesero_nombre
            FROM pedidos p
            LEFT JOIN mesas m ON p.mesa_id = m.id
            LEFT JOIN usuarios u ON p.mesero_id = u.id
            ORDER BY p.hora_pedido DESC
            LIMIT ?
        ", [$limit]);
        
        if ($stmt === false) {
            return [];
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}
?>
