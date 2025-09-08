<?php
/**
 * Página de Galería y Menú - El Punto
 * Catálogo visual de servicios y platos
 */

session_start();
require_once '../config/config.php';
require_once '../includes/db.php';

// Función para obtener la imagen correcta del servicio
function getGalleryImage($servicio) {
    $imageMap = [
        // Entradas
        'Bruschetta' => 'https://images.unsplash.com/photo-1572441713132-51c75654db73?w=600&h=400&fit=crop&crop=center',
        'Ensalada César' => 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=600&h=400&fit=crop&crop=center',
        'Sopa del Día' => 'https://images.unsplash.com/photo-1547592180-85f173990554?w=600&h=400&fit=crop&crop=center',
        'Sopa de Tomate' => 'https://images.unsplash.com/photo-1547592180-85f173990554?w=600&h=400&fit=crop&crop=center',
        
        // Platos Principales
        'Filete de Res' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=600&h=400&fit=crop&crop=center',
        'Paella Valenciana' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=600&h=400&fit=crop&crop=center',
        'Pasta Carbonara' => 'https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?w=600&h=400&fit=crop&crop=center',
        'Pollo a la Plancha' => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=600&h=400&fit=crop&crop=center',
        'Salmón al Horno' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=600&h=400&fit=crop&crop=center',
        'Pizza Margherita' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=600&h=400&fit=crop&crop=center',
        
        // Bebidas
        'Café' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=600&h=400&fit=crop&crop=center',
        'Cerveza' => 'https://images.unsplash.com/photo-1608270586620-248524c67de9?w=600&h=400&fit=crop&crop=center',
        'Vino Tinto' => 'https://images.unsplash.com/photo-1506377247375-2b5e0d4d8d1b?w=600&h=400&fit=crop&crop=center',
        'Refresco' => 'https://images.unsplash.com/photo-1621263764928-df1444c5e859?w=600&h=400&fit=crop&crop=center',
        'Agua' => 'https://images.unsplash.com/photo-1548839140-5a7c38d67963?w=600&h=400&fit=crop&crop=center',
        
        // Postres
        'Cheesecake' => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=600&h=400&fit=crop&crop=center',
        'Flan' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&h=400&fit=crop&crop=center',
        'Tiramisú' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=600&h=400&fit=crop&crop=center',
        'Gelato de Vainilla' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&h=400&fit=crop&crop=center',
        
        // Servicios del Sistema
        'Instalación' => 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=600&h=400&fit=crop&crop=center',
        'Capacitación' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=600&h=400&fit=crop&crop=center',
        'Mantenimiento' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=600&h=400&fit=crop&crop=center',
        'Soporte Premium' => 'https://images.unsplash.com/photo-1553877522-43269d4ea984?w=600&h=400&fit=crop&crop=center'
    ];
    
    $nombre = $servicio['nombre'];
    $imagen = isset($imageMap[$nombre]) ? $imageMap[$nombre] : 'https://images.unsplash.com/photo-1551218808-94e220e084d2?w=600&h=400&fit=crop&crop=center';
    
    return $imagen;
}

// Obtener servicios desde la base de datos
$servicios = [];
try {
    $db = Database::getInstance();
    $stmt = $db->query("SELECT * FROM servicios ORDER BY categoria, nombre");
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
            'imagen' => 'pasta-carbonara.jpg',
            'disponible' => 1
        ],
        [
            'id' => 2,
            'nombre' => 'Ensalada César',
            'descripcion' => 'Lechuga romana, crutones, queso parmesano y aderezo César',
            'precio' => 8.99,
            'categoria' => 'Entradas',
            'imagen' => 'ensalada-cesar.jpg',
            'disponible' => 1
        ],
        [
            'id' => 3,
            'nombre' => 'Tiramisú',
            'descripcion' => 'Postre italiano clásico con café y mascarpone',
            'precio' => 6.99,
            'categoria' => 'Postres',
            'imagen' => 'tiramisu.jpg',
            'disponible' => 1
        ],
        [
            'id' => 4,
            'nombre' => 'Pizza Margherita',
            'descripcion' => 'Pizza tradicional con tomate, mozzarella y albahaca',
            'precio' => 14.99,
            'categoria' => 'Platos Principales',
            'imagen' => 'pizza-margherita.jpg',
            'disponible' => 1
        ],
        [
            'id' => 5,
            'nombre' => 'Sopa de Tomate',
            'descripcion' => 'Sopa cremosa de tomate con albahaca y crutones',
            'precio' => 7.99,
            'categoria' => 'Entradas',
            'imagen' => 'sopa-tomate.jpg',
            'disponible' => 1
        ],
        [
            'id' => 6,
            'nombre' => 'Gelato de Vainilla',
            'descripcion' => 'Helado artesanal de vainilla con frutas frescas',
            'precio' => 5.99,
            'categoria' => 'Postres',
            'imagen' => 'gelato-vainilla.jpg',
            'disponible' => 1
        ]
    ];
}

// Agrupar servicios por categoría
$categorias = [];
foreach ($servicios as $servicio) {
    $categoria = $servicio['categoria'];
    if (!isset($categorias[$categoria])) {
        $categorias[$categoria] = [];
    }
    $categorias[$categoria][] = $servicio;
}

// Obtener categoría seleccionada
$categoria_seleccionada = $_GET['categoria'] ?? 'todas';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería y Menú - El Punto</title>
    <meta name="description" content="Explora nuestro menú completo con fotografías de alta calidad de todos nuestros platos y servicios">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        .gallery-container {
            min-height: 100vh;
            background: var(--gray-100);
            padding: 20px;
        }
        
        .gallery-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
            border-radius: 20px;
        }
        
        .gallery-header h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .gallery-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .gallery-content {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .category-filters {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
            text-align: center;
        }
        
        .category-filters h2 {
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
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .service-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .service-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, var(--gray-200), var(--gray-300));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-600);
            position: relative;
            overflow: hidden;
        }
        
        .service-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .service-card:hover .service-image img {
            transform: scale(1.1);
        }
        
        .service-image-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
        }
        
        .service-image-placeholder i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .service-info {
            padding: 25px;
        }
        
        .service-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 10px;
        }
        
        .service-description {
            color: var(--gray-600);
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .service-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .service-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .service-category {
            background: var(--gray-100);
            color: var(--gray-600);
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .service-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-view {
            flex: 1;
            padding: 10px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-view:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-order {
            flex: 1;
            padding: 10px;
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-order:hover {
            background: var(--success-dark);
            transform: translateY(-2px);
        }
        
        .availability-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--success-color);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .unavailable-badge {
            background: var(--danger-color);
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
        
        .search-section {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }
        
        .search-section h2 {
            color: var(--gray-800);
            margin-bottom: 20px;
            font-size: 1.5rem;
            text-align: center;
        }
        
        .search-box {
            display: flex;
            gap: 15px;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .search-input {
            flex: 1;
            padding: 15px;
            border: 2px solid var(--gray-200);
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 122, 77, 0.1);
        }
        
        .search-btn {
            padding: 15px 25px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray-600);
        }
        
        .no-results i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        @media (max-width: 768px) {
            .gallery-header h1 {
                font-size: 2rem;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .filter-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .search-box {
                flex-direction: column;
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
        }
        
        .modal-image {
            width: 100%;
            height: 300px;
            background: var(--gray-200);
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-600);
        }
        
        .modal-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .modal-description {
            color: var(--gray-700);
            line-height: 1.6;
            margin-bottom: 20px;
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
    </style>
</head>
<body>
    <a href="../index.php" class="back-home">
        <i class="fas fa-arrow-left"></i>
        Volver al Inicio
    </a>
    
    <div class="gallery-container">
        <div class="gallery-header">
            <h1>Galería y Menú</h1>
            <p>Explora nuestra selección completa de servicios y platos con fotografías de alta calidad</p>
        </div>
        
        <div class="gallery-content">
            <!-- Búsqueda -->
            <div class="search-section">
                <h2><i class="fas fa-search"></i> Buscar Servicios</h2>
                <div class="search-box">
                    <input type="text" id="searchInput" class="search-input" placeholder="Buscar por nombre o descripción...">
                    <button class="search-btn" onclick="searchServices()">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </div>
            
            <!-- Filtros de Categoría -->
            <div class="category-filters">
                <h2><i class="fas fa-filter"></i> Filtrar por Categoría</h2>
                <div class="filter-buttons">
                    <button class="filter-btn <?php echo $categoria_seleccionada === 'todas' ? 'active' : ''; ?>" 
                            onclick="filterByCategory('todas')">
                        Todas las Categorías
                    </button>
                    <?php foreach (array_keys($categorias) as $categoria): ?>
                        <button class="filter-btn <?php echo $categoria_seleccionada === $categoria ? 'active' : ''; ?>" 
                                onclick="filterByCategory('<?php echo $categoria; ?>')">
                            <?php echo $categoria; ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Grid de Servicios -->
            <div class="services-grid" id="servicesGrid">
                <?php foreach ($servicios as $servicio): ?>
                    <div class="service-card" data-category="<?php echo $servicio['categoria']; ?>" 
                         data-name="<?php echo strtolower($servicio['nombre']); ?>"
                         data-description="<?php echo strtolower($servicio['descripcion']); ?>">
                        <div class="service-image">
                            <img src="<?php echo getGalleryImage($servicio); ?>" 
                                 alt="<?php echo $servicio['nombre']; ?>"
                                 onerror="this.parentElement.innerHTML='<div class=\'service-image-placeholder\'><i class=\'fas fa-utensils\'></i><p><?php echo $servicio['nombre']; ?></p></div>'">
                            
                            <div class="availability-badge <?php echo $servicio['disponible'] ? '' : 'unavailable-badge'; ?>">
                                <?php echo $servicio['disponible'] ? 'Disponible' : 'No Disponible'; ?>
                            </div>
                        </div>
                        
                        <div class="service-info">
                            <div class="service-name"><?php echo $servicio['nombre']; ?></div>
                            <div class="service-description"><?php echo $servicio['descripcion']; ?></div>
                            
                            <div class="service-meta">
                                <div class="service-price">$<?php echo number_format($servicio['precio'], 2); ?></div>
                                <div class="service-category"><?php echo $servicio['categoria']; ?></div>
                            </div>
                            
                            <div class="service-actions">
                                <button class="btn-view" onclick="viewService(<?php echo htmlspecialchars(json_encode($servicio)); ?>)">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                <?php if ($servicio['disponible']): ?>
                                    <button class="btn-order" onclick="orderService(<?php echo $servicio['id']; ?>)">
                                        <i class="fas fa-shopping-cart"></i> Ordenar
                                    </button>
                                <?php else: ?>
                                    <button class="btn-order" disabled style="opacity: 0.5; cursor: not-allowed;">
                                        <i class="fas fa-times"></i> No Disponible
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Mensaje de no resultados -->
            <div class="no-results" id="noResults" style="display: none;">
                <i class="fas fa-search"></i>
                <h3>No se encontraron resultados</h3>
                <p>Intenta con otros términos de búsqueda o cambia los filtros</p>
            </div>
        </div>
    </div>
    
    <!-- Modal de Detalles del Servicio -->
    <div id="serviceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Detalles del Servicio</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-image" id="modalImage">
                    <i class="fas fa-utensils" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
                
                <div class="modal-description" id="modalDescription">
                    Descripción del servicio...
                </div>
                
                <div class="modal-details">
                    <div class="detail-item">
                        <div class="detail-label">Precio</div>
                        <div class="detail-value" id="modalPrice">$0.00</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Categoría</div>
                        <div class="detail-value" id="modalCategory">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Estado</div>
                        <div class="detail-value" id="modalStatus">-</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">ID</div>
                        <div class="detail-value" id="modalId">-</div>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button class="modal-btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                    <button class="modal-btn btn-primary" id="modalOrderBtn" onclick="orderFromModal()">
                        <i class="fas fa-shopping-cart"></i> Ordenar Ahora
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let currentServices = <?php echo json_encode($servicios); ?>;
        let currentCategory = '<?php echo $categoria_seleccionada; ?>';
        
        // Función para obtener la URL de imagen de la galería
        function getGalleryImageUrl(nombre) {
            const imageMap = {
                // Entradas
                'Bruschetta': 'https://images.unsplash.com/photo-1572441713132-51c75654db73?w=600&h=400&fit=crop&crop=center',
                'Ensalada César': 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=600&h=400&fit=crop&crop=center',
                'Sopa del Día': 'https://images.unsplash.com/photo-1547592180-85f173990554?w=600&h=400&fit=crop&crop=center',
                'Sopa de Tomate': 'https://images.unsplash.com/photo-1547592180-85f173990554?w=600&h=400&fit=crop&crop=center',
                
                // Platos Principales
                'Filete de Res': 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=600&h=400&fit=crop&crop=center',
                'Paella Valenciana': 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=600&h=400&fit=crop&crop=center',
                'Pasta Carbonara': 'https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?w=600&h=400&fit=crop&crop=center',
                'Pollo a la Plancha': 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=600&h=400&fit=crop&crop=center',
                'Salmón al Horno': 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=600&h=400&fit=crop&crop=center',
                'Pizza Margherita': 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=600&h=400&fit=crop&crop=center',
                
                // Bebidas
                'Café': 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=600&h=400&fit=crop&crop=center',
                'Cerveza': 'https://images.unsplash.com/photo-1608270586620-248524c67de9?w=600&h=400&fit=crop&crop=center',
                'Vino Tinto': 'https://images.unsplash.com/photo-1506377247375-2b5e0d4d8d1b?w=600&h=400&fit=crop&crop=center',
                'Refresco': 'https://images.unsplash.com/photo-1621263764928-df1444c5e859?w=600&h=400&fit=crop&crop=center',
                'Agua': 'https://images.unsplash.com/photo-1548839140-5a7c38d67963?w=600&h=400&fit=crop&crop=center',
                
                // Postres
                'Cheesecake': 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=600&h=400&fit=crop&crop=center',
                'Flan': 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&h=400&fit=crop&crop=center',
                'Tiramisú': 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=600&h=400&fit=crop&crop=center',
                'Gelato de Vainilla': 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=600&h=400&fit=crop&crop=center',
                
                // Servicios del Sistema
                'Instalación': 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=600&h=400&fit=crop&crop=center',
                'Capacitación': 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=600&h=400&fit=crop&crop=center',
                'Mantenimiento': 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=600&h=400&fit=crop&crop=center',
                'Soporte Premium': 'https://images.unsplash.com/photo-1553877522-43269d4ea984?w=600&h=400&fit=crop&crop=center'
            };
            
            return imageMap[nombre] || 'https://images.unsplash.com/photo-1551218808-94e220e084d2?w=600&h=400&fit=crop&crop=center';
        }
        
        // Filtrar por categoría
        function filterByCategory(category) {
            currentCategory = category;
            
            // Actualizar botones activos
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Filtrar servicios
            const services = document.querySelectorAll('.service-card');
            let visibleCount = 0;
            
            services.forEach(service => {
                const serviceCategory = service.dataset.category;
                if (category === 'todas' || serviceCategory === category) {
                    service.style.display = 'block';
                    visibleCount++;
                } else {
                    service.style.display = 'none';
                }
            });
            
            // Mostrar/ocultar mensaje de no resultados
            const noResults = document.getElementById('noResults');
            if (visibleCount === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        }
        
        // Búsqueda de servicios
        function searchServices() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const services = document.querySelectorAll('.service-card');
            let visibleCount = 0;
            
            services.forEach(service => {
                const name = service.dataset.name;
                const description = service.dataset.description;
                const category = service.dataset.category;
                
                if (name.includes(searchTerm) || 
                    description.includes(searchTerm) || 
                    category.toLowerCase().includes(searchTerm)) {
                    service.style.display = 'block';
                    visibleCount++;
                } else {
                    service.style.display = 'none';
                }
            });
            
            // Mostrar/ocultar mensaje de no resultados
            const noResults = document.getElementById('noResults');
            if (visibleCount === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        }
        
        // Búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('input', function() {
            if (this.value.trim() === '') {
                // Si no hay término de búsqueda, aplicar filtro de categoría actual
                filterByCategory(currentCategory);
            } else {
                searchServices();
            }
        });
        
        // Ver servicio en modal
        function viewService(service) {
            document.getElementById('modalTitle').textContent = service.nombre;
            document.getElementById('modalDescription').textContent = service.descripcion;
            document.getElementById('modalPrice').textContent = `$${parseFloat(service.precio).toFixed(2)}`;
            document.getElementById('modalCategory').textContent = service.categoria;
            document.getElementById('modalStatus').textContent = service.disponible ? 'Disponible' : 'No Disponible';
            document.getElementById('modalId').textContent = service.id;
            
            // Configurar imagen del modal
            const modalImage = document.getElementById('modalImage');
            const imageUrl = getGalleryImageUrl(service.nombre);
            modalImage.innerHTML = `<img src="${imageUrl}" alt="${service.nombre}" onerror="this.parentElement.innerHTML='<i class=\'fas fa-utensils\' style=\'font-size: 3rem; opacity: 0.5;\'></i>'">`;
            
            // Configurar botón de ordenar
            const orderBtn = document.getElementById('modalOrderBtn');
            if (service.disponible) {
                orderBtn.onclick = () => orderService(service.id);
                orderBtn.disabled = false;
                orderBtn.style.opacity = '1';
            } else {
                orderBtn.disabled = true;
                orderBtn.style.opacity = '0.5';
                orderBtn.textContent = 'No Disponible';
            }
            
            // Mostrar modal
            document.getElementById('serviceModal').style.display = 'block';
        }
        
        // Cerrar modal
        function closeModal() {
            document.getElementById('serviceModal').style.display = 'none';
        }
        
        // Ordenar servicio
        function orderService(serviceId) {
            // Aquí se implementaría la lógica para agregar al carrito
            alert(`Servicio ${serviceId} agregado al carrito. Redirigiendo a la página de servicios...`);
            // Redirigir a la página de servicios para completar la orden
            window.location.href = 'servicios.php';
        }
        
        // Ordenar desde modal
        function orderFromModal() {
            const serviceId = document.getElementById('modalId').textContent;
            orderService(serviceId);
        }
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('serviceModal');
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
        
        // Inicializar filtros
        document.addEventListener('DOMContentLoaded', function() {
            if (currentCategory !== 'todas') {
                filterByCategory(currentCategory);
            }
        });
    </script>
</body>
</html>
