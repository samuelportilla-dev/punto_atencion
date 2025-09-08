<?php
/**
 * Página de Ubicación de Mesas - El Punto
 * Mapa interactivo del salón y gestión de mesas
 */

session_start();
require_once '../config/config.php';
require_once '../includes/db.php';

// Procesar acciones CRUD
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $db = Database::getInstance();
        
        if ($_POST['action'] === 'crear_mesa') {
            $numero = $_POST['numero'] ?? '';
            $capacidad = $_POST['capacidad'] ?? 4;
            $ubicacion = $_POST['ubicacion'] ?? 'interior';
            $x = $_POST['x'] ?? 100;
            $y = $_POST['y'] ?? 100;
            
            if (empty($numero)) {
                $error_message = 'El número de mesa es obligatorio';
            } else {
                $stmt = $db->query("INSERT INTO mesas (numero, capacidad, estado, ubicacion, x, y) VALUES (?, ?, 'Disponible', ?, ?, ?)", [$numero, $capacidad, $ubicacion, $x, $y]);
                $success_message = "Mesa {$numero} creada exitosamente";
            }
            
        } elseif ($_POST['action'] === 'editar_mesa') {
            $id = $_POST['id'] ?? '';
            $numero = $_POST['numero'] ?? '';
            $capacidad = $_POST['capacidad'] ?? 4;
            $estado = $_POST['estado'] ?? 'Disponible';
            $ubicacion = $_POST['ubicacion'] ?? 'interior';
            $x = $_POST['x'] ?? 100;
            $y = $_POST['y'] ?? 100;
            
            if (empty($id) || empty($numero)) {
                $error_message = 'Datos incompletos para editar la mesa';
            } else {
                $stmt = $db->query("UPDATE mesas SET numero = ?, capacidad = ?, estado = ?, ubicacion = ?, x = ?, y = ? WHERE id = ?", [$numero, $capacidad, $estado, $ubicacion, $x, $y, $id]);
                $success_message = "Mesa {$numero} actualizada exitosamente";
            }
            
        } elseif ($_POST['action'] === 'eliminar_mesa') {
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                $error_message = 'ID de mesa no válido';
            } else {
                // Verificar si la mesa tiene pedidos activos
                $stmt = $db->query("SELECT COUNT(*) FROM pedidos WHERE mesa_id = ? AND estado IN ('Pendiente', 'En Cocina', 'Listo')", [$id]);
                $pedidos_activos = $stmt->fetchColumn();
                
                if ($pedidos_activos > 0) {
                    $error_message = 'No se puede eliminar la mesa porque tiene pedidos activos';
                } else {
                    $stmt = $db->query("DELETE FROM mesas WHERE id = ?", [$id]);
                    $success_message = "Mesa eliminada exitosamente";
                }
            }
        }
        
    } catch (Exception $e) {
        $error_message = 'Error: ' . $e->getMessage();
    }
}

// Obtener mesas desde la base de datos
$mesas = [];
try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT * FROM mesas ORDER BY numero");
    $mesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // En caso de error, usar datos de ejemplo
    $mesas = [
        [
            'id' => 1,
            'numero' => 1,
            'capacidad' => 4,
            'estado' => 'Disponible',
            'ubicacion' => 'terraza',
            'x' => 100,
            'y' => 100
        ],
        [
            'id' => 2,
            'numero' => 2,
            'capacidad' => 6,
            'estado' => 'Ocupada',
            'ubicacion' => 'terraza',
            'x' => 200,
            'y' => 100
        ],
        [
            'id' => 3,
            'numero' => 3,
            'capacidad' => 4,
            'estado' => 'Reservada',
            'ubicacion' => 'interior',
            'x' => 100,
            'y' => 200
        ],
        [
            'id' => 4,
            'numero' => 4,
            'capacidad' => 8,
            'estado' => 'Disponible',
            'ubicacion' => 'interior',
            'x' => 200,
            'y' => 200
        ],
        [
            'id' => 5,
            'numero' => 5,
            'capacidad' => 2,
            'estado' => 'Ocupada',
            'ubicacion' => 'bar',
            'x' => 300,
            'y' => 150
        ],
        [
            'id' => 6,
            'numero' => 6,
            'capacidad' => 4,
            'estado' => 'Disponible',
            'ubicacion' => 'bar',
            'x' => 400,
            'y' => 150
        ]
    ];
}

// Obtener estadísticas de mesas
$estadisticas = [
    'total' => count($mesas),
    'disponibles' => count(array_filter($mesas, fn($m) => $m['estado'] === 'Disponible')),
    'ocupadas' => count(array_filter($mesas, fn($m) => $m['estado'] === 'Ocupada')),
    'reservadas' => count(array_filter($mesas, fn($m) => $m['estado'] === 'Reservada')),
    'limpieza' => count(array_filter($mesas, fn($m) => $m['estado'] === 'Mantenimiento'))
];

// Obtener ubicaciones únicas
$ubicaciones = array_unique(array_column($mesas, 'ubicacion'));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubicación de Mesas - El Punto</title>
    <meta name="description" content="Visualiza la ubicación de todas las mesas del restaurante y su estado en tiempo real">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        .tables-container {
            min-height: 100vh;
            background: var(--gray-100);
            padding: 20px;
        }
        
        .tables-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
            border-radius: 20px;
        }
        
        .tables-header h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .tables-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .tables-content {
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
        
        .stat-icon.available {
            background: var(--success-color);
        }
        
        .stat-icon.occupied {
            background: var(--danger-color);
        }
        
        .stat-icon.reserved {
            background: var(--warning-color);
        }
        
        .stat-icon.cleaning {
            background: var(--info-color);
        }
        
        .stat-icon.total {
            background: var(--primary-color);
        }
        
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
        
        .map-section {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }
        
        .map-section h2 {
            color: var(--gray-800);
            margin-bottom: 30px;
            font-size: 1.8rem;
            text-align: center;
        }
        
        .restaurant-map {
            position: relative;
            width: 100%;
            height: 600px;
            background: linear-gradient(135deg, var(--gray-50), var(--gray-100));
            border-radius: 15px;
            border: 3px solid var(--gray-200);
            overflow: hidden;
        }
        
        .map-area {
            position: absolute;
            border: 2px dashed var(--gray-300);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 14px;
        }
        
        .map-area.terraza {
            top: 20px;
            left: 20px;
            width: 300px;
            height: 200px;
            background: rgba(76, 175, 80, 0.1);
            border-color: var(--success-color);
        }
        
        .map-area.interior {
            top: 20px;
            right: 20px;
            width: 400px;
            height: 300px;
            background: rgba(33, 150, 243, 0.1);
            border-color: var(--info-color);
        }
        
        .map-area.bar {
            bottom: 20px;
            left: 20px;
            width: 250px;
            height: 150px;
            background: rgba(255, 193, 7, 0.1);
            border-color: var(--warning-color);
        }
        
        .table-marker {
            position: absolute;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .table-marker:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }
        
        .table-marker.available {
            background: var(--success-color);
        }
        
        .table-marker.occupied {
            background: var(--danger-color);
        }
        
        .table-marker.reserved {
            background: var(--warning-color);
        }
        
        .table-marker.cleaning {
            background: var(--info-color);
        }
        
        .table-marker::after {
            content: attr(data-capacity);
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            color: var(--gray-700);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 500;
            white-space: nowrap;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .legend {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gray-700);
            font-weight: 500;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 50%;
        }
        
        .legend-color.available { background: var(--success-color); }
        .legend-color.occupied { background: var(--danger-color); }
        .legend-color.reserved { background: var(--warning-color); }
        .legend-color.cleaning { background: var(--info-color); }
        
        .tables-list {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .tables-list h2 {
            color: var(--gray-800);
            margin-bottom: 30px;
            font-size: 1.8rem;
            text-align: center;
        }
        
        .tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .table-card {
            background: var(--gray-50);
            border-radius: 15px;
            padding: 25px;
            border-left: 5px solid var(--gray-300);
            transition: all 0.3s ease;
        }
        
        .table-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .table-card.available {
            border-left-color: var(--success-color);
            background: rgba(76, 175, 80, 0.05);
        }
        
        .table-card.occupied {
            border-left-color: var(--danger-color);
            background: rgba(244, 67, 54, 0.05);
        }
        
        .table-card.reserved {
            border-left-color: var(--warning-color);
            background: rgba(255, 193, 7, 0.05);
        }
        
        .table-card.cleaning {
            border-left-color: var(--info-color);
            background: rgba(33, 150, 243, 0.05);
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .table-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--gray-800);
        }
        
        .table-status {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .table-status.available {
            background: var(--success-color);
            color: white;
        }
        
        .table-status.occupied {
            background: var(--danger-color);
            color: white;
        }
        
        .table-status.reserved {
            background: var(--warning-color);
            color: white;
        }
        
        .table-status.cleaning {
            background: var(--info-color);
            color: white;
        }
        
        .table-details {
            margin-bottom: 20px;
        }
        
        .table-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: var(--gray-600);
        }
        
        .table-detail strong {
            color: var(--gray-800);
        }
        
        .table-actions {
            display: flex;
            gap: 10px;
        }
        
        .table-btn {
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
        
        .btn-edit {
            background: var(--warning-color);
            color: white;
        }
        
        .btn-edit:hover {
            background: var(--warning-dark);
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
        
        .filters-section {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
            text-align: center;
        }
        
        .filters-section h2 {
            color: var(--gray-800);
            margin-bottom: 25px;
            font-size: 1.5rem;
        }
        
        .filter-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }
        
        .filter-btn {
            padding: 12px 24px;
            background: var(--gray-200);
            color: var(--gray-700);
            border: none;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover,
        .filter-btn.active {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        @media (max-width: 768px) {
            .tables-header h1 {
                font-size: 2rem;
            }
            
            .stats-section {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .restaurant-map {
                height: 400px;
            }
            
            .map-area {
                font-size: 12px;
            }
            
            .table-marker {
                width: 50px;
                height: 50px;
                font-size: 12px;
            }
            
            .tables-grid {
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
            display: none !important;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }
        
        .modal.show {
            display: block !important;
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
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
        }
        
        .modal-details {
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--gray-700);
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 122, 77, 0.1);
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-home">
        <i class="fas fa-arrow-left"></i>
        Volver al Inicio
    </a>
    
    <div class="tables-container">
        <div class="tables-header">
            <h1>Ubicación de Mesas</h1>
            <p>Visualiza la distribución del salón y el estado de todas las mesas en tiempo real</p>
        </div>
        
        <div class="tables-content">
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
            
            <!-- Estadísticas -->
            <div class="stats-section">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-table"></i>
                    </div>
                    <div class="stat-number"><?php echo $estadisticas['total']; ?></div>
                    <div class="stat-label">Total de Mesas</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon available">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number"><?php echo $estadisticas['disponibles']; ?></div>
                    <div class="stat-label">Disponibles</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon occupied">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-number"><?php echo $estadisticas['ocupadas']; ?></div>
                    <div class="stat-label">Ocupadas</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon reserved">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-number"><?php echo $estadisticas['reservadas']; ?></div>
                    <div class="stat-label">Reservadas</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon cleaning">
                        <i class="fas fa-broom"></i>
                    </div>
                    <div class="stat-number"><?php echo $estadisticas['limpieza']; ?></div>
                    <div class="stat-label">En Limpieza</div>
                </div>
            </div>
            
            <!-- Filtros y Acciones -->
            <div class="filters-section">
                <h2><i class="fas fa-filter"></i> Filtrar por Estado</h2>
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filterTables('todas')">
                        Todas las Mesas
                    </button>
                    <button class="filter-btn" onclick="filterTables('Disponible')">
                        Disponibles
                    </button>
                    <button class="filter-btn" onclick="filterTables('Ocupada')">
                        Ocupadas
                    </button>
                    <button class="filter-btn" onclick="filterTables('Reservada')">
                        Reservadas
                    </button>
                    <button class="filter-btn" onclick="filterTables('Mantenimiento')">
                        En Mantenimiento
                    </button>
                </div>
                
                <div style="margin-top: 20px;">
                    <button class="filter-btn" onclick="openCreateTableModal()" style="background: var(--success-color); color: white;">
                        <i class="fas fa-plus"></i> Nueva Mesa
                    </button>
                    <button class="filter-btn" onclick="testModals()" style="background: var(--info-color); color: white;">
                        <i class="fas fa-bug"></i> Test Modales
                    </button>
                </div>
            </div>
            
            <!-- Mapa del Restaurante -->
            <div class="map-section">
                <h2><i class="fas fa-map-marked-alt"></i> Mapa del Restaurante</h2>
                <div class="restaurant-map">
                    <!-- Áreas del restaurante -->
                    <div class="map-area terraza">Terraza</div>
                    <div class="map-area interior">Salón Interior</div>
                    <div class="map-area bar">Bar</div>
                    
                    <!-- Marcadores de mesas -->
                    <?php foreach ($mesas as $mesa): ?>
                        <div class="table-marker <?php echo $mesa['estado']; ?>" 
                             style="left: <?php echo $mesa['x']; ?>px; top: <?php echo $mesa['y']; ?>px;"
                             data-capacity="<?php echo $mesa['capacidad']; ?> personas"
                             onclick="viewTable(<?php echo htmlspecialchars(json_encode($mesa)); ?>)">
                            <?php echo $mesa['numero']; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Leyenda -->
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-color available"></div>
                        <span>Disponible</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color occupied"></div>
                        <span>Ocupada</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color reserved"></div>
                        <span>Reservada</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color cleaning"></div>
                        <span>En Limpieza</span>
                    </div>
                </div>
            </div>
            
            <!-- Lista de Mesas -->
            <div class="tables-list">
                <h2><i class="fas fa-list"></i> Lista de Mesas</h2>
                <div class="tables-grid" id="tablesGrid">
                    <?php foreach ($mesas as $mesa): ?>
                        <div class="table-card <?php echo $mesa['estado']; ?>" 
                             data-status="<?php echo $mesa['estado']; ?>">
                            <div class="table-header">
                                <div class="table-number">Mesa <?php echo $mesa['numero']; ?></div>
                                <div class="table-status <?php echo $mesa['estado']; ?>">
                                    <?php 
                                    switch($mesa['estado']) {
                                        case 'disponible': echo 'Disponible'; break;
                                        case 'ocupada': echo 'Ocupada'; break;
                                        case 'reservada': echo 'Reservada'; break;
                                        case 'limpieza': echo 'Limpieza'; break;
                                        default: echo ucfirst($mesa['estado']);
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="table-details">
                                <div class="table-detail">
                                    <strong>Capacidad:</strong>
                                    <span><?php echo $mesa['capacidad']; ?> personas</span>
                                </div>
                                <div class="table-detail">
                                    <strong>Ubicación:</strong>
                                    <span><?php echo ucfirst($mesa['ubicacion']); ?></span>
                                </div>
                                <div class="table-detail">
                                    <strong>Estado:</strong>
                                    <span><?php echo ucfirst($mesa['estado']); ?></span>
                                </div>
                            </div>
                            
                            <div class="table-actions">
                                <button class="table-btn btn-view" onclick="viewTable(<?php echo htmlspecialchars(json_encode($mesa)); ?>)">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <button class="table-btn btn-edit" onclick="editTable(<?php echo htmlspecialchars(json_encode($mesa)); ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="table-btn" onclick="deleteTable(<?php echo $mesa['id']; ?>, <?php echo $mesa['numero']; ?>)" 
                                        style="background: var(--danger-color); color: white;">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Detalles de Mesa -->
    <div id="tableModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Detalles de Mesa</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-details">
                    <div class="detail-item">
                        <div class="detail-label">Número</div>
                        <div class="detail-value" id="modalNumber">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Capacidad</div>
                        <div class="detail-value" id="modalCapacity">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Estado</div>
                        <div class="detail-value" id="modalStatus">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Ubicación</div>
                        <div class="detail-value" id="modalLocation">-</div>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button class="modal-btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                    <button class="modal-btn btn-primary" onclick="editTableFromModal()">
                        <i class="fas fa-edit"></i> Editar Mesa
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Crear Mesa -->
    <div id="createTableModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Crear Nueva Mesa</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="crear_mesa">
                    
                    <div class="form-group">
                        <label for="numero">Número de Mesa *</label>
                        <input type="number" name="numero" id="numero" required min="1" max="999">
                    </div>
                    
                    <div class="form-group">
                        <label for="capacidad">Capacidad *</label>
                        <input type="number" name="capacidad" id="capacidad" required min="1" max="20" value="4">
                    </div>
                    
                    <div class="form-group">
                        <label for="ubicacion">Ubicación</label>
                        <select name="ubicacion" id="ubicacion">
                            <option value="interior">Interior</option>
                            <option value="terraza">Terraza</option>
                            <option value="bar">Bar</option>
                            <option value="privado">Salón Privado</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="x">Posición X (píxeles)</label>
                        <input type="number" name="x" id="x" min="0" max="1000" value="100">
                    </div>
                    
                    <div class="form-group">
                        <label for="y">Posición Y (píxeles)</label>
                        <input type="number" name="y" id="y" min="0" max="1000" value="100">
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="modal-btn btn-secondary" onclick="closeModal()">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="modal-btn btn-primary">
                            <i class="fas fa-plus"></i> Crear Mesa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal de Editar Mesa -->
    <div id="editTableModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Mesa</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="editar_mesa">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="form-group">
                        <label for="edit_numero">Número de Mesa *</label>
                        <input type="number" name="numero" id="edit_numero" required min="1" max="999">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_capacidad">Capacidad *</label>
                        <input type="number" name="capacidad" id="edit_capacidad" required min="1" max="20">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_estado">Estado</label>
                        <select name="estado" id="edit_estado">
                            <option value="Disponible">Disponible</option>
                            <option value="Ocupada">Ocupada</option>
                            <option value="Reservada">Reservada</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_ubicacion">Ubicación</label>
                        <select name="ubicacion" id="edit_ubicacion">
                            <option value="interior">Interior</option>
                            <option value="terraza">Terraza</option>
                            <option value="bar">Bar</option>
                            <option value="privado">Salón Privado</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_x">Posición X (píxeles)</label>
                        <input type="number" name="x" id="edit_x" min="0" max="1000">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_y">Posición Y (píxeles)</label>
                        <input type="number" name="y" id="edit_y" min="0" max="1000">
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="modal-btn btn-secondary" onclick="closeModal()">
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
    
    <!-- Modal de Confirmar Eliminación -->
    <div id="deleteTableModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirmar Eliminación</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar la mesa <span id="deleteTableNumber"></span>?</p>
                <p style="color: var(--danger-color); font-weight: 500;">Esta acción no se puede deshacer.</p>
                
                <form method="POST" action="" id="deleteForm">
                    <input type="hidden" name="action" value="eliminar_mesa">
                    <input type="hidden" name="id" id="delete_id">
                </form>
                
                <div class="modal-actions">
                    <button type="button" class="modal-btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="modal-btn" onclick="confirmDelete()" 
                            style="background: var(--danger-color); color: white;">
                        <i class="fas fa-trash"></i> Eliminar Mesa
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let currentTables = <?php echo json_encode($mesas); ?>;
        let currentFilter = 'todas';
        let selectedTableId = null;
        
        // Filtrar mesas por estado
        function filterTables(status) {
            currentFilter = status;
            
            // Actualizar botones activos
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Filtrar mesas en la lista
            const tableCards = document.querySelectorAll('.table-card');
            let visibleCount = 0;
            
            tableCards.forEach(card => {
                const tableStatus = card.dataset.status;
                if (status === 'todas' || tableStatus === status) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Filtrar marcadores en el mapa
            const tableMarkers = document.querySelectorAll('.table-marker');
            tableMarkers.forEach(marker => {
                const markerStatus = marker.classList.contains('available') ? 'Disponible' :
                                   marker.classList.contains('occupied') ? 'Ocupada' :
                                   marker.classList.contains('reserved') ? 'Reservada' :
                                   marker.classList.contains('cleaning') ? 'Mantenimiento' : '';
                
                if (status === 'todas' || markerStatus === status) {
                    marker.style.display = 'block';
                } else {
                    marker.style.display = 'none';
                }
            });
        }
        
        // Ver detalles de mesa
        function viewTable(table) {
            selectedTableId = table.id;
            
            document.getElementById('modalTitle').textContent = `Mesa ${table.numero}`;
            document.getElementById('modalNumber').textContent = table.numero;
            document.getElementById('modalCapacity').textContent = `${table.capacidad} personas`;
            document.getElementById('modalStatus').textContent = getStatusText(table.estado);
            document.getElementById('modalLocation').textContent = ucfirst(table.ubicacion);
            
            // Mostrar modal
            document.getElementById('tableModal').classList.add('show');
        }
        
        // Abrir modal de crear mesa
        function openCreateTableModal() {
            console.log('Abriendo modal de crear mesa');
            document.getElementById('createTableModal').classList.add('show');
        }
        
        // Editar mesa
        function editTable(table) {
            console.log('Editando mesa:', table);
            selectedTableId = table.id;
            
            // Llenar formulario de edición
            document.getElementById('edit_id').value = table.id;
            document.getElementById('edit_numero').value = table.numero;
            document.getElementById('edit_capacidad').value = table.capacidad;
            document.getElementById('edit_estado').value = table.estado;
            document.getElementById('edit_ubicacion').value = table.ubicacion;
            document.getElementById('edit_x').value = table.x || 100;
            document.getElementById('edit_y').value = table.y || 100;
            
            // Mostrar modal de edición
            document.getElementById('editTableModal').classList.add('show');
        }
        
        // Editar mesa desde modal
        function editTableFromModal() {
            if (selectedTableId) {
                const table = currentTables.find(t => t.id == selectedTableId);
                if (table) {
                    editTable(table);
                }
            }
        }
        
        // Eliminar mesa
        function deleteTable(tableId, tableNumber) {
            console.log('Eliminando mesa:', tableId, tableNumber);
            document.getElementById('delete_id').value = tableId;
            document.getElementById('deleteTableNumber').textContent = tableNumber;
            document.getElementById('deleteTableModal').classList.add('show');
        }
        
        // Confirmar eliminación
        function confirmDelete() {
            document.getElementById('deleteForm').submit();
        }
        
        // Cerrar modal
        function closeModal() {
            console.log('Cerrando modales');
            document.getElementById('tableModal').classList.remove('show');
            document.getElementById('createTableModal').classList.remove('show');
            document.getElementById('editTableModal').classList.remove('show');
            document.getElementById('deleteTableModal').classList.remove('show');
            selectedTableId = null;
        }
        
        // Utilidades
        function getStatusText(status) {
            switch(status) {
                case 'disponible': return 'Disponible';
                case 'ocupada': return 'Ocupada';
                case 'reservada': return 'Reservada';
                case 'limpieza': return 'En Limpieza';
                default: return ucfirst(status);
            }
        }
        
        function ucfirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modals = ['tableModal', 'createTableModal', 'editTableModal', 'deleteTableModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (event.target === modal) {
                    closeModal();
                }
            });
        }
        
        // Cerrar modal con ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
        
        // Función de prueba para modales
        function testModals() {
            console.log('=== TEST DE MODALES ===');
            console.log('Modal crear:', document.getElementById('createTableModal'));
            console.log('Modal editar:', document.getElementById('editTableModal'));
            console.log('Modal eliminar:', document.getElementById('deleteTableModal'));
            console.log('Modal ver:', document.getElementById('tableModal'));
            
            // Probar abrir modal de crear
            openCreateTableModal();
            setTimeout(() => {
                closeModal();
                console.log('Test completado');
            }, 2000);
        }

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Página de mesas cargada correctamente');
            console.log('Mesas disponibles:', currentTables);
        });

        // Actualizar estadísticas en tiempo real (simulado)
        setInterval(function() {
            // Aquí se implementaría la actualización en tiempo real
            // desde la base de datos
        }, 30000); // Cada 30 segundos
    </script>
</body>
</html>
