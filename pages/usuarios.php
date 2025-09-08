<?php
/**
 * Página de Usuarios - El Punto
 * Gestión de usuarios del sistema
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

// Obtener usuarios
$usuarios = getRows("SELECT * FROM usuarios ORDER BY nombre");

// Procesar acciones
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $db = Database::getInstance();
        
        if ($_POST['action'] === 'crear_usuario') {
            $nombre = $_POST['nombre'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $rol = $_POST['rol'] ?? 'Mesero';
            
            if (empty($nombre) || empty($email) || empty($password)) {
                $error_message = 'Todos los campos son obligatorios';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->query("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)", [$nombre, $email, $password_hash, $rol]);
                $success_message = "Usuario {$nombre} creado exitosamente";
            }
            
        } elseif ($_POST['action'] === 'editar_usuario') {
            $id = $_POST['id'] ?? '';
            $nombre = $_POST['nombre'] ?? '';
            $email = $_POST['email'] ?? '';
            $rol = $_POST['rol'] ?? 'Mesero';
            $activo = isset($_POST['activo']) ? 1 : 0;
            
            if (empty($id) || empty($nombre) || empty($email)) {
                $error_message = 'Datos incompletos para editar el usuario';
            } else {
                $stmt = $db->query("UPDATE usuarios SET nombre = ?, email = ?, rol = ?, activo = ? WHERE id = ?", [$nombre, $email, $rol, $activo, $id]);
                $success_message = "Usuario {$nombre} actualizado exitosamente";
            }
        }
        
    } catch (Exception $e) {
        $error_message = 'Error: ' . $e->getMessage();
    }
}

require_once '../includes/header.php';
?>

<div class="page-header">
    <div class="page-header-content">
        <h1>Gestión de Usuarios</h1>
        <p>Administra los usuarios del sistema y sus permisos</p>
    </div>
</div>

<div class="users-container">
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
    
    <!-- Acciones -->
    <div class="actions-section">
        <button class="btn btn-primary" onclick="openCreateUserModal()">
            <i class="fas fa-plus"></i> Nuevo Usuario
        </button>
    </div>
    
    <!-- Lista de usuarios -->
    <div class="users-list">
        <div class="users-grid">
            <?php foreach ($usuarios as $usuario): ?>
                <div class="user-card">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-info">
                        <h3><?php echo $usuario['nombre']; ?></h3>
                        <p><?php echo $usuario['email']; ?></p>
                        <span class="user-role"><?php echo $usuario['rol']; ?></span>
                        <span class="user-status <?php echo $usuario['activo'] ? 'active' : 'inactive'; ?>">
                            <?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>
                        </span>
                    </div>
                    <div class="user-actions">
                        <button class="btn btn-sm btn-outline" onclick="editUser(<?php echo htmlspecialchars(json_encode($usuario)); ?>)">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal de Crear Usuario -->
<div id="createUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Crear Nuevo Usuario</h2>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="">
                <input type="hidden" name="action" value="crear_usuario">
                
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" name="nombre" id="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" name="email" id="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <input type="password" name="password" id="password" required>
                </div>
                
                <div class="form-group">
                    <label for="rol">Rol</label>
                    <select name="rol" id="rol">
                        <option value="Mesero">Mesero</option>
                        <option value="Cocinero">Cocinero</option>
                        <option value="Administrador">Administrador</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Editar Usuario -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Editar Usuario</h2>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="">
                <input type="hidden" name="action" value="editar_usuario">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label for="edit_nombre">Nombre *</label>
                    <input type="text" name="nombre" id="edit_nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email *</label>
                    <input type="email" name="email" id="edit_email" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_rol">Rol</label>
                    <select name="rol" id="edit_rol">
                        <option value="Mesero">Mesero</option>
                        <option value="Cocinero">Cocinero</option>
                        <option value="Administrador">Administrador</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="activo" id="edit_activo"> Usuario Activo
                    </label>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.users-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.actions-section {
    margin-bottom: 2rem;
}

.users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.user-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.user-avatar {
    width: 60px;
    height: 60px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin: 0 auto;
}

.user-info {
    text-align: center;
}

.user-info h3 {
    margin: 0 0 0.5rem 0;
    color: var(--gray-800);
}

.user-info p {
    margin: 0 0 0.5rem 0;
    color: var(--gray-600);
}

.user-role {
    display: inline-block;
    background: var(--primary-color);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    margin-right: 0.5rem;
}

.user-status {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

.user-status.active {
    background: #d4edda;
    color: #155724;
}

.user-status.inactive {
    background: #f8d7da;
    color: #721c24;
}

.user-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.success-message, .error-message {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    text-align: center;
}

.success-message {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.8);
}

.modal-content {
    background: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 10px;
    width: 90%;
    max-width: 500px;
}

.modal-header {
    background: var(--primary-color);
    color: white;
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 10px 10px 0 0;
}

.close-modal {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
}

.modal-body {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid var(--gray-200);
    border-radius: 5px;
    font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary-color);
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
}
</style>

<script>
function openCreateUserModal() {
    document.getElementById('createUserModal').style.display = 'block';
}

function editUser(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_nombre').value = user.nombre;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_rol').value = user.rol;
    document.getElementById('edit_activo').checked = user.activo == 1;
    document.getElementById('editUserModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('createUserModal').style.display = 'none';
    document.getElementById('editUserModal').style.display = 'none';
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
