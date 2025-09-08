<?php
/**
 * Página de Historial - El Punto
 * Historial de pedidos y transacciones
 */

session_start();
require_once '../config/config.php';
require_once '../includes/db.php';

// Obtener pedidos desde la base de datos
$pedidos = [];
try {
    $db = Database::getInstance();
    if (!$db->isConnected()) {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    $stmt = $db->query("
        SELECT p.*, u.nombre as mesero_nombre, m.numero as mesa_numero
        FROM pedidos p
        LEFT JOIN usuarios u ON p.mesero_id = u.id
        LEFT JOIN mesas m ON p.mesa_id = m.id
        ORDER BY p.hora_pedido DESC
        LIMIT 50
    ");
    
    if ($stmt === false) {
        throw new Exception("Error al ejecutar la consulta");
    }
    
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // En caso de error, usar datos de ejemplo
    $pedidos = [
        [
            'id' => 1,
            'numero_pedido' => 'P001',
            'mesa_id' => 1,
            'mesa_numero' => 1,
            'mesero_id' => 2,
            'mesero_nombre' => 'Carlos López',
            'estado' => 'completado',
            'total' => 45.50,
            'hora_pedido' => '2024-01-15 14:30:00',
            'fecha_completado' => '2024-01-15 15:15:00',
            'notas' => 'Sin cebolla en la ensalada'
        ],
        [
            'id' => 2,
            'numero_pedido' => 'P002',
            'mesa_id' => 3,
            'mesa_numero' => 3,
            'mesero_id' => 2,
            'mesero_nombre' => 'Carlos López',
            'estado' => 'en_proceso',
            'total' => 32.75,
            'hora_pedido' => '2024-01-15 15:45:00',
            'fecha_completado' => null,
            'notas' => 'Extra queso en la pizza'
        ],
        [
            'id' => 3,
            'numero_pedido' => 'P003',
            'mesa_id' => 2,
            'mesa_numero' => 2,
            'mesero_id' => 2,
            'mesero_nombre' => 'Carlos López',
            'estado' => 'cancelado',
            'total' => 28.90,
            'hora_pedido' => '2024-01-15 13:20:00',
            'fecha_completado' => null,
            'notas' => 'Cliente cambió de opinión'
        ],
        [
            'id' => 4,
            'numero_pedido' => 'P004',
            'mesa_id' => 5,
            'mesa_numero' => 5,
            'mesero_id' => 2,
            'mesero_nombre' => 'Carlos López',
            'estado' => 'completado',
            'total' => 67.25,
            'hora_pedido' => '2024-01-15 12:15:00',
            'fecha_completado' => '2024-01-15 13:00:00',
            'notas' => 'Postre para llevar'
        ],
        [
            'id' => 5,
            'numero_pedido' => 'P005',
            'mesa_id' => 4,
            'mesa_numero' => 4,
            'mesero_id' => 2,
            'mesero_nombre' => 'Carlos López',
            'estado' => 'pendiente',
            'total' => 41.80,
            'hora_pedido' => '2024-01-15 16:00:00',
            'fecha_completado' => null,
            'notas' => 'Sin gluten'
        ]
    ];
}

// Obtener estadísticas
$estadisticas = [
    'total' => count($pedidos),
    'completados' => count(array_filter($pedidos, fn($p) => $p['estado'] === 'completado')),
    'en_proceso' => count(array_filter($pedidos, fn($p) => $p['estado'] === 'en_proceso')),
    'pendientes' => count(array_filter($pedidos, fn($p) => $p['estado'] === 'pendiente')),
    'cancelados' => count(array_filter($pedidos, fn($p) => $p['estado'] === 'cancelado')),
    'total_ventas' => array_sum(array_column(array_filter($pedidos, fn($p) => $p['estado'] === 'completado'), 'total'))
];

// Obtener filtros
$filtro_estado = $_GET['estado'] ?? 'todos';
$filtro_fecha = $_GET['fecha'] ?? 'hoy';
$busqueda = $_GET['busqueda'] ?? '';

// Aplicar filtros
if ($filtro_estado !== 'todos') {
    $pedidos = array_filter($pedidos, fn($p) => $p['estado'] === $filtro_estado);
}

if ($filtro_fecha === 'hoy') {
    $hoy = date('Y-m-d');
    $pedidos = array_filter($pedidos, fn($p) => date('Y-m-d', strtotime($p['hora_pedido'])) === $hoy);
} elseif ($filtro_fecha === 'semana') {
    $semana_pasada = date('Y-m-d', strtotime('-7 days'));
    $pedidos = array_filter($pedidos, fn($p) => date('Y-m-d', strtotime($p['hora_pedido'])) >= $semana_pasada);
} elseif ($filtro_fecha === 'mes') {
    $mes_pasado = date('Y-m-d', strtotime('-30 days'));
    $pedidos = array_filter($pedidos, fn($p) => date('Y-m-d', strtotime($p['hora_pedido'])) >= $mes_pasado);
}

if ($busqueda) {
    $pedidos = array_filter($pedidos, function($p) use ($busqueda) {
        return stripos($p['numero_pedido'], $busqueda) !== false ||
               stripos($p['mesero_nombre'], $busqueda) !== false ||
               stripos($p['notas'], $busqueda) !== false;
    });
}

// Función para obtener el estado en español
function getEstadoText($estado) {
    switch($estado) {
        case 'pendiente': return 'Pendiente';
        case 'en_proceso': return 'En Proceso';
        case 'completado': return 'Completado';
        case 'cancelado': return 'Cancelado';
        default: return ucfirst($estado);
    }
}

// Función para obtener la clase CSS del estado
function getEstadoClass($estado) {
    switch($estado) {
        case 'pendiente': return 'pending';
        case 'en_proceso': return 'processing';
        case 'completado': return 'completed';
        case 'cancelado': return 'cancelled';
        default: return 'default';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pedidos - El Punto</title>
    <meta name="description" content="Consulta el historial completo de pedidos, transacciones y estadísticas del restaurante">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        .history-container {
            min-height: 100vh;
            background: var(--gray-100);
            padding: 20px;
        }
        
        .history-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
            border-radius: 20px;
        }
        
        .history-header h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .history-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .history-content {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
            color: white;
        }
        
        .stat-icon.total { background: var(--primary-color); }
        .stat-icon.completed { background: var(--success-color); }
        .stat-icon.processing { background: var(--warning-color); }
        .stat-icon.pending { background: var(--info-color); }
        .stat-icon.cancelled { background: var(--danger-color); }
        .stat-icon.sales { background: var(--success-color); }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--gray-600);
            font-weight: 500;
        }
        
        .filters-section {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }
        
        .filters-section h2 {
            color: var(--gray-800);
            margin-bottom: 25px;
            font-size: 1.5rem;
            text-align: center;
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            margin-bottom: 8px;
            color: var(--gray-700);
            font-weight: 500;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 12px;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 122, 77, 0.1);
        }
        
        .filter-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 12px 24px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
        }
        
        .btn-secondary:hover {
            background: var(--gray-300);
        }
        
        .orders-section {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .orders-section h2 {
            color: var(--gray-800);
            margin-bottom: 30px;
            font-size: 1.8rem;
            text-align: center;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .orders-table th,
        .orders-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .orders-table th {
            background: var(--gray-50);
            color: var(--gray-800);
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .orders-table tr:hover {
            background: var(--gray-50);
        }
        
        .order-number {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .order-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            text-align: center;
            min-width: 100px;
        }
        
        .order-status.pending {
            background: var(--info-color);
            color: white;
        }
        
        .order-status.processing {
            background: var(--warning-color);
            color: white;
        }
        
        .order-status.completed {
            background: var(--success-color);
            color: white;
        }
        
        .order-status.cancelled {
            background: var(--danger-color);
            color: white;
        }
        
        .order-total {
            font-weight: 600;
            color: var(--success-color);
        }
        
        .order-actions {
            display: flex;
            gap: 8px;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-view {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-view:hover {
            background: var(--primary-dark);
        }
        
        .btn-edit {
            background: var(--warning-color);
            color: white;
        }
        
        .btn-edit:hover {
            background: var(--warning-dark);
        }
        
        .btn-print {
            background: var(--info-color);
            color: white;
        }
        
        .btn-print:hover {
            background: var(--info-dark);
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
        
        .export-section {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
            text-align: center;
        }
        
        .export-section h2 {
            color: var(--gray-800);
            margin-bottom: 25px;
            font-size: 1.5rem;
        }
        
        .export-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .export-btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-excel {
            background: #217346;
            color: white;
        }
        
        .btn-pdf {
            background: #dc3545;
            color: white;
        }
        
        .btn-csv {
            background: var(--primary-color);
            color: white;
        }
        
        @media (max-width: 768px) {
            .history-header h1 {
                font-size: 2rem;
            }
            
            .stats-section {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .orders-table {
                font-size: 14px;
            }
            
            .orders-table th,
            .orders-table td {
                padding: 10px 8px;
            }
            
            .order-actions {
                flex-direction: column;
                gap: 5px;
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
            max-width: 800px;
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
        
        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .detail-item {
            text-align: center;
            padding: 15px;
            background: var(--gray-50);
            border-radius: 10px;
        }
        
        .detail-label {
            font-size: 12px;
            color: var(--gray-600);
            text-transform: uppercase;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--gray-800);
        }
        
        .order-items {
            margin-bottom: 25px;
        }
        
        .order-items h3 {
            color: var(--gray-800);
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        .item-list {
            background: var(--gray-50);
            border-radius: 10px;
            padding: 20px;
        }
        
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .item-row:last-child {
            border-bottom: none;
        }
        
        .item-name {
            font-weight: 500;
            color: var(--gray-800);
        }
        
        .item-price {
            color: var(--success-color);
            font-weight: 600;
        }
        
        .modal-actions {
            display: flex;
            gap: 15px;
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
    </style>
</head>
<body>
    <a href="../index.php" class="back-home">
        <i class="fas fa-arrow-left"></i>
        Volver al Inicio
    </a>
    
    <div class="history-container">
        <div class="history-header">
            <h1>Historial de Pedidos</h1>
            <p>Consulta el historial completo de pedidos, transacciones y estadísticas del restaurante</p>
        </div>
        
        <div class="history-content">
            <!-- Estadísticas -->
            <div class="stats-section">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="stat-number"><?php echo $estadisticas['total']; ?></div>
                    <div class="stat-label">Total de Pedidos</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon completed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number"><?php echo $estadisticas['completados']; ?></div>
                    <div class="stat-label">Completados</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon processing">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number"><?php echo $estadisticas['en_proceso']; ?></div>
                    <div class="stat-label">En Proceso</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-number"><?php echo $estadisticas['pendientes']; ?></div>
                    <div class="stat-label">Pendientes</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon cancelled">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-number"><?php echo $estadisticas['cancelados']; ?></div>
                    <div class="stat-label">Cancelados</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon sales">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-number">$<?php echo number_format($estadisticas['total_ventas'], 2); ?></div>
                    <div class="stat-label">Total Ventas</div>
                </div>
            </div>
            
            <!-- Filtros -->
            <div class="filters-section">
                <h2><i class="fas fa-filter"></i> Filtros y Búsqueda</h2>
                
                <form method="GET" action="" id="filtersForm">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="estado">Estado del Pedido</label>
                            <select name="estado" id="estado">
                                <option value="todos" <?php echo $filtro_estado === 'todos' ? 'selected' : ''; ?>>Todos los Estados</option>
                                <option value="pendiente" <?php echo $filtro_estado === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="en_proceso" <?php echo $filtro_estado === 'en_proceso' ? 'selected' : ''; ?>>En Proceso</option>
                                <option value="completado" <?php echo $filtro_estado === 'completado' ? 'selected' : ''; ?>>Completado</option>
                                <option value="cancelado" <?php echo $filtro_estado === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="fecha">Período de Tiempo</label>
                            <select name="fecha" id="fecha">
                                <option value="todos" <?php echo $filtro_fecha === 'todos' ? 'selected' : ''; ?>>Todas las Fechas</option>
                                <option value="hoy" <?php echo $filtro_fecha === 'hoy' ? 'selected' : ''; ?>>Hoy</option>
                                <option value="semana" <?php echo $filtro_fecha === 'semana' ? 'selected' : ''; ?>>Última Semana</option>
                                <option value="mes" <?php echo $filtro_fecha === 'mes' ? 'selected' : ''; ?>>Último Mes</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="busqueda">Buscar</label>
                            <input type="text" name="busqueda" id="busqueda" 
                                   placeholder="Número de pedido, mesero, notas..." 
                                   value="<?php echo htmlspecialchars($busqueda); ?>">
                        </div>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="filter-btn">
                            <i class="fas fa-search"></i> Aplicar Filtros
                        </button>
                        <button type="button" class="filter-btn btn-secondary" onclick="resetFilters()">
                            <i class="fas fa-undo"></i> Limpiar Filtros
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Exportar -->
            <div class="export-section">
                <h2><i class="fas fa-download"></i> Exportar Historial</h2>
                <div class="export-buttons">
                    <button class="export-btn btn-excel" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> Exportar a Excel
                    </button>
                    <button class="export-btn btn-pdf" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf"></i> Exportar a PDF
                    </button>
                    <button class="export-btn btn-csv" onclick="exportToCSV()">
                        <i class="fas fa-file-csv"></i> Exportar a CSV
                    </button>
                </div>
            </div>
            
            <!-- Lista de Pedidos -->
            <div class="orders-section">
                <h2><i class="fas fa-list"></i> Lista de Pedidos</h2>
                
                <?php if (empty($pedidos)): ?>
                    <div class="no-orders">
                        <i class="fas fa-search"></i>
                        <h3>No se encontraron pedidos</h3>
                        <p>Intenta cambiar los filtros o la búsqueda</p>
                    </div>
                <?php else: ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Pedido</th>
                                <th>Mesa</th>
                                <th>Mesero</th>
                                <th>Estado</th>
                                <th>Total</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td>
                                        <span class="order-number"><?php echo $pedido['numero_pedido']; ?></span>
                                    </td>
                                    <td>Mesa <?php echo $pedido['mesa_numero']; ?></td>
                                    <td><?php echo $pedido['mesero_nombre']; ?></td>
                                    <td>
                                        <span class="order-status <?php echo getEstadoClass($pedido['estado']); ?>">
                                            <?php echo getEstadoText($pedido['estado']); ?>
                                        </span>
                                    </td>
                                    <td class="order-total">$<?php echo number_format($pedido['total'], 2); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['hora_pedido'])); ?></td>
                                    <td>
                                        <div class="order-actions">
                                            <button class="action-btn btn-view" onclick="viewOrder(<?php echo htmlspecialchars(json_encode($pedido)); ?>)">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                            <button class="action-btn btn-edit" onclick="editOrder(<?php echo $pedido['id']; ?>)">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
                                            <button class="action-btn btn-print" onclick="printOrder(<?php echo $pedido['id']; ?>)">
                                                <i class="fas fa-print"></i> Imprimir
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal de Detalles del Pedido -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Detalles del Pedido</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="order-details">
                    <div class="detail-item">
                        <div class="detail-label">Número de Pedido</div>
                        <div class="detail-value" id="modalOrderNumber">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Mesa</div>
                        <div class="detail-value" id="modalTable">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Mesero</div>
                        <div class="detail-value" id="modalWaiter">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Estado</div>
                        <div class="detail-value" id="modalStatus">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Total</div>
                        <div class="detail-value" id="modalTotal">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Fecha</div>
                        <div class="detail-value" id="modalDate">-</div>
                    </div>
                </div>
                
                <div class="order-items">
                    <h3>Items del Pedido</h3>
                    <div class="item-list" id="modalItems">
                        <div class="item-row">
                            <span class="item-name">Ejemplo de item</span>
                            <span class="item-price">$0.00</span>
                        </div>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button class="modal-btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                    <button class="modal-btn btn-primary" onclick="printOrderFromModal()">
                        <i class="fas fa-print"></i> Imprimir Pedido
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let selectedOrder = null;
        
        // Ver detalles del pedido
        function viewOrder(order) {
            selectedOrder = order;
            
            document.getElementById('modalTitle').textContent = `Pedido ${order.numero_pedido}`;
            document.getElementById('modalOrderNumber').textContent = order.numero_pedido;
            document.getElementById('modalTable').textContent = `Mesa ${order.mesa_numero}`;
            document.getElementById('modalWaiter').textContent = order.mesero_nombre;
            document.getElementById('modalStatus').textContent = getEstadoText(order.estado);
            document.getElementById('modalTotal').textContent = `$${parseFloat(order.total).toFixed(2)}`;
            document.getElementById('modalDate').textContent = formatDate(order.hora_pedido);
            
            // Simular items del pedido (en una implementación real vendrían de la base de datos)
            const itemsHtml = `
                <div class="item-row">
                    <span class="item-name">Pasta Carbonara</span>
                    <span class="item-price">$12.99</span>
                </div>
                <div class="item-row">
                    <span class="item-name">Ensalada César</span>
                    <span class="item-price">$8.99</span>
                </div>
                <div class="item-row">
                    <span class="item-name">Tiramisú</span>
                    <span class="item-price">$6.99</span>
                </div>
            `;
            document.getElementById('modalItems').innerHTML = itemsHtml;
            
            // Mostrar modal
            document.getElementById('orderModal').style.display = 'block';
        }
        
        // Editar pedido
        function editOrder(orderId) {
            alert(`Editar pedido ${orderId}. Esta funcionalidad se implementará en futuras versiones.`);
        }
        
        // Imprimir pedido
        function printOrder(orderId) {
            // Obtener datos del pedido (en una implementación real vendrían de la base de datos)
            const order = {
                id: orderId,
                numero_pedido: 'P' + orderId.toString().padStart(3, '0'),
                mesa_numero: Math.floor(Math.random() * 10) + 1,
                mesero_nombre: 'Carlos López',
                estado: 'Completado',
                total: (Math.random() * 100 + 20).toFixed(2),
                hora_pedido: new Date().toISOString(),
                notas: 'Sin cebolla en la ensalada',
                items: [
                    { nombre: 'Pasta Carbonara', precio: 12.99, cantidad: 1 },
                    { nombre: 'Ensalada César', precio: 8.99, cantidad: 1 },
                    { nombre: 'Tiramisú', precio: 6.99, cantidad: 1 }
                ]
            };
            
            // Crear ventana de impresión
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <title>Pedido ${order.numero_pedido}</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 20px;
                            color: #333;
                        }
                        .header {
                            text-align: center;
                            border-bottom: 2px solid #007a4d;
                            padding-bottom: 20px;
                            margin-bottom: 30px;
                        }
                        .header h1 {
                            color: #007a4d;
                            margin: 0;
                            font-size: 28px;
                        }
                        .header h2 {
                            margin: 5px 0 0 0;
                            font-size: 18px;
                            color: #666;
                        }
                        .order-info {
                            display: grid;
                            grid-template-columns: 1fr 1fr;
                            gap: 20px;
                            margin-bottom: 30px;
                        }
                        .info-section {
                            background: #f8f9fa;
                            padding: 15px;
                            border-radius: 8px;
                        }
                        .info-section h3 {
                            margin: 0 0 10px 0;
                            color: #007a4d;
                            font-size: 16px;
                        }
                        .info-item {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 5px;
                        }
                        .info-label {
                            font-weight: bold;
                        }
                        .items-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 30px;
                        }
                        .items-table th,
                        .items-table td {
                            padding: 12px;
                            text-align: left;
                            border-bottom: 1px solid #ddd;
                        }
                        .items-table th {
                            background: #007a4d;
                            color: white;
                            font-weight: bold;
                        }
                        .items-table tr:nth-child(even) {
                            background: #f8f9fa;
                        }
                        .total-section {
                            text-align: right;
                            margin-top: 20px;
                        }
                        .total-amount {
                            font-size: 24px;
                            font-weight: bold;
                            color: #007a4d;
                        }
                        .footer {
                            margin-top: 40px;
                            text-align: center;
                            font-size: 12px;
                            color: #666;
                            border-top: 1px solid #ddd;
                            padding-top: 20px;
                        }
                        @media print {
                            body { margin: 0; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>RESTAURANTE GRAN MURALLA CHINA</h1>
                        <h2>Cúcuta, Norte de Santander</h2>
                    </div>
                    
                    <div class="order-info">
                        <div class="info-section">
                            <h3>Información del Pedido</h3>
                            <div class="info-item">
                                <span class="info-label">Número:</span>
                                <span>${order.numero_pedido}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Mesa:</span>
                                <span>${order.mesa_numero}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Mesero:</span>
                                <span>${order.mesero_nombre}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Estado:</span>
                                <span>${order.estado}</span>
                            </div>
                        </div>
                        
                        <div class="info-section">
                            <h3>Fecha y Hora</h3>
                            <div class="info-item">
                                <span class="info-label">Fecha:</span>
                                <span>${new Date(order.hora_pedido).toLocaleDateString('es-ES')}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Hora:</span>
                                <span>${new Date(order.hora_pedido).toLocaleTimeString('es-ES')}</span>
                            </div>
                            ${order.notas ? `
                            <div class="info-item">
                                <span class="info-label">Notas:</span>
                                <span>${order.notas}</span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${order.items.map(item => `
                                <tr>
                                    <td>${item.nombre}</td>
                                    <td>${item.cantidad}</td>
                                    <td>$${item.precio.toFixed(2)}</td>
                                    <td>$${(item.precio * item.cantidad).toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                    
                    <div class="total-section">
                        <div class="total-amount">
                            Total: $${order.total}
                        </div>
                    </div>
                    
                    <div class="footer">
                        <p>¡Gracias por su visita!</p>
                        <p>Restaurante Gran Muralla China - Cúcuta</p>
                        <p>Tel: +57 (7) 571-2345 | Email: info@granmurallachina.com</p>
                    </div>
                </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.focus();
            
            // Esperar a que se cargue el contenido y luego imprimir
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
        }
        
        // Imprimir desde modal
        function printOrderFromModal() {
            if (selectedOrder) {
                // Usar los datos reales del pedido seleccionado
                const order = {
                    id: selectedOrder.id,
                    numero_pedido: selectedOrder.numero_pedido,
                    mesa_numero: selectedOrder.mesa_numero,
                    mesero_nombre: selectedOrder.mesero_nombre,
                    estado: selectedOrder.estado,
                    total: selectedOrder.total,
                    hora_pedido: selectedOrder.hora_pedido,
                    notas: selectedOrder.notas || '',
                    items: [
                        { nombre: 'Pasta Carbonara', precio: 12.99, cantidad: 1 },
                        { nombre: 'Ensalada César', precio: 8.99, cantidad: 1 },
                        { nombre: 'Tiramisú', precio: 6.99, cantidad: 1 }
                    ]
                };
                
                // Crear ventana de impresión con datos reales
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html lang="es">
                    <head>
                        <meta charset="UTF-8">
                        <title>Pedido ${order.numero_pedido}</title>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                margin: 20px;
                                color: #333;
                            }
                            .header {
                                text-align: center;
                                border-bottom: 2px solid #007a4d;
                                padding-bottom: 20px;
                                margin-bottom: 30px;
                            }
                            .header h1 {
                                color: #007a4d;
                                margin: 0;
                                font-size: 28px;
                            }
                            .header h2 {
                                margin: 5px 0 0 0;
                                font-size: 18px;
                                color: #666;
                            }
                            .order-info {
                                display: grid;
                                grid-template-columns: 1fr 1fr;
                                gap: 20px;
                                margin-bottom: 30px;
                            }
                            .info-section {
                                background: #f8f9fa;
                                padding: 15px;
                                border-radius: 8px;
                            }
                            .info-section h3 {
                                margin: 0 0 10px 0;
                                color: #007a4d;
                                font-size: 16px;
                            }
                            .info-item {
                                display: flex;
                                justify-content: space-between;
                                margin-bottom: 5px;
                            }
                            .info-label {
                                font-weight: bold;
                            }
                            .items-table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-bottom: 30px;
                            }
                            .items-table th,
                            .items-table td {
                                padding: 12px;
                                text-align: left;
                                border-bottom: 1px solid #ddd;
                            }
                            .items-table th {
                                background: #007a4d;
                                color: white;
                                font-weight: bold;
                            }
                            .items-table tr:nth-child(even) {
                                background: #f8f9fa;
                            }
                            .total-section {
                                text-align: right;
                                margin-top: 20px;
                            }
                            .total-amount {
                                font-size: 24px;
                                font-weight: bold;
                                color: #007a4d;
                            }
                            .footer {
                                margin-top: 40px;
                                text-align: center;
                                font-size: 12px;
                                color: #666;
                                border-top: 1px solid #ddd;
                                padding-top: 20px;
                            }
                            @media print {
                                body { margin: 0; }
                                .no-print { display: none; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1>RESTAURANTE GRAN MURALLA CHINA</h1>
                            <h2>Cúcuta, Norte de Santander</h2>
                        </div>
                        
                        <div class="order-info">
                            <div class="info-section">
                                <h3>Información del Pedido</h3>
                                <div class="info-item">
                                    <span class="info-label">Número:</span>
                                    <span>${order.numero_pedido}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Mesa:</span>
                                    <span>${order.mesa_numero}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Mesero:</span>
                                    <span>${order.mesero_nombre}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Estado:</span>
                                    <span>${order.estado}</span>
                                </div>
                            </div>
                            
                            <div class="info-section">
                                <h3>Fecha y Hora</h3>
                                <div class="info-item">
                                    <span class="info-label">Fecha:</span>
                                    <span>${new Date(order.hora_pedido).toLocaleDateString('es-ES')}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Hora:</span>
                                    <span>${new Date(order.hora_pedido).toLocaleTimeString('es-ES')}</span>
                                </div>
                                ${order.notas ? `
                                <div class="info-item">
                                    <span class="info-label">Notas:</span>
                                    <span>${order.notas}</span>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                        
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${order.items.map(item => `
                                    <tr>
                                        <td>${item.nombre}</td>
                                        <td>${item.cantidad}</td>
                                        <td>$${item.precio.toFixed(2)}</td>
                                        <td>$${(item.precio * item.cantidad).toFixed(2)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                        
                        <div class="total-section">
                            <div class="total-amount">
                                Total: $${parseFloat(order.total).toFixed(2)}
                            </div>
                        </div>
                        
                        <div class="footer">
                            <p>¡Gracias por su visita!</p>
                            <p>Restaurante Gran Muralla China - Cúcuta</p>
                            <p>Tel: +57 (7) 571-2345 | Email: info@granmurallachina.com</p>
                        </div>
                    </body>
                    </html>
                `);
                
                printWindow.document.close();
                printWindow.focus();
                
                // Esperar a que se cargue el contenido y luego imprimir
                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 500);
            }
        }
        
        // Cerrar modal
        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
            selectedOrder = null;
        }
        
        // Limpiar filtros
        function resetFilters() {
            document.getElementById('estado').value = 'todos';
            document.getElementById('fecha').value = 'todos';
            document.getElementById('busqueda').value = '';
            document.getElementById('filtersForm').submit();
        }
        
        // Exportar funciones
        function exportToExcel() {
            alert('Exportar a Excel. Esta funcionalidad se implementará en futuras versiones.');
        }
        
        function exportToPDF() {
            alert('Exportar a PDF. Esta funcionalidad se implementará en futuras versiones.');
        }
        
        function exportToCSV() {
            alert('Exportar a CSV. Esta funcionalidad se implementará en futuras versiones.');
        }
        
        // Utilidades
        function getEstadoText(estado) {
            switch(estado) {
                case 'pendiente': return 'Pendiente';
                case 'en_proceso': return 'En Proceso';
                case 'completado': return 'Completado';
                case 'cancelado': return 'Cancelado';
                default: return estado.charAt(0).toUpperCase() + estado.slice(1);
            }
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target === modal) {
                closeModal();
            }
        }
        
        // Cerrar modal con ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
        
        // Auto-refresh cada 5 minutos
        setInterval(function() {
            // Aquí se implementaría la actualización automática
            // desde la base de datos
        }, 300000);
    </script>
</body>
</html>
