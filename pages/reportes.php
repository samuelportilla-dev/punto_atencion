<?php
/**
 * Página de Reportes - El Punto
 * Reportes y estadísticas del sistema
 */

session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Obtener parámetros de filtro
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$tipo_reporte = $_GET['tipo_reporte'] ?? 'ventas';

// Obtener datos según el tipo de reporte
$datos_reporte = [];
$titulo_reporte = '';

switch ($tipo_reporte) {
    case 'ventas':
        $titulo_reporte = 'Reporte de Ventas';
        $datos_reporte = getRows("
            SELECT DATE(hora_pedido) as fecha, 
                   COUNT(*) as total_pedidos, 
                   SUM(total) as total_ventas
            FROM pedidos 
            WHERE DATE(hora_pedido) BETWEEN ? AND ?
            GROUP BY DATE(hora_pedido)
            ORDER BY fecha DESC
        ", [$fecha_inicio, $fecha_fin]);
        break;
        
    case 'servicios':
        $titulo_reporte = 'Reporte de Servicios';
        $datos_reporte = getRows("
            SELECT s.nombre, 
                   COUNT(dp.id) as total_pedidos, 
                   SUM(dp.cantidad) as total_cantidad,
                   SUM(dp.subtotal) as total_ventas
            FROM servicios s
            LEFT JOIN detalle_pedidos dp ON s.id = dp.servicio_id
            LEFT JOIN pedidos p ON dp.pedido_id = p.id
            WHERE DATE(p.hora_pedido) BETWEEN ? AND ?
            GROUP BY s.id
            ORDER BY total_ventas DESC
        ", [$fecha_inicio, $fecha_fin]);
        break;
        
    case 'meseros':
        $titulo_reporte = 'Reporte de Meseros';
        $datos_reporte = getRows("
            SELECT u.nombre, 
                   COUNT(p.id) as total_pedidos, 
                   SUM(p.total) as total_ventas
            FROM usuarios u
            LEFT JOIN pedidos p ON u.id = p.mesero_id
            WHERE DATE(p.hora_pedido) BETWEEN ? AND ?
            GROUP BY u.id
            ORDER BY total_ventas DESC
        ", [$fecha_inicio, $fecha_fin]);
        break;
}

// Calcular totales
$total_ventas = array_sum(array_column($datos_reporte, 'total_ventas'));
$total_pedidos = array_sum(array_column($datos_reporte, 'total_pedidos'));

require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Reportes y Estadísticas</h1>
        <p>Análisis detallado del rendimiento del restaurante</p>
    </div>
</div>

<div class="reports-container">
    <!-- Filtros -->
    <div class="filters-section">
        <h2>Filtros de Reporte</h2>
        <form method="GET" action="" class="filters-form">
            <div class="filter-group">
                <label for="tipo_reporte">Tipo de Reporte</label>
                <select name="tipo_reporte" id="tipo_reporte">
                    <option value="ventas" <?php echo $tipo_reporte === 'ventas' ? 'selected' : ''; ?>>Ventas por Día</option>
                    <option value="servicios" <?php echo $tipo_reporte === 'servicios' ? 'selected' : ''; ?>>Servicios Populares</option>
                    <option value="meseros" <?php echo $tipo_reporte === 'meseros' ? 'selected' : ''; ?>>Rendimiento de Meseros</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="fecha_inicio">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
            </div>
            
            <div class="filter-group">
                <label for="fecha_fin">Fecha Fin</label>
                <input type="date" name="fecha_fin" id="fecha_fin" value="<?php echo $fecha_fin; ?>">
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Generar Reporte
                </button>
                <button type="button" class="btn btn-secondary" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </button>
            </div>
        </form>
    </div>
    
    <!-- Resumen -->
    <div class="summary-section">
        <h2>Resumen del Período</h2>
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="summary-content">
                    <h3><?php echo formatCurrency($total_ventas); ?></h3>
                    <p>Total Ventas</p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="summary-content">
                    <h3><?php echo $total_pedidos; ?></h3>
                    <p>Total Pedidos</p>
                </div>
            </div>
            
            <div class="summary-card">
                <div class="summary-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="summary-content">
                    <h3><?php echo $total_pedidos > 0 ? formatCurrency($total_ventas / $total_pedidos) : '$0.00'; ?></h3>
                    <p>Promedio por Pedido</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabla de datos -->
    <div class="data-section">
        <h2><?php echo $titulo_reporte; ?></h2>
        <div class="data-table-container">
            <?php if (empty($datos_reporte)): ?>
                <div class="empty-state">
                    <i class="fas fa-chart-bar"></i>
                    <p>No hay datos para el período seleccionado</p>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <?php if ($tipo_reporte === 'ventas'): ?>
                                <th>Fecha</th>
                                <th>Total Pedidos</th>
                                <th>Total Ventas</th>
                            <?php elseif ($tipo_reporte === 'servicios'): ?>
                                <th>Servicio</th>
                                <th>Total Pedidos</th>
                                <th>Cantidad Vendida</th>
                                <th>Total Ventas</th>
                            <?php elseif ($tipo_reporte === 'meseros'): ?>
                                <th>Mesero</th>
                                <th>Total Pedidos</th>
                                <th>Total Ventas</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($datos_reporte as $fila): ?>
                            <tr>
                                <?php if ($tipo_reporte === 'ventas'): ?>
                                    <td><?php echo date('d/m/Y', strtotime($fila['fecha'])); ?></td>
                                    <td><?php echo $fila['total_pedidos']; ?></td>
                                    <td><?php echo formatCurrency($fila['total_ventas']); ?></td>
                                <?php elseif ($tipo_reporte === 'servicios'): ?>
                                    <td><?php echo $fila['nombre']; ?></td>
                                    <td><?php echo $fila['total_pedidos']; ?></td>
                                    <td><?php echo $fila['total_cantidad']; ?></td>
                                    <td><?php echo formatCurrency($fila['total_ventas']); ?></td>
                                <?php elseif ($tipo_reporte === 'meseros'): ?>
                                    <td><?php echo $fila['nombre']; ?></td>
                                    <td><?php echo $fila['total_pedidos']; ?></td>
                                    <td><?php echo formatCurrency($fila['total_ventas']); ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.reports-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.filters-section {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.filters-section h2 {
    margin-bottom: 1.5rem;
    color: var(--gray-800);
}

.filters-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--gray-700);
}

.filter-group input,
.filter-group select {
    padding: 0.75rem;
    border: 2px solid var(--gray-200);
    border-radius: 5px;
    font-size: 1rem;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: var(--primary-color);
}

.filter-actions {
    display: flex;
    gap: 1rem;
    align-items: end;
}

.summary-section {
    margin-bottom: 2rem;
}

.summary-section h2 {
    margin-bottom: 1.5rem;
    color: var(--gray-800);
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.summary-card {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.summary-icon {
    width: 60px;
    height: 60px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.summary-content h3 {
    margin: 0 0 0.5rem 0;
    color: var(--gray-800);
    font-size: 1.5rem;
}

.summary-content p {
    margin: 0;
    color: var(--gray-600);
}

.data-section {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.data-section h2 {
    margin-bottom: 1.5rem;
    color: var(--gray-800);
}

.data-table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.data-table th,
.data-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--gray-200);
}

.data-table th {
    background: var(--gray-50);
    font-weight: 600;
    color: var(--gray-800);
}

.data-table tr:hover {
    background: var(--gray-50);
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--gray-600);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

@media (max-width: 768px) {
    .filters-form {
        grid-template-columns: 1fr;
    }
    
    .filter-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .summary-cards {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function exportToExcel() {
    // Crear un enlace de descarga para exportar los datos
    const table = document.querySelector('.data-table');
    if (!table) {
        alert('No hay datos para exportar');
        return;
    }
    
    // Crear CSV
    let csv = '';
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        const rowData = Array.from(cells).map(cell => `"${cell.textContent.trim()}"`);
        csv += rowData.join(',') + '\n';
    });
    
    // Descargar archivo
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'reporte_<?php echo $tipo_reporte; ?>_<?php echo date('Y-m-d'); ?>.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>

<?php require_once '../includes/footer.php'; ?>
