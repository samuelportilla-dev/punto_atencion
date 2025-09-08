<?php
/**
 * Página de Registro - El Punto
 * Sistema de registro de usuarios
 */

session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: inicio.php");
    exit();
}

// Procesar registro
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $rol = $_POST['rol'] ?? '';
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password) || empty($rol)) {
        $error = 'Por favor completa todos los campos';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido';
    } else {
        // Simular registro exitoso (en producción guardar en base de datos)
        $success = 'Registro exitoso! Ahora puedes iniciar sesión.';
        
        // Limpiar formulario
        $nombre = $email = $password = $confirm_password = $rol = '';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - El Punto</title>
    <meta name="description" content="Registrarse en el sistema El Punto">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #007a4d 0%, #005a3a 100%);
            padding: 20px;
        }
        
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        
        .register-logo {
            margin-bottom: 30px;
        }
        
        .register-logo i {
            font-size: 3rem;
            color: #007a4d;
            margin-bottom: 15px;
        }
        
        .register-logo h1 {
            font-size: 1.8rem;
            color: #333;
            margin: 0;
        }
        
        .register-logo p {
            color: #666;
            margin: 10px 0 0 0;
        }
        
        .register-form {
            text-align: left;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007a4d;
            box-shadow: 0 0 0 3px rgba(0, 122, 77, 0.1);
        }
        
        .register-btn {
            width: 100%;
            padding: 14px;
            background: #007a4d;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .register-btn:hover {
            background: #005a3a;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .register-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e1e5e9;
        }
        
        .register-footer a {
            color: #007a4d;
            text-decoration: none;
            font-weight: 500;
        }
        
        .register-footer a:hover {
            text-decoration: underline;
        }
        
        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-home:hover {
            transform: translateX(-5px);
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }
        
        .strength-weak { color: #dc3545; }
        .strength-medium { color: #ffc107; }
        .strength-strong { color: #28a745; }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .register-card {
                padding: 30px 20px;
            }
            
            .register-logo h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-home">
        <i class="fas fa-arrow-left"></i>
        Volver al Inicio
    </a>
    
    <div class="register-container">
        <div class="register-card">
            <div class="register-logo">
                <i class="fas fa-user-plus"></i>
                <h1>Crear Cuenta</h1>
                <p>Únete al sistema El Punto</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form class="register-form" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo</label>
                        <input type="text" id="nombre" name="nombre" required 
                               value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>"
                               placeholder="Tu nombre completo">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               placeholder="tu@email.com">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Mínimo 6 caracteres">
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Repite tu contraseña">
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label for="rol">Rol en el Sistema</label>
                    <select id="rol" name="rol" required>
                        <option value="">Selecciona tu rol</option>
                        <option value="Mesero" <?php echo ($_POST['rol'] ?? '') === 'Mesero' ? 'selected' : ''; ?>>Mesero</option>
                        <option value="Cocinero" <?php echo ($_POST['rol'] ?? '') === 'Cocinero' ? 'selected' : ''; ?>>Cocinero</option>
                        <option value="Administrador" <?php echo ($_POST['rol'] ?? '') === 'Administrador' ? 'selected' : ''; ?>>Administrador</option>
                    </select>
                </div>
                
                <button type="submit" class="register-btn">
                    <i class="fas fa-user-plus"></i>
                    Crear Cuenta
                </button>
            </form>
            
            <div class="register-footer">
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            </div>
        </div>
    </div>
    
    <script>
        // Enfocar el primer campo al cargar
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('nombre').focus();
        });
        
        // Validación de contraseña en tiempo real
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.textContent = '';
                strengthDiv.className = 'password-strength';
                return;
            }
            
            let strength = 0;
            let message = '';
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            if (strength <= 2) {
                message = 'Débil';
                strengthDiv.className = 'password-strength strength-weak';
            } else if (strength <= 3) {
                message = 'Media';
                strengthDiv.className = 'password-strength strength-medium';
            } else {
                message = 'Fuerte';
                strengthDiv.className = 'password-strength strength-strong';
            }
            
            strengthDiv.textContent = `Fortaleza: ${message}`;
        });
        
        // Validación de confirmación de contraseña
        document.getElementById('confirm_password').addEventListener('blur', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#dc3545';
                showFieldError(this, 'Las contraseñas no coinciden');
            } else {
                this.style.borderColor = '#007a4d';
                hideFieldError(this);
            }
        });
        
        // Validación de email
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value.trim();
            if (email && !isValidEmail(email)) {
                this.style.borderColor = '#dc3545';
                showFieldError(this, 'Email inválido');
            } else {
                this.style.borderColor = '#007a4d';
                hideFieldError(this);
            }
        });
        
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        function showFieldError(field, message) {
            hideFieldError(field);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.style.color = '#dc3545';
            errorDiv.style.fontSize = '12px';
            errorDiv.style.marginTop = '5px';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
        }
        
        function hideFieldError(field) {
            const existingError = field.parentNode.querySelector('.field-error');
            if (existingError) {
                existingError.remove();
            }
        }
        
        // Validación del formulario antes de enviar
        document.querySelector('.register-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const email = document.getElementById('email').value;
            
            let hasErrors = false;
            
            // Validar contraseñas
            if (password !== confirmPassword) {
                showFieldError(document.getElementById('confirm_password'), 'Las contraseñas no coinciden');
                hasErrors = true;
            }
            
            // Validar email
            if (email && !isValidEmail(email)) {
                showFieldError(document.getElementById('email'), 'Email inválido');
                hasErrors = true;
            }
            
            // Validar longitud de contraseña
            if (password.length < 6) {
                showFieldError(document.getElementById('password'), 'La contraseña debe tener al menos 6 caracteres');
                hasErrors = true;
            }
            
            if (hasErrors) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
