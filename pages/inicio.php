<?php
/**
 * Página de Inicio - El Punto
 * Dashboard principal del sistema
 */

require_once '../includes/header.php';

// Obtener estadísticas actualizadas
$stats = getStats();

// Obtener pedidos recientes
$recentOrders = getRecentOrders(5);

// Obtener servicios más populares
$popularServices = getRows("
    SELECT s.nombre, s.precio, COUNT(dp.id) as total_pedidos
    FROM servicios s
    LEFT JOIN detalle_pedidos dp ON s.id = dp.servicio_id
    WHERE s.disponible = 1
    GROUP BY s.id
    ORDER BY total_pedidos DESC
    LIMIT 5
");

// Obtener mesas con estado
$mesas = getRows("SELECT * FROM mesas ORDER BY numero");

// Obtener usuarios activos
$usuariosActivos = getRows("
    SELECT u.nombre, u.rol, COUNT(p.id) as pedidos_hoy
    FROM usuarios u
    LEFT JOIN pedidos p ON u.id = p.mesero_id AND DATE(p.hora_pedido) = CURDATE()
    WHERE u.activo = 1
    GROUP BY u.id
    ORDER BY pedidos_hoy DESC
    LIMIT 5
");

// Calcular estadísticas adicionales
$ventasSemana = getValue("
    SELECT COALESCE(SUM(total), 0) 
    FROM pedidos 
    WHERE hora_pedido >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");

$pedidosSemana = getValue("
    SELECT COUNT(*) 
    FROM pedidos 
    WHERE hora_pedido >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");

$promedioTicket = $stats['total_pedidos'] > 0 ? $stats['ventas_hoy'] / $stats['total_pedidos'] : 0;
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Dashboard</h1>
        <p>Bienvenido al sistema de gestión El Punto. Aquí puedes ver el estado general de tu restaurante.</p>
    </div>
</div>

<!-- Estadísticas principales -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $stats['total_pedidos']; ?></h3>
            <p>Total Pedidos</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon pending">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $stats['pedidos_pendientes']; ?></h3>
            <p>Pendientes</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon cooking">
            <i class="fas fa-fire"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $stats['pedidos_cocina']; ?></h3>
            <p>En Cocina</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon ready">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $stats['pedidos_listos']; ?></h3>
            <p>Listos</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon sales">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo formatCurrency($stats['ventas_hoy']); ?></h3>
            <p>Ventas Hoy</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon tables">
            <i class="fas fa-chair"></i>
        </div>
        <div class="stat-content">
            <h3><?php echo $stats['mesas_ocupadas']; ?></h3>
            <p>Mesas Ocupadas</p>
        </div>
    </div>
</div>

<!-- Contenido principal del dashboard -->
<div class="dashboard-content">
    <div class="dashboard-grid">
        <!-- Pedidos Recientes -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-history"></i> Pedidos Recientes</h3>
                <a href="pedidos.php" class="btn btn-sm btn-outline">Ver Todos</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentOrders)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No hay pedidos recientes</p>
                    </div>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($recentOrders as $order): ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <div class="order-number"><?php echo $order['numero_pedido']; ?></div>
                                    <div class="order-details">
                                        <span class="mesa">Mesa <?php echo $order['mesa_numero']; ?></span>
                                        <span class="mesero"><?php echo $order['mesero_nombre']; ?></span>
                                    </div>
                                </div>
                                <div class="order-status">
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['estado'])); ?>">
                                        <?php echo $order['estado']; ?>
                                    </span>
                                    <div class="order-time">
                                        <?php echo formatDate($order['hora_pedido']); ?>
                                    </div>
                                </div>
                                <div class="order-actions">
                                    <button class="btn btn-sm btn-outline" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Estado de Mesas -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-chair"></i> Estado de Mesas</h3>
                <a href="mesas.php" class="btn btn-sm btn-outline">Gestionar</a>
            </div>
            <div class="card-body">
                <div class="tables-grid">
                    <?php foreach ($mesas as $mesa): ?>
                        <div class="table-item table-<?php echo strtolower($mesa['estado']); ?>" 
                             data-tooltip="Mesa <?php echo $mesa['numero']; ?> - <?php echo $mesa['estado']; ?>">
                            <div class="table-number"><?php echo $mesa['numero']; ?></div>
                            <div class="table-status"><?php echo $mesa['estado']; ?></div>
                            <div class="table-capacity"><?php echo $mesa['capacidad']; ?> pax</div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="table-legend">
                    <div class="legend-item">
                        <span class="legend-color disponible"></span>
                        <span>Disponible</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color ocupada"></span>
                        <span>Ocupada</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color reservada"></span>
                        <span>Reservada</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color mantenimiento"></span>
                        <span>Mantenimiento</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Servicios Populares -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-star"></i> Servicios Populares</h3>
                <a href="servicios.php" class="btn btn-sm btn-outline">Ver Catálogo</a>
            </div>
            <div class="card-body">
                <?php if (empty($popularServices)): ?>
                    <div class="empty-state">
                        <i class="fas fa-chart-bar"></i>
                        <p>No hay datos de popularidad</p>
                    </div>
                <?php else: ?>
                    <div class="popular-services">
                        <?php foreach ($popularServices as $service): ?>
                            <div class="service-item">
                                <div class="service-info">
                                    <div class="service-name"><?php echo $service['nombre']; ?></div>
                                    <div class="service-price"><?php echo formatCurrency($service['precio']); ?></div>
                                </div>
                                <div class="service-stats">
                                    <div class="service-orders">
                                        <i class="fas fa-shopping-cart"></i>
                                        <?php echo $service['total_pedidos']; ?> pedidos
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actividad de Usuarios -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-users"></i> Actividad de Usuarios</h3>
                <a href="usuarios.php" class="btn btn-sm btn-outline">Ver Todos</a>
            </div>
            <div class="card-body">
                <?php if (empty($usuariosActivos)): ?>
                    <div class="empty-state">
                        <i class="fas fa-user-clock"></i>
                        <p>No hay actividad reciente</p>
                    </div>
                <?php else: ?>
                    <div class="users-activity">
                        <?php foreach ($usuariosActivos as $usuario): ?>
                            <div class="user-item">
                                <div class="user-info">
                                    <div class="user-name"><?php echo $usuario['nombre']; ?></div>
                                    <div class="user-role"><?php echo $usuario['rol']; ?></div>
                                </div>
                                <div class="user-stats">
                                    <div class="user-orders">
                                        <i class="fas fa-shopping-cart"></i>
                                        <?php echo $usuario['pedidos_hoy']; ?> pedidos hoy
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Estadísticas Semanales -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-chart-line"></i> Estadísticas Semanales</h3>
                <a href="reportes.php" class="btn btn-sm btn-outline">Ver Reportes</a>
            </div>
            <div class="card-body">
                <div class="weekly-stats">
                    <div class="stat-item">
                        <div class="stat-label">Ventas Semana</div>
                        <div class="stat-value"><?php echo formatCurrency($ventasSemana); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Pedidos Semana</div>
                        <div class="stat-value"><?php echo $pedidosSemana; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Promedio Ticket</div>
                        <div class="stat-value"><?php echo formatCurrency($promedioTicket); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Servicios Activos</div>
                        <div class="stat-value"><?php echo $stats['total_servicios']; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
            </div>
            <div class="card-body">
                <div class="quick-actions">
                    <a href="pedidos.php?action=new" class="quick-action-btn">
                        <i class="fas fa-plus"></i>
                        <span>Nuevo Pedido</span>
                    </a>
                    <a href="servicios.php" class="quick-action-btn">
                        <i class="fas fa-list"></i>
                        <span>Ver Servicios</span>
                    </a>
                    <a href="mesas.php" class="quick-action-btn">
                        <i class="fas fa-chair"></i>
                        <span>Gestionar Mesas</span>
                    </a>
                    <a href="historial.php" class="quick-action-btn">
                        <i class="fas fa-history"></i>
                        <span>Ver Historial</span>
                    </a>
                    <a href="reportes.php" class="quick-action-btn">
                        <i class="fas fa-chart-bar"></i>
                        <span>Generar Reporte</span>
                    </a>
                    <a href="configuracion.php" class="quick-action-btn">
                        <i class="fas fa-cog"></i>
                        <span>Configuración</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles de pedido -->
<div class="modal" id="orderDetailsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detalles del Pedido</h3>
            <button class="modal-close" onclick="closeModal('orderDetailsModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="orderDetailsContent">
            <!-- Contenido dinámico -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('orderDetailsModal')">Cerrar</button>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para el dashboard */
.dashboard-content {
    margin-top: var(--spacing-xl);
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: var(--spacing-xl);
}

.dashboard-card {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--box-shadow);
    overflow: hidden;
}

.card-header {
    background: var(--gray-100);
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    margin: 0;
    font-size: var(--font-size-lg);
    color: var(--gray-800);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.card-body {
    padding: var(--spacing-lg);
}

/* Pedidos recientes */
.orders-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-md);
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.order-item:hover {
    background: var(--gray-50);
    border-color: var(--primary-color);
}

.order-info {
    flex: 1;
}

.order-number {
    font-weight: 600;
    color: var(--gray-800);
    margin-bottom: var(--spacing-xs);
}

.order-details {
    display: flex;
    gap: var(--spacing-md);
    font-size: var(--font-size-sm);
    color: var(--gray-600);
}

.order-status {
    text-align: right;
    margin-right: var(--spacing-md);
}

.status-badge {
    display: inline-block;
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--border-radius-sm);
    font-size: var(--font-size-xs);
    font-weight: 500;
    text-transform: uppercase;
}

.status-pendiente { background: var(--warning-color); color: var(--white); }
.status-en-cocina { background: var(--danger-color); color: var(--white); }
.status-listo { background: var(--success-color); color: var(--white); }
.status-entregado { background: var(--info-color); color: var(--white); }
.status-cancelado { background: var(--gray-600); color: var(--white); }

.order-time {
    font-size: var(--font-size-xs);
    color: var(--gray-500);
    margin-top: var(--spacing-xs);
}

/* Estado de mesas */
.tables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-lg);
}

.table-item {
    text-align: center;
    padding: var(--spacing-sm);
    border-radius: var(--border-radius);
    border: 2px solid var(--gray-200);
    transition: var(--transition);
    cursor: pointer;
}

.table-disponible {
    background: var(--success-color);
    color: var(--white);
    border-color: var(--success-color);
}

.table-ocupada {
    background: var(--danger-color);
    color: var(--white);
    border-color: var(--danger-color);
}

.table-reservada {
    background: var(--warning-color);
    color: var(--white);
    border-color: var(--warning-color);
}

.table-mantenimiento {
    background: var(--gray-600);
    color: var(--white);
    border-color: var(--gray-600);
}

.table-number {
    font-weight: 600;
    font-size: var(--font-size-lg);
}

.table-status {
    font-size: var(--font-size-xs);
    margin-top: var(--spacing-xs);
}

.table-capacity {
    font-size: var(--font-size-xs);
    opacity: 0.8;
    margin-top: var(--spacing-xs);
}

.table-legend {
    display: flex;
    justify-content: center;
    gap: var(--spacing-lg);
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-size: var(--font-size-sm);
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 50%;
}

.legend-color.disponible { background: var(--success-color); }
.legend-color.ocupada { background: var(--danger-color); }
.legend-color.reservada { background: var(--warning-color); }
.legend-color.mantenimiento { background: var(--gray-600); }

/* Servicios populares */
.popular-services {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.service-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-md);
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius);
}

.service-name {
    font-weight: 500;
    color: var(--gray-800);
    margin-bottom: var(--spacing-xs);
}

.service-price {
    font-size: var(--font-size-sm);
    color: var(--primary-color);
    font-weight: 600;
}

.service-orders {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-size: var(--font-size-sm);
    color: var(--gray-600);
}

/* Actividad de usuarios */
.users-activity {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.user-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-md);
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius);
}

.user-name {
    font-weight: 500;
    color: var(--gray-800);
    margin-bottom: var(--spacing-xs);
}

.user-role {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
}

.user-orders {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-size: var(--font-size-sm);
    color: var(--gray-600);
}

/* Estadísticas semanales */
.weekly-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-lg);
}

.stat-item {
    text-align: center;
    padding: var(--spacing-md);
    background: var(--gray-50);
    border-radius: var(--border-radius);
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
    margin-bottom: var(--spacing-xs);
}

.stat-value {
    font-size: var(--font-size-xl);
    font-weight: 700;
    color: var(--primary-color);
}

/* Acciones rápidas */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-md);
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-lg);
    background: var(--gray-50);
    border: 2px solid var(--gray-200);
    border-radius: var(--border-radius);
    text-decoration: none;
    color: var(--gray-700);
    transition: var(--transition);
}

.quick-action-btn:hover {
    background: var(--primary-color);
    color: var(--white);
    border-color: var(--primary-color);
    transform: translateY(-2px);
}

.quick-action-btn i {
    font-size: var(--font-size-2xl);
}

.quick-action-btn span {
    font-weight: 500;
    text-align: center;
}

/* Estados vacíos */
.empty-state {
    text-align: center;
    padding: var(--spacing-2xl);
    color: var(--gray-500);
}

.empty-state i {
    font-size: var(--font-size-4xl);
    margin-bottom: var(--spacing-md);
    opacity: 0.5;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    
    .order-item {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-sm);
    }
    
    .order-status {
        text-align: left;
        margin-right: 0;
    }
    
    .tables-grid {
        grid-template-columns: repeat(4, 1fr);
    }
    
    .weekly-stats {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Función para ver detalles de pedido
function viewOrderDetails(orderId) {
    showLoading();
    
    fetch(`../includes/get_order_details.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                document.getElementById('orderDetailsContent').innerHTML = data.html;
                document.getElementById('orderDetailsModal').style.display = 'flex';
            } else {
                showMessage('Error al cargar los detalles del pedido', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            showMessage('Error de conexión', 'error');
        });
}

// Actualizar datos en tiempo real
setInterval(() => {
    updateDashboardStats();
}, 30000); // Cada 30 segundos

function updateDashboardStats() {
    fetch('../includes/get_dashboard_stats.php')
        .then(response => response.json())
        .then(data => {
            // Actualizar estadísticas
            updateStatCards(data.stats);
            
            // Actualizar pedidos recientes si hay cambios
            if (data.recentOrders) {
                updateRecentOrders(data.recentOrders);
            }
            
            // Actualizar estado de mesas
            if (data.mesas) {
                updateMesasStatus(data.mesas);
            }
        })
        .catch(error => {
            console.error('Error actualizando dashboard:', error);
        });
}

function updateStatCards(stats) {
    const statCards = document.querySelectorAll('.stat-card h3');
    if (statCards.length >= 6) {
        statCards[0].textContent = stats.total_pedidos || 0;
        statCards[1].textContent = stats.pedidos_pendientes || 0;
        statCards[2].textContent = stats.pedidos_cocina || 0;
        statCards[3].textContent = stats.pedidos_listos || 0;
        statCards[4].textContent = formatCurrency(stats.ventas_hoy || 0);
        statCards[5].textContent = stats.mesas_ocupadas || 0;
    }
}

function updateRecentOrders(orders) {
    const ordersList = document.querySelector('.orders-list');
    if (!ordersList) return;
    
    ordersList.innerHTML = '';
    
    orders.forEach(order => {
        const orderItem = document.createElement('div');
        orderItem.className = 'order-item';
        orderItem.innerHTML = `
            <div class="order-info">
                <div class="order-number">${order.numero_pedido}</div>
                <div class="order-details">
                    <span class="mesa">Mesa ${order.mesa_numero}</span>
                    <span class="mesero">${order.mesero_nombre}</span>
                </div>
            </div>
            <div class="order-status">
                <span class="status-badge status-${order.estado.toLowerCase().replace(' ', '-')}">
                    ${order.estado}
                </span>
                <div class="order-time">
                    ${formatDate(order.hora_pedido)}
                </div>
            </div>
            <div class="order-actions">
                <button class="btn btn-sm btn-outline" onclick="viewOrderDetails(${order.id})">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        `;
        ordersList.appendChild(orderItem);
    });
}

function updateMesasStatus(mesas) {
    mesas.forEach(mesa => {
        const tableItem = document.querySelector(`[data-tooltip*="Mesa ${mesa.numero}"]`);
        if (tableItem) {
            tableItem.className = `table-item table-${mesa.estado.toLowerCase()}`;
            tableItem.setAttribute('data-tooltip', `Mesa ${mesa.numero} - ${mesa.estado}`);
        }
    });
}

// Inicializar tooltips para las mesas
document.addEventListener('DOMContentLoaded', function() {
    const tableItems = document.querySelectorAll('.table-item');
    tableItems.forEach(item => {
        item.addEventListener('click', function() {
            const mesaInfo = this.getAttribute('data-tooltip');
            showMessage(`Información: ${mesaInfo}`, 'info');
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
