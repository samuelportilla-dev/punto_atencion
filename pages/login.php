<?php
/**
 * Página de Login - El Punto
 * Sistema de autenticación
 */

session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: inicio.php");
    exit();
}

// Procesar login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor completa todos los campos';
    } else {
        // Simular autenticación (en producción usar base de datos)
        if ($email === 'admin@elpunto.com' && $password === 'admin123') {
            $_SESSION['user_id'] = 1;
            $_SESSION['user_name'] = 'Administrador';
            $_SESSION['user_role'] = 'Administrador';
            header("Location: inicio.php");
            exit();
        } elseif ($email === 'mesero@elpunto.com' && $password === 'mesero123') {
            $_SESSION['user_id'] = 2;
            $_SESSION['user_name'] = 'Mesero';
            $_SESSION['user_role'] = 'Mesero';
            header("Location: inicio.php");
            exit();
        } else {
            $error = 'Credenciales inválidas';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - El Punto</title>
    <meta name="description" content="Iniciar sesión en el sistema El Punto">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #007a4d 0%, #005a3a 100%);
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .login-logo {
            margin-bottom: 30px;
        }
        
        .login-logo i {
            font-size: 3rem;
            color: #007a4d;
            margin-bottom: 15px;
        }
        
        .login-logo h1 {
            font-size: 1.8rem;
            color: #333;
            margin: 0;
        }
        
        .login-logo p {
            color: #666;
            margin: 10px 0 0 0;
        }
        
        .login-form {
            text-align: left;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #007a4d;
            box-shadow: 0 0 0 3px rgba(0, 122, 77, 0.1);
        }
        
        .login-btn {
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
        
        .login-btn:hover {
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
        
        .login-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e1e5e9;
        }
        
        .login-footer a {
            color: #007a4d;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .demo-credentials {
            background: #e8f5e8;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #155724;
        }
        
        .demo-credentials h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        
        .demo-credentials p {
            margin: 5px 0;
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
        
        @media (max-width: 480px) {
            .login-card {
                padding: 30px 20px;
            }
            
            .login-logo h1 {
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
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <i class="fas fa-utensils"></i>
                <h1>El Punto</h1>
                <p>Sistema de Intercomunicación</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="demo-credentials">
                <h4><i class="fas fa-info-circle"></i> Credenciales de Demo</h4>
                <p><strong>Administrador:</strong> admin@elpunto.com / admin123</p>
                <p><strong>Mesero:</strong> mesero@elpunto.com / mesero123</p>
            </div>
            
            <form class="login-form" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="tu@email.com">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Tu contraseña">
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    Iniciar Sesión
                </button>
            </form>
            
            <div class="login-footer">
                <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
                <p><a href="forgot-password.php">¿Olvidaste tu contraseña?</a></p>
            </div>
        </div>
    </div>
    
    <script>
        // Enfocar el primer campo al cargar
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
        
        // Validación en tiempo real
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
        
        document.getElementById('password').addEventListener('blur', function() {
            const password = this.value;
            if (password && password.length < 6) {
                this.style.borderColor = '#dc3545';
                showFieldError(this, 'Mínimo 6 caracteres');
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
    </script>
</body>
</html>
