<?php
/**
 * Página de Servicios - El Punto
 * Catálogo de servicios con precios y sistema de pedidos
 */

require_once '../includes/header.php';

// Función para obtener la imagen correcta del servicio
function getServiceImage($servicio) {
    $imageMap = [
        // Entradas
        'Bruschetta' => 'https://images.unsplash.com/photo-1572441713132-51c75654db73?w=400&h=300&fit=crop&crop=center',
        'Ensalada César' => 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=400&h=300&fit=crop&crop=center',
        'Sopa del Día' => 'https://images.unsplash.com/photo-1547592180-85f173990554?w=400&h=300&fit=crop&crop=center',
        
        // Platos Principales
        'Filete de Res' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=400&h=300&fit=crop&crop=center',
        'Paella Valenciana' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400&h=300&fit=crop&crop=center',
        'Pasta Carbonara' => 'https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?w=400&h=300&fit=crop&crop=center',
        'Pollo a la Plancha' => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=400&h=300&fit=crop&crop=center',
        'Salmón al Horno' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=400&h=300&fit=crop&crop=center',
        
        // Bebidas
        'Café' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=400&h=300&fit=crop&crop=center',
        'Cerveza' => 'https://images.unsplash.com/photo-1608270586620-248524c67de9?w=400&h=300&fit=crop&crop=center',
        'Vino Tinto' => 'https://images.unsplash.com/photo-1506377247375-2b5e0d4d8d1b?w=400&h=300&fit=crop&crop=center',
        'Refresco' => 'https://images.unsplash.com/photo-1621263764928-df1444c5e859?w=400&h=300&fit=crop&crop=center',
        'Agua' => 'https://images.unsplash.com/photo-1548839140-5a7c38d67963?w=400&h=300&fit=crop&crop=center',
        
        // Postres
        'Cheesecake' => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=400&h=300&fit=crop&crop=center',
        'Flan' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&h=300&fit=crop&crop=center',
        'Tiramisú' => 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=400&h=300&fit=crop&crop=center',
        
        // Servicios del Sistema
        'Instalación' => 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=400&h=300&fit=crop&crop=center',
        'Capacitación' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=400&h=300&fit=crop&crop=center',
        'Mantenimiento' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=400&h=300&fit=crop&crop=center',
        'Soporte Premium' => 'https://images.unsplash.com/photo-1553877522-43269d4ea984?w=400&h=300&fit=crop&crop=center'
    ];
    
    $nombre = $servicio['nombre'];
    $imagen = isset($imageMap[$nombre]) ? $imageMap[$nombre] : 'https://images.unsplash.com/photo-1551218808-94e220e084d2?w=400&h=300&fit=crop&crop=center';
    
    return $imagen;
}

// Obtener categoría filtrada
$categoria = isset($_GET['categoria']) ? sanitizeInput($_GET['categoria']) : null;

// Obtener servicios de la base de datos
$servicios = getServicesByCategory($categoria);

// Obtener categorías disponibles
$categorias = SERVICIO_CATEGORIAS;

// Obtener mesas disponibles para el formulario de pedido
$mesas = getAvailableTables();

// Obtener usuarios activos (meseros)
$usuarios = getActiveUsers();
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Catálogo de Servicios</h1>
        <p>Explora nuestra amplia selección de servicios y productos para tu restaurante</p>
    </div>
</div>

<!-- Filtros de categoría -->
<div class="filters-section">
    <div class="filters-container">
        <div class="filter-buttons">
            <a href="?categoria=" class="filter-btn <?php echo !$categoria ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i>
                Todos
            </a>
            <?php foreach ($categorias as $cat => $nombre): ?>
                <a href="?categoria=<?php echo urlencode($cat); ?>" 
                   class="filter-btn <?php echo $categoria === $cat ? 'active' : ''; ?>">
                    <i class="fas fa-<?php echo $cat === 'Entrada' ? 'leaf' : ($cat === 'Plato Principal' ? 'utensils' : ($cat === 'Postre' ? 'birthday-cake' : ($cat === 'Bebida' ? 'glass-martini' : 'cog'))); ?>"></i>
                    <?php echo $nombre; ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="search-box">
            <input type="text" id="searchServices" placeholder="Buscar servicios..." class="search-input">
            <i class="fas fa-search search-icon"></i>
        </div>
    </div>
</div>

<!-- Grid de servicios -->
<div class="services-grid" id="servicesGrid">
    <?php if (empty($servicios)): ?>
        <div class="no-results">
            <i class="fas fa-search"></i>
            <h3>No se encontraron servicios</h3>
            <p>Intenta con otros filtros o términos de búsqueda</p>
        </div>
    <?php else: ?>
        <?php foreach ($servicios as $servicio): ?>
            <div class="service-card" data-categoria="<?php echo $servicio['categoria']; ?>" data-nombre="<?php echo strtolower($servicio['nombre']); ?>">
                <div class="service-image">
                    <img src="<?php echo getServiceImage($servicio); ?>" alt="<?php echo htmlspecialchars($servicio['nombre']); ?>" class="service-img">
                    <div class="service-overlay">
                        <button class="btn btn-primary btn-sm" onclick="addToCart(<?php echo $servicio['id']; ?>)">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                        <button class="btn btn-outline btn-sm" onclick="viewDetails(<?php echo $servicio['id']; ?>)">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                    </div>
                </div>
                
                <div class="service-content">
                    <div class="service-header">
                        <h3><?php echo $servicio['nombre']; ?></h3>
                        <span class="service-price"><?php echo formatCurrency($servicio['precio']); ?></span>
                    </div>
                    
                    <p class="service-description"><?php echo $servicio['descripcion']; ?></p>
                    
                    <div class="service-meta">
                        <span class="service-category">
                            <i class="fas fa-tag"></i>
                            <?php echo $servicio['categoria']; ?>
                        </span>
                        <?php if ($servicio['categoria'] !== 'Sistema'): ?>
                            <span class="service-time">
                                <i class="fas fa-clock"></i>
                                <?php echo $servicio['tiempo_preparacion']; ?> min
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="service-actions">
                        <button class="btn btn-primary" onclick="addToCart(<?php echo $servicio['id']; ?>)">
                            <i class="fas fa-shopping-cart"></i>
                            Agregar al Pedido
                        </button>
                        <button class="btn btn-outline" onclick="viewDetails(<?php echo $servicio['id']; ?>)">
                            <i class="fas fa-info-circle"></i>
                            Detalles
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Carrito flotante -->
<div class="floating-cart" id="floatingCart">
    <div class="cart-header">
        <h3><i class="fas fa-shopping-cart"></i> Carrito de Pedido</h3>
        <button class="cart-toggle" onclick="toggleCart()">
            <i class="fas fa-chevron-up"></i>
        </button>
    </div>
    
    <div class="cart-content" id="cartContent">
        <div class="cart-items" id="cartItems">
            <!-- Los items se cargan dinámicamente -->
        </div>
        
        <div class="cart-summary">
            <div class="cart-total">
                <span>Subtotal:</span>
                <span id="cartSubtotal">$0.00</span>
            </div>
            <div class="cart-tax">
                <span>Impuesto (10%):</span>
                <span id="cartTax">$0.00</span>
            </div>
            <div class="cart-grand-total">
                <span>Total:</span>
                <span id="cartTotal">$0.00</span>
            </div>
        </div>
        
        <div class="cart-actions">
            <button class="btn btn-secondary" onclick="clearCart()">
                <i class="fas fa-trash"></i>
                Limpiar
            </button>
            <button class="btn btn-primary" onclick="showOrderModal()">
                <i class="fas fa-check"></i>
                Realizar Pedido
            </button>
        </div>
    </div>
</div>

<!-- Modal de detalles del servicio -->
<div class="modal" id="serviceModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="serviceModalTitle">Detalles del Servicio</h3>
            <button class="modal-close" onclick="closeModal('serviceModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="serviceModalBody">
            <!-- Contenido dinámico -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('serviceModal')">Cerrar</button>
            <button class="btn btn-primary" id="addToCartFromModal">
                <i class="fas fa-shopping-cart"></i>
                Agregar al Carrito
            </button>
        </div>
    </div>
</div>

<!-- Modal de pedido -->
<div class="modal" id="orderModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Realizar Pedido</h3>
            <button class="modal-close" onclick="closeModal('orderModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="orderForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="orderMesa">Mesa *</label>
                        <select id="orderMesa" name="mesa_id" required>
                            <option value="">Seleccionar mesa</option>
                            <?php foreach ($mesas as $mesa): ?>
                                <option value="<?php echo $mesa['id']; ?>">
                                    Mesa <?php echo $mesa['numero']; ?> (<?php echo $mesa['capacidad']; ?> personas)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php if ($isLoggedIn && $_SESSION['user_role'] === 'Mesero'): ?>
                        <div class="form-group">
                            <label for="orderMesero">Mesero</label>
                            <select id="orderMesero" name="mesero_id">
                                <option value="<?php echo $_SESSION['user_id']; ?>">
                                    <?php echo $_SESSION['user_name']; ?>
                                </option>
                            </select>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label for="orderMesero">Mesero *</label>
                            <select id="orderMesero" name="mesero_id" required>
                                <option value="">Seleccionar mesero</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <?php if ($usuario['rol'] === 'Mesero'): ?>
                                        <option value="<?php echo $usuario['id']; ?>">
                                            <?php echo $usuario['nombre']; ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="orderNotes">Notas adicionales</label>
                    <textarea id="orderNotes" name="notas" rows="3" 
                              placeholder="Especificaciones especiales, alergias, preferencias..."></textarea>
                </div>
                
                <div class="order-summary">
                    <h4>Resumen del Pedido</h4>
                    <div id="orderSummary">
                        <!-- Se llena dinámicamente -->
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('orderModal')">Cancelar</button>
            <button class="btn btn-primary" onclick="submitOrder()">
                <i class="fas fa-paper-plane"></i>
                Enviar Pedido
            </button>
        </div>
    </div>
</div>

<!-- Estilos para las imágenes reales -->
<style>
.service-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    background: #f8f9fa;
}

.service-img:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}

.service-img:not([src]) {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 0.9rem;
}

.service-detail-img {
    width: 100%;
    height: 300px;
    object-fit: cover;
    border-radius: 8px;
}

.service-image {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
}
</style>

<!-- Script específico para servicios -->
<script>
// Variables globales
let cart = [];
let currentService = null;

// Mapeo de imágenes reales
const serviceImages = {
    'ensalada-cesar': 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=400&h=300&fit=crop&crop=center',
    'sopa-dia': 'https://images.unsplash.com/photo-1547592180-85f173990554?w=400&h=300&fit=crop&crop=center',
    'bruschetta': 'https://images.unsplash.com/photo-1572441713132-51c75654db73?w=400&h=300&fit=crop&crop=center',
    'pasta-carbonara': 'https://images.unsplash.com/photo-1621996346565-e3dbc353d2e5?w=400&h=300&fit=crop&crop=center',
    'filete-res': 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=400&h=300&fit=crop&crop=center',
    'paella': 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400&h=300&fit=crop&crop=center',
    'pollo-plancha': 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=400&h=300&fit=crop&crop=center',
    'salmon-horno': 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=400&h=300&fit=crop&crop=center',
    'tiramisu': 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=400&h=300&fit=crop&crop=center',
    'flan': 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=400&h=300&fit=crop&crop=center',
    'cheesecake': 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?w=400&h=300&fit=crop&crop=center',
    'agua': 'https://images.unsplash.com/photo-1548839140-5b7c4a0a8a0a?w=400&h=300&fit=crop&crop=center',
    'refresco': 'https://images.unsplash.com/photo-1581636625402-29b2a704ef13?w=400&h=300&fit=crop&crop=center',
    'cerveza': 'https://images.unsplash.com/photo-1608270586620-248524c67de9?w=400&h=300&fit=crop&crop=center',
    'vino-tinto': 'https://images.unsplash.com/photo-1506377247377-2a5b3b417ebb?w=400&h=300&fit=crop&crop=center',
    'cafe': 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=400&h=300&fit=crop&crop=center',
    'instalacion': 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=400&h=300&fit=crop&crop=center',
    'capacitacion': 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=400&h=300&fit=crop&crop=center',
    'mantenimiento': 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=400&h=300&fit=crop&crop=center',
    'premium': 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=400&h=300&fit=crop&crop=center',
    'default': 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=400&h=300&fit=crop&crop=center'
};

// Función para obtener URL de imagen
function getServiceImageUrl(imageName) {
    return serviceImages[imageName] || serviceImages['default'];
}

// Función para agregar al carrito
function addToCart(serviceId) {
    const service = <?php echo json_encode($servicios); ?>.find(s => s.id == serviceId);
    if (!service) return;
    
    const existingItem = cart.find(item => item.id == serviceId);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({
            id: service.id,
            nombre: service.nombre,
            precio: parseFloat(service.precio),
            cantidad: 1
        });
    }
    
    updateCart();
    showMessage('Servicio agregado al carrito', 'success');
}

// Función para actualizar carrito
function updateCart() {
    const cartItems = document.getElementById('cartItems');
    const cartSubtotal = document.getElementById('cartSubtotal');
    const cartTax = document.getElementById('cartTax');
    const cartTotal = document.getElementById('cartTotal');
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="empty-cart">No hay items en el carrito</p>';
        cartSubtotal.textContent = '$0.00';
        cartTax.textContent = '$0.00';
        cartTotal.textContent = '$0.00';
        return;
    }
    
    let subtotal = 0;
    cartItems.innerHTML = '';
    
    cart.forEach(item => {
        const itemTotal = item.precio * item.cantidad;
        subtotal += itemTotal;
        
        const itemElement = document.createElement('div');
        itemElement.className = 'cart-item';
        itemElement.innerHTML = `
            <div class="cart-item-info">
                <h4>${item.nombre}</h4>
                <p>${formatCurrency(item.precio)} x ${item.cantidad}</p>
            </div>
            <div class="cart-item-actions">
                <button onclick="updateQuantity(${item.id}, -1)" class="btn-quantity">-</button>
                <span>${item.cantidad}</span>
                <button onclick="updateQuantity(${item.id}, 1)" class="btn-quantity">+</button>
                <button onclick="removeFromCart(${item.id})" class="btn-remove">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        cartItems.appendChild(itemElement);
    });
    
    const tax = subtotal * 0.10;
    const total = subtotal + tax;
    
    cartSubtotal.textContent = formatCurrency(subtotal);
    cartTax.textContent = formatCurrency(tax);
    cartTotal.textContent = formatCurrency(total);
}

// Función para actualizar cantidad
function updateQuantity(serviceId, change) {
    const item = cart.find(item => item.id == serviceId);
    if (!item) return;
    
    item.cantidad += change;
    if (item.cantidad <= 0) {
        removeFromCart(serviceId);
    } else {
        updateCart();
    }
}

// Función para remover del carrito
function removeFromCart(serviceId) {
    cart = cart.filter(item => item.id != serviceId);
    updateCart();
    showMessage('Servicio removido del carrito', 'info');
}

// Función para limpiar carrito
function clearCart() {
    cart = [];
    updateCart();
    showMessage('Carrito limpiado', 'info');
}

// Función para mostrar modal de pedido
function showOrderModal() {
    if (cart.length === 0) {
        showMessage('El carrito está vacío', 'warning');
        return;
    }
    
    // Actualizar resumen del pedido
    const orderSummary = document.getElementById('orderSummary');
    let summaryHTML = '';
    let subtotal = 0;
    
    cart.forEach(item => {
        const itemTotal = item.precio * item.cantidad;
        subtotal += itemTotal;
        summaryHTML += `
            <div class="summary-item">
                <span>${item.nombre} x ${item.cantidad}</span>
                <span>${formatCurrency(itemTotal)}</span>
            </div>
        `;
    });
    
    const tax = subtotal * 0.10;
    const total = subtotal + tax;
    
    summaryHTML += `
        <div class="summary-total">
            <span>Subtotal:</span>
            <span>${formatCurrency(subtotal)}</span>
        </div>
        <div class="summary-total">
            <span>Impuesto (10%):</span>
            <span>${formatCurrency(tax)}</span>
        </div>
        <div class="summary-grand-total">
            <span>Total:</span>
            <span>${formatCurrency(total)}</span>
        </div>
    `;
    
    orderSummary.innerHTML = summaryHTML;
    
    document.getElementById('orderModal').style.display = 'flex';
}

// Función para enviar pedido
function submitOrder() {
    const form = document.getElementById('orderForm');
    if (!validateForm('orderForm')) {
        showMessage('Por favor completa todos los campos requeridos', 'error');
        return;
    }
    
    const formData = new FormData(form);
    formData.append('items', JSON.stringify(cart));
    formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    showLoading();
    
    fetch('../includes/create_order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showMessage('Pedido enviado exitosamente', 'success');
            closeModal('orderModal');
            clearCart();
            // Redirigir al historial después de 2 segundos
            setTimeout(() => {
                window.location.href = 'historial.php';
            }, 2000);
        } else {
            showMessage(data.message || 'Error al enviar el pedido', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showMessage('Error al enviar el pedido', 'error');
    });
}

// Función para ver detalles del servicio
function viewDetails(serviceId) {
    const service = <?php echo json_encode($servicios); ?>.find(s => s.id == serviceId);
    if (!service) return;
    
    currentService = service;
    
    document.getElementById('serviceModalTitle').textContent = service.nombre;
    document.getElementById('serviceModalBody').innerHTML = `
        <div class="service-details">
            <div class="service-detail-image">
                <img src="${getServiceImageUrl(service.imagen)}" 
                     alt="${service.nombre}"
                     class="service-detail-img"
                     onerror="this.src='${getServiceImageUrl('default')}'">
            </div>
            <div class="service-detail-info">
                <h3>${service.nombre}</h3>
                <p class="service-description">${service.descripcion}</p>
                <div class="service-meta-details">
                    <div class="meta-item">
                        <i class="fas fa-tag"></i>
                        <span><strong>Categoría:</strong> ${service.categoria}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-dollar-sign"></i>
                        <span><strong>Precio:</strong> ${formatCurrency(service.precio)}</span>
                    </div>
                    ${service.categoria !== 'Sistema' ? `
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span><strong>Tiempo de preparación:</strong> ${service.tiempo_preparacion} minutos</span>
                        </div>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('addToCartFromModal').onclick = () => {
        addToCart(service.id);
        closeModal('serviceModal');
    };
    
    document.getElementById('serviceModal').style.display = 'flex';
}

// Función para alternar carrito
function toggleCart() {
    const cartContent = document.getElementById('cartContent');
    const cartToggle = document.querySelector('.cart-toggle i');
    
    if (cartContent.style.display === 'none') {
        cartContent.style.display = 'block';
        cartToggle.className = 'fas fa-chevron-down';
    } else {
        cartContent.style.display = 'none';
        cartToggle.className = 'fas fa-chevron-up';
    }
}

// Función para buscar servicios
function searchServices() {
    const searchTerm = document.getElementById('searchServices').value.toLowerCase();
    const serviceCards = document.querySelectorAll('.service-card');
    
    serviceCards.forEach(card => {
        const nombre = card.getAttribute('data-nombre');
        const categoria = card.getAttribute('data-categoria');
        
        if (nombre.includes(searchTerm) || categoria.toLowerCase().includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar carrito
    updateCart();
    
    // Buscador
    document.getElementById('searchServices').addEventListener('input', searchServices);
    
    // Cerrar modales al hacer clic fuera
    window.onclick = function(event) {
        const serviceModal = document.getElementById('serviceModal');
        const orderModal = document.getElementById('orderModal');
        
        if (event.target === serviceModal) {
            closeModal('serviceModal');
        }
        if (event.target === orderModal) {
            closeModal('orderModal');
        }
    };
});
</script>

<?php require_once '../includes/footer.php'; ?>
