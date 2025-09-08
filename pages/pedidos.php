<?php
/**
 * Página de Pedidos y Disponibilidad - El Punto
 * Sistema de gestión de pedidos y consulta de disponibilidad
 */

session_start();
require_once '../config/config.php';
require_once '../includes/db.php';

// Obtener servicios disponibles
$servicios = [];
try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT * FROM servicios WHERE disponible = 1 ORDER BY categoria, nombre");
    $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // En caso de error, usar datos de ejemplo
    $servicios = [
        [
            'id' => 1,
            'nombre' => 'Pasta Carbonara',
            'descripcion' => 'Pasta italiana con salsa cremosa, panceta y queso parmesano',
            'precio' => 12.99,
            'categoria' => 'Platos Principales',
            'disponible' => 1,
            'tiempo_preparacion' => 15
        ],
        [
            'id' => 2,
            'nombre' => 'Ensalada César',
            'descripcion' => 'Lechuga romana, crutones, queso parmesano y aderezo César',
            'precio' => 8.99,
            'categoria' => 'Entradas',
            'disponible' => 1,
            'tiempo_preparacion' => 8
        ],
        [
            'id' => 3,
            'nombre' => 'Pizza Margherita',
            'descripcion' => 'Pizza tradicional con tomate, mozzarella y albahaca',
            'precio' => 14.99,
            'categoria' => 'Platos Principales',
            'disponible' => 1,
            'tiempo_preparacion' => 20
        ]
    ];
}

// Obtener mesas disponibles
$mesas = [];
try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT * FROM mesas WHERE estado = 'Disponible' ORDER BY numero");
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // En caso de error, usar datos de ejemplo
    $mesas = [
        ['id' => 1, 'numero' => 1, 'capacidad' => 4, 'estado' => 'disponible'],
        ['id' => 2, 'numero' => 2, 'capacidad' => 6, 'estado' => 'disponible'],
        ['id' => 3, 'numero' => 3, 'capacidad' => 4, 'estado' => 'disponible'],
        ['id' => 4, 'numero' => 4, 'capacidad' => 8, 'estado' => 'disponible']
    ];
}

// Obtener pedidos activos
$pedidos_activos = [];
try {
    $db = Database::getInstance();
    $stmt = $db->query("
        SELECT p.*, u.nombre as mesero_nombre, m.numero as mesa_numero
        FROM pedidos p
        LEFT JOIN usuarios u ON p.mesero_id = u.id
        LEFT JOIN mesas m ON p.mesa_id = m.id
        WHERE p.estado IN ('Pendiente', 'En Cocina')
        ORDER BY p.hora_pedido ASC
    ");
    $pedidos_activos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // En caso de error, usar datos de ejemplo
    $pedidos_activos = [
        [
            'id' => 1,
            'numero_pedido' => 'P001',
            'mesa_numero' => 1,
            'mesero_nombre' => 'Carlos López',
            'estado' => 'en_proceso',
            'total' => 45.50,
            'hora_pedido' => '2024-01-15 14:30:00',
            'notas' => 'Sin cebolla en la ensalada'
        ],
        [
            'id' => 2,
            'numero_pedido' => 'P002',
            'mesa_numero' => 3,
            'mesero_nombre' => 'Carlos López',
            'estado' => 'pendiente',
            'total' => 32.75,
            'hora_pedido' => '2024-01-15 15:45:00',
            'notas' => 'Extra queso en la pizza'
        ]
    ];
}

// Procesar formularios
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'crear_pedido') {
        $mesa_id = $_POST['mesa_id'] ?? '';
        $mesero_id = $_POST['mesero_id'] ?? '';
        $notas = $_POST['notas'] ?? '';
        $items = $_POST['items'] ?? [];
        
        if (empty($mesa_id) || empty($mesero_id) || empty($items)) {
            $error_message = 'Por favor completa todos los campos obligatorios';
        } else {
            try {
                $db = Database::getInstance();
                
                // Generar número de pedido único
                $numero_pedido = 'P' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
                
                // Calcular total
                $total = 0;
                foreach ($items as $servicio_id) {
                    $cantidad = $_POST['quantity_' . $servicio_id] ?? 1;
                    $servicio = array_filter($servicios, fn($s) => $s['id'] == $servicio_id);
                    if (!empty($servicio)) {
                        $servicio = array_values($servicio)[0];
                        $total += $servicio['precio'] * $cantidad;
                    }
                }
                
                // Crear pedido
                $stmt = $db->query("
                    INSERT INTO pedidos (numero_pedido, mesa_id, mesero_id, estado, total, notas, hora_pedido) 
                    VALUES (?, ?, ?, 'Pendiente', ?, ?, NOW())
                ", [$numero_pedido, $mesa_id, $mesero_id, $total, $notas]);
                $pedido_id = $db->getConnection()->lastInsertId();
                
                // Crear detalles del pedido
                foreach ($items as $servicio_id) {
                    $cantidad = $_POST['quantity_' . $servicio_id] ?? 1;
                    $servicio = array_filter($servicios, fn($s) => $s['id'] == $servicio_id);
                    if (!empty($servicio)) {
                        $servicio = array_values($servicio)[0];
                        $subtotal = $servicio['precio'] * $cantidad;
                        
                        $stmt = $db->query("
                            INSERT INTO detalle_pedidos (pedido_id, servicio_id, cantidad, precio_unitario, subtotal) 
                            VALUES (?, ?, ?, ?, ?)
                        ", [$pedido_id, $servicio_id, $cantidad, $servicio['precio'], $subtotal]);
                    }
                }
                
                // Actualizar estado de la mesa
                $stmt = $db->query("UPDATE mesas SET estado = 'Ocupada' WHERE id = ?", [$mesa_id]);
                
                $success_message = "Pedido creado exitosamente! Número: {$numero_pedido}";
                
            } catch (Exception $e) {
                $error_message = 'Error al crear el pedido: ' . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'editar_pedido') {
        $pedido_id = $_POST['pedido_id'] ?? '';
        $mesa_id = $_POST['mesa_id'] ?? '';
        $mesero_id = $_POST['mesero_id'] ?? '';
        $estado = $_POST['estado'] ?? '';
        $notas = $_POST['notas'] ?? '';
        $items = $_POST['items'] ?? [];
        
        if (empty($pedido_id) || empty($mesa_id) || empty($mesero_id) || empty($estado)) {
            $error_message = 'Por favor completa todos los campos obligatorios';
        } else {
            try {
                $db = Database::getInstance();
                
                // Calcular nuevo total
                $total = 0;
                foreach ($items as $servicio_id) {
                    $cantidad = $_POST['quantity_' . $servicio_id] ?? 1;
                    $servicio = array_filter($servicios, fn($s) => $s['id'] == $servicio_id);
                    if (!empty($servicio)) {
                        $servicio = array_values($servicio)[0];
                        $total += $servicio['precio'] * $cantidad;
                    }
                }
                
                // Actualizar pedido
                $stmt = $db->query("
                    UPDATE pedidos 
                    SET mesa_id = ?, mesero_id = ?, estado = ?, total = ?, notas = ?
                    WHERE id = ?
                ", [$mesa_id, $mesero_id, $estado, $total, $notas, $pedido_id]);
                
                // Eliminar detalles anteriores
                $stmt = $db->query("DELETE FROM detalle_pedidos WHERE pedido_id = ?", [$pedido_id]);
                
                // Crear nuevos detalles del pedido
                foreach ($items as $servicio_id) {
                    $cantidad = $_POST['quantity_' . $servicio_id] ?? 1;
                    $servicio = array_filter($servicios, fn($s) => $s['id'] == $servicio_id);
                    if (!empty($servicio)) {
                        $servicio = array_values($servicio)[0];
                        $subtotal = $servicio['precio'] * $cantidad;
                        
                        $stmt = $db->query("
                            INSERT INTO detalle_pedidos (pedido_id, servicio_id, cantidad, precio_unitario, subtotal) 
                            VALUES (?, ?, ?, ?, ?)
                        ", [$pedido_id, $servicio_id, $cantidad, $servicio['precio'], $subtotal]);
                    }
                }
                
                $success_message = "Pedido actualizado exitosamente!";
                
            } catch (Exception $e) {
                $error_message = 'Error al actualizar el pedido: ' . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'consultar_disponibilidad') {
        $servicio_id = $_POST['servicio_id'] ?? '';
        if (!empty($servicio_id)) {
            $servicio = array_filter($servicios, fn($s) => $s['id'] == $servicio_id);
            if (!empty($servicio)) {
                $servicio = array_values($servicio)[0];
                $success_message = "El servicio '{$servicio['nombre']}' está disponible. Tiempo de preparación: {$servicio['tiempo_preparacion']} minutos.";
            } else {
                $error_message = 'Servicio no encontrado';
            }
        }
    }
}

// Función para obtener el estado en español
function getEstadoText($estado) {
    switch($estado) {
        case 'Pendiente': return 'Pendiente';
        case 'En Cocina': return 'En Cocina';
        case 'Listo': return 'Listo';
        case 'Entregado': return 'Entregado';
        case 'Cancelado': return 'Cancelado';
        default: return ucfirst($estado);
    }
}

// Función para obtener la clase CSS del estado
function getEstadoClass($estado) {
    switch($estado) {
        case 'Pendiente': return 'pending';
        case 'En Cocina': return 'processing';
        case 'Listo': return 'ready';
        case 'Entregado': return 'completed';
        case 'Cancelado': return 'cancelled';
        default: return 'default';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos y Disponibilidad - El Punto</title>
    <meta name="description" content="Gestiona pedidos y consulta la disponibilidad de servicios en tiempo real">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        .orders-container {
            min-height: 100vh;
            background: var(--gray-100);
            padding: 20px;
        }
        
        .orders-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
            border-radius: 20px;
        }
        
        .orders-header h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .orders-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .orders-content {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .action-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .action-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
        }
        
        .action-icon.create {
            background: var(--success-color);
        }
        
        .action-icon.check {
            background: var(--info-color);
        }
        
        .action-icon.status {
            background: var(--warning-color);
        }
        
        .action-card h3 {
            color: var(--gray-800);
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        
        .action-card p {
            color: var(--gray-600);
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        .action-btn {
            padding: 12px 24px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .action-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .orders-section {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }
        
        .orders-section h2 {
            color: var(--gray-800);
            margin-bottom: 30px;
            font-size: 1.8rem;
            text-align: center;
        }
        
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .order-card {
            background: var(--gray-50);
            border-radius: 15px;
            padding: 25px;
            border-left: 5px solid var(--gray-300);
            transition: all 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .order-card.pending {
            border-left-color: var(--info-color);
            background: rgba(33, 150, 243, 0.05);
        }
        
        .order-card.processing {
            border-left-color: var(--warning-color);
            background: rgba(255, 193, 7, 0.05);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .order-number {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .order-status {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .order-status.pending {
            background: var(--info-color);
            color: white;
        }
        
        .order-status.processing {
            background: var(--warning-color);
            color: white;
        }
        
        .order-details {
            margin-bottom: 20px;
        }
        
        .order-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: var(--gray-600);
        }
        
        .order-detail strong {
            color: var(--gray-800);
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
        }
        
        .order-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-view {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-view:hover {
            background: var(--primary-dark);
        }
        
        .btn-update {
            background: var(--warning-color);
            color: white;
        }
        
        .btn-update:hover {
            background: var(--warning-dark);
        }
        
        .btn-complete {
            background: var(--success-color);
            color: white;
        }
        
        .btn-complete:hover {
            background: var(--success-dark);
        }
        
        .back-home {
            position: fixed;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .back-home:hover {
            transform: translateX(-5px);
        }
        
        .no-orders {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray-600);
        }
        
        .no-orders i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        @media (max-width: 768px) {
            .orders-header h1 {
                font-size: 2rem;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .orders-grid {
                grid-template-columns: 1fr;
            }
            
            .back-home {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 20px;
                color: var(--primary-color);
            }
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow: hidden;
            position: relative;
            animation: modalSlideIn 0.3s ease;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .close-modal {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .close-modal:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .modal-body {
            padding: 30px;
            max-height: 60vh;
            overflow-y: auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--gray-700);
            font-weight: 500;
        }
        
        .form-group select,
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group select:focus,
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 122, 77, 0.1);
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .services-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 10px;
        }
        
        .service-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px;
            border-bottom: 1px solid var(--gray-100);
        }
        
        .service-item:last-child {
            border-bottom: none;
        }
        
        .service-checkbox {
            width: 18px;
            height: 18px;
        }
        
        .service-info {
            flex: 1;
        }
        
        .service-name {
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 5px;
        }
        
        .service-price {
            color: var(--success-color);
            font-weight: 600;
        }
        
        .quantity-input {
            width: 60px;
            padding: 5px;
            text-align: center;
            border: 1px solid var(--gray-200);
            border-radius: 4px;
        }
        
        .modal-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }
        
        .modal-btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
        }
        
        .btn-secondary:hover {
            background: var(--gray-300);
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-home">
        <i class="fas fa-arrow-left"></i>
        Volver al Inicio
    </a>
    
    <div class="orders-container">
        <div class="orders-header">
            <h1>Pedidos y Disponibilidad</h1>
            <p>Gestiona pedidos y consulta la disponibilidad de servicios en tiempo real</p>
        </div>
        
        <div class="orders-content">
            <!-- Mensajes de estado -->
            <?php if ($success_message): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <!-- Acciones Rápidas -->
            <div class="quick-actions">
                <div class="action-card">
                    <div class="action-icon create">
                        <i class="fas fa-plus"></i>
                    </div>
                    <h3>Crear Nuevo Pedido</h3>
                    <p>Registra un nuevo pedido para una mesa específica con todos los detalles</p>
                    <button class="action-btn" onclick="openCreateOrderModal()">
                        <i class="fas fa-plus"></i> Crear Pedido
                    </button>
                </div>
                
                <div class="action-card">
                    <div class="action-icon check">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Consultar Disponibilidad</h3>
                    <p>Verifica si un servicio está disponible y su tiempo de preparación</p>
                    <button class="action-btn" onclick="openAvailabilityModal()">
                        <i class="fas fa-search"></i> Consultar
                    </button>
                </div>
                
                <div class="action-card">
                    <div class="action-icon status">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Estado de Pedidos</h3>
                    <p>Monitorea el estado de todos los pedidos activos en tiempo real</p>
                    <button class="action-btn" onclick="refreshOrders()">
                        <i class="fas fa-sync"></i> Actualizar
                    </button>
                </div>
            </div>
            
            <!-- Pedidos Activos -->
            <div class="orders-section">
                <h2><i class="fas fa-list"></i> Pedidos Activos</h2>
                
                <?php if (empty($pedidos_activos)): ?>
                    <div class="no-orders">
                        <i class="fas fa-clipboard-list"></i>
                        <h3>No hay pedidos activos</h3>
                        <p>Los pedidos aparecerán aquí cuando sean creados</p>
                    </div>
                <?php else: ?>
                    <div class="orders-grid">
                        <?php foreach ($pedidos_activos as $pedido): ?>
                            <div class="order-card <?php echo getEstadoClass($pedido['estado']); ?>">
                                <div class="order-header">
                                    <div class="order-number"><?php echo $pedido['numero_pedido']; ?></div>
                                    <div class="order-status <?php echo getEstadoClass($pedido['estado']); ?>">
                                        <?php echo getEstadoText($pedido['estado']); ?>
                                    </div>
                                </div>
                                
                                <div class="order-details">
                                    <div class="order-detail">
                                        <strong>Mesa:</strong>
                                        <span>Mesa <?php echo $pedido['mesa_numero']; ?></span>
                                    </div>
                                    <div class="order-detail">
                                        <strong>Mesero:</strong>
                                        <span><?php echo $pedido['mesero_nombre']; ?></span>
                                    </div>
                                    <div class="order-detail">
                                        <strong>Total:</strong>
                                        <span>$<?php echo number_format($pedido['total'], 2); ?></span>
                                    </div>
                                    <div class="order-detail">
                                        <strong>Hora:</strong>
                                        <span><?php echo date('H:i', strtotime($pedido['hora_pedido'])); ?></span>
                                    </div>
                                    <?php if ($pedido['notas']): ?>
                                        <div class="order-detail">
                                            <strong>Notas:</strong>
                                            <span><?php echo $pedido['notas']; ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="order-actions">
                                    <button class="order-btn btn-view" onclick="viewOrder(<?php echo htmlspecialchars(json_encode($pedido)); ?>)">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                    <button class="order-btn btn-update" onclick="editOrder(<?php echo $pedido['id']; ?>)">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button class="order-btn btn-update" onclick="updateOrderStatus(<?php echo $pedido['id']; ?>)">
                                        <i class="fas fa-sync"></i> Estado
                                    </button>
                                    <?php if ($pedido['estado'] === 'en_proceso'): ?>
                                        <button class="order-btn btn-complete" onclick="completeOrder(<?php echo $pedido['id']; ?>)">
                                            <i class="fas fa-check"></i> Completar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal de Crear Pedido -->
    <div id="createOrderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Crear Nuevo Pedido</h2>
                <button class="close-modal" onclick="closeModal('createOrderModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="createOrderForm">
                    <input type="hidden" name="action" value="crear_pedido">
                    
                    <div class="form-group">
                        <label for="mesa_id">Mesa *</label>
                        <select name="mesa_id" id="mesa_id" required>
                            <option value="">Selecciona una mesa</option>
                            <?php foreach ($mesas as $mesa): ?>
                                <option value="<?php echo $mesa['id']; ?>">
                                    Mesa <?php echo $mesa['numero']; ?> (<?php echo $mesa['capacidad']; ?> personas)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="mesero_id">Mesero *</label>
                        <select name="mesero_id" id="mesero_id" required>
                            <option value="">Selecciona un mesero</option>
                            <option value="2">Carlos López</option>
                            <option value="3">Ana García</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Servicios *</label>
                        <div class="services-list">
                            <?php foreach ($servicios as $servicio): ?>
                                <div class="service-item">
                                    <input type="checkbox" name="items[]" value="<?php echo $servicio['id']; ?>" 
                                           class="service-checkbox" onchange="updateTotal()">
                                    <div class="service-info">
                                        <div class="service-name"><?php echo $servicio['nombre']; ?></div>
                                        <div class="service-price">$<?php echo number_format($servicio['precio'], 2); ?></div>
                                    </div>
                                    <input type="number" name="quantity_<?php echo $servicio['id']; ?>" 
                                           value="1" min="1" max="10" class="quantity-input" 
                                           onchange="updateTotal()" style="display: none;">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notas">Notas Especiales</label>
                        <textarea name="notas" id="notas" placeholder="Instrucciones especiales, alergias, etc."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Total del Pedido: <span id="orderTotal">$0.00</span></label>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="modal-btn btn-secondary" onclick="closeModal('createOrderModal')">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="modal-btn btn-primary">
                            <i class="fas fa-check"></i> Crear Pedido
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal de Editar Pedido -->
    <div id="editOrderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Pedido</h2>
                <button class="close-modal" onclick="closeModal('editOrderModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="editOrderForm">
                    <input type="hidden" name="action" value="editar_pedido">
                    <input type="hidden" name="pedido_id" id="edit_pedido_id">
                    
                    <div class="form-group">
                        <label for="edit_mesa_id">Mesa *</label>
                        <select name="mesa_id" id="edit_mesa_id" required>
                            <option value="">Selecciona una mesa</option>
                            <?php foreach ($mesas as $mesa): ?>
                                <option value="<?php echo $mesa['id']; ?>">
                                    Mesa <?php echo $mesa['numero']; ?> (<?php echo $mesa['capacidad']; ?> personas)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_mesero_id">Mesero *</label>
                        <select name="mesero_id" id="edit_mesero_id" required>
                            <option value="">Selecciona un mesero</option>
                            <option value="2">Carlos López</option>
                            <option value="3">Ana García</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_estado">Estado *</label>
                        <select name="estado" id="edit_estado" required>
                            <option value="">Selecciona un estado</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="En Cocina">En Cocina</option>
                            <option value="Listo">Listo</option>
                            <option value="Entregado">Entregado</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Servicios *</label>
                        <div class="services-list" id="edit_services_list">
                            <?php foreach ($servicios as $servicio): ?>
                                <div class="service-item">
                                    <input type="checkbox" name="items[]" value="<?php echo $servicio['id']; ?>" 
                                           class="service-checkbox" onchange="updateEditTotal()">
                                    <div class="service-info">
                                        <div class="service-name"><?php echo $servicio['nombre']; ?></div>
                                        <div class="service-price">$<?php echo number_format($servicio['precio'], 2); ?></div>
                                    </div>
                                    <input type="number" name="quantity_<?php echo $servicio['id']; ?>" 
                                           value="1" min="1" max="10" class="quantity-input" 
                                           onchange="updateEditTotal()" style="display: none;">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_notas">Notas Especiales</label>
                        <textarea name="notas" id="edit_notas" placeholder="Instrucciones especiales, alergias, etc."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Total del Pedido: <span id="editOrderTotal">$0.00</span></label>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="modal-btn btn-secondary" onclick="closeModal('editOrderModal')">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="modal-btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal de Consultar Disponibilidad -->
    <div id="availabilityModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Consultar Disponibilidad</h2>
                <button class="close-modal" onclick="closeModal('availabilityModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="" id="availabilityForm">
                    <input type="hidden" name="action" value="consultar_disponibilidad">
                    
                    <div class="form-group">
                        <label for="servicio_id">Servicio</label>
                        <select name="servicio_id" id="servicio_id" required>
                            <option value="">Selecciona un servicio</option>
                            <?php foreach ($servicios as $servicio): ?>
                                <option value="<?php echo $servicio['id']; ?>">
                                    <?php echo $servicio['nombre']; ?> - $<?php echo number_format($servicio['precio'], 2); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="modal-btn btn-secondary" onclick="closeModal('availabilityModal')">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="modal-btn btn-primary">
                            <i class="fas fa-search"></i> Consultar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Abrir modal de crear pedido
        function openCreateOrderModal() {
            document.getElementById('createOrderModal').style.display = 'block';
            updateTotal();
        }
        
        // Abrir modal de disponibilidad
        function openAvailabilityModal() {
            document.getElementById('availabilityModal').style.display = 'block';
        }
        
        // Cerrar modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Actualizar total del pedido
        function updateTotal() {
            let total = 0;
            const checkboxes = document.querySelectorAll('input[name="items[]"]:checked');
            
            checkboxes.forEach(checkbox => {
                const serviceId = checkbox.value;
                const quantityInput = document.querySelector(`input[name="quantity_${serviceId}"]`);
                
                if (quantityInput) {
                    const quantity = parseInt(quantityInput.value) || 1;
                    const price = getServicePrice(serviceId);
                    total += price * quantity;
                    
                    // Mostrar/ocultar input de cantidad
                    quantityInput.style.display = 'block';
                }
            });
            
            // Ocultar inputs de cantidad no seleccionados
            document.querySelectorAll('input[name^="quantity_"]').forEach(input => {
                const serviceId = input.name.replace('quantity_', '');
                const checkbox = document.querySelector(`input[name="items[]"][value="${serviceId}"]`);
                if (!checkbox.checked) {
                    input.style.display = 'none';
                }
            });
            
            document.getElementById('orderTotal').textContent = `$${total.toFixed(2)}`;
        }
        
        // Actualizar total del pedido en edición
        function updateEditTotal() {
            let total = 0;
            const checkboxes = document.querySelectorAll('#edit_services_list input[name="items[]"]:checked');
            
            checkboxes.forEach(checkbox => {
                const serviceId = checkbox.value;
                const quantityInput = document.querySelector(`#edit_services_list input[name="quantity_${serviceId}"]`);
                
                if (quantityInput) {
                    const quantity = parseInt(quantityInput.value) || 1;
                    const price = getServicePrice(serviceId);
                    total += price * quantity;
                    
                    // Mostrar/ocultar input de cantidad
                    quantityInput.style.display = 'block';
                }
            });
            
            // Ocultar inputs de cantidad no seleccionados
            document.querySelectorAll('#edit_services_list input[name^="quantity_"]').forEach(input => {
                const serviceId = input.name.replace('quantity_', '');
                const checkbox = document.querySelector(`#edit_services_list input[name="items[]"][value="${serviceId}"]`);
                if (!checkbox.checked) {
                    input.style.display = 'none';
                }
            });
            
            document.getElementById('editOrderTotal').textContent = `$${total.toFixed(2)}`;
        }
        
        // Obtener precio del servicio
        function getServicePrice(serviceId) {
            const services = <?php echo json_encode($servicios); ?>;
            const service = services.find(s => s.id == serviceId);
            return service ? parseFloat(service.precio) : 0;
        }
        
        // Ver pedido
        function viewOrder(order) {
            alert(`Ver pedido ${order.numero_pedido}. Esta funcionalidad se implementará en futuras versiones.`);
        }
        
        // Editar pedido
        function editOrder(orderId) {
            // Obtener datos del pedido (en una implementación real vendrían de la base de datos)
            const order = {
                id: orderId,
                mesa_id: 1,
                mesero_id: 2,
                estado: 'Pendiente',
                notas: 'Notas del pedido',
                items: [1, 2] // IDs de servicios seleccionados
            };
            
            // Llenar formulario de edición
            document.getElementById('edit_pedido_id').value = order.id;
            document.getElementById('edit_mesa_id').value = order.mesa_id;
            document.getElementById('edit_mesero_id').value = order.mesero_id;
            document.getElementById('edit_estado').value = order.estado;
            document.getElementById('edit_notas').value = order.notas;
            
            // Seleccionar servicios
            document.querySelectorAll('#edit_services_list input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = order.items.includes(parseInt(checkbox.value));
            });
            
            // Actualizar total y mostrar modal
            updateEditTotal();
            document.getElementById('editOrderModal').style.display = 'block';
        }
        
        // Actualizar estado del pedido
        function updateOrderStatus(orderId) {
            if (confirm('¿Estás seguro de que quieres actualizar el estado de este pedido?')) {
                // Aquí se implementaría la actualización del estado
                alert(`Estado del pedido ${orderId} actualizado. Esta funcionalidad se implementará en futuras versiones.`);
            }
        }
        
        // Completar pedido
        function completeOrder(orderId) {
            if (confirm('¿Estás seguro de que quieres marcar este pedido como completado?')) {
                alert(`Pedido ${orderId} marcado como completado. Esta funcionalidad se implementará en futuras versiones.`);
            }
        }
        
        // Actualizar pedidos
        function refreshOrders() {
            location.reload();
        }
        
        // Cerrar modales al hacer clic fuera
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
        
        // Cerrar modales con ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    modal.style.display = 'none';
                });
            }
        });
        
        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            // Configurar eventos para checkboxes
            document.querySelectorAll('input[name="items[]"]').forEach(checkbox => {
                checkbox.addEventListener('change', updateTotal);
            });
            
            // Configurar eventos para inputs de cantidad
            document.querySelectorAll('input[name^="quantity_"]').forEach(input => {
                input.addEventListener('change', updateTotal);
            });
        });
        
        // Auto-refresh cada 2 minutos
        setInterval(function() {
            // Aquí se implementaría la actualización automática
            // desde la base de datos
        }, 120000);
    </script>
</body>
</html>
