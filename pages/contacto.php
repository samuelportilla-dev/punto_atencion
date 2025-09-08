<?php
/**
 * Página de Contacto - El Punto
 * Formulario de contacto y información
 */

session_start();

// Procesar formulario de contacto
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? htmlspecialchars(trim($_POST['nombre'])) : '';
    $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $asunto = isset($_POST['asunto']) ? htmlspecialchars(trim($_POST['asunto'])) : '';
    $mensaje = isset($_POST['mensaje']) ? htmlspecialchars(trim($_POST['mensaje'])) : '';
    
    // Validaciones
    $errors = [];
    
    if (empty($nombre)) {
        $errors[] = 'El nombre es obligatorio';
    }
    
    if (empty($email)) {
        $errors[] = 'El email es obligatorio';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El email no es válido';
    }
    
    if (empty($asunto)) {
        $errors[] = 'El asunto es obligatorio';
    }
    
    if (empty($mensaje)) {
        $errors[] = 'El mensaje es obligatorio';
    }
    
    if (empty($errors)) {
        // Guardar en archivo
        $contacto_data = date('Y-m-d H:i:s') . " | Nombre: $nombre | Email: $email | Asunto: $asunto | Mensaje: $mensaje\n";
        
        if (file_put_contents('../contactos_el_punto.txt', $contacto_data, FILE_APPEND | LOCK_EX)) {
            $success_message = '¡Gracias por tu mensaje! Nos pondremos en contacto contigo pronto.';
            // Limpiar campos después del envío exitoso
            $nombre = $email = $asunto = $mensaje = '';
        } else {
            $error_message = 'Error al enviar el mensaje. Por favor, inténtalo de nuevo.';
        }
    } else {
        $error_message = implode(', ', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - El Punto</title>
    <meta name="description" content="Contacta con el equipo de El Punto para cualquier consulta o soporte">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        .contact-container {
            min-height: 100vh;
            background: var(--gray-100);
            padding: 20px;
        }
        
        .contact-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
            border-radius: 20px;
        }
        
        .contact-header h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .contact-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .contact-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .contact-form-section {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .contact-form-section h2 {
            color: var(--gray-800);
            margin-bottom: 30px;
            font-size: 1.8rem;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--gray-700);
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--gray-200);
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 122, 77, 0.1);
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .submit-btn {
            width: 100%;
            padding: 15px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .submit-btn:disabled {
            background: var(--gray-400);
            cursor: not-allowed;
            transform: none;
        }
        
        .contact-info-section {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .contact-info-section h2 {
            color: var(--gray-800);
            margin-bottom: 30px;
            font-size: 1.8rem;
        }
        
        .contact-method {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .contact-method:last-child {
            border-bottom: none;
        }
        
        .contact-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        
        .contact-details h3 {
            color: var(--gray-800);
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
        
        .contact-details p {
            color: var(--gray-600);
            margin: 0;
        }
        
        .contact-details a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .contact-details a:hover {
            text-decoration: underline;
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
        
        .map-section {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .map-section h2 {
            color: var(--gray-800);
            margin-bottom: 30px;
            font-size: 1.8rem;
        }
        
        .map-placeholder {
            background: var(--gray-200);
            height: 300px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-600);
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        
        .business-hours {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
        }
        
        .business-hours h2 {
            color: var(--gray-800);
            margin-bottom: 30px;
            font-size: 1.8rem;
            text-align: center;
        }
        
        .hours-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .day-item {
            text-align: center;
            padding: 20px;
            background: var(--gray-50);
            border-radius: 10px;
        }
        
        .day-name {
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 10px;
        }
        
        .day-hours {
            color: var(--gray-600);
        }
        
        @media (max-width: 768px) {
            .contact-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .contact-header h1 {
                font-size: 2rem;
            }
            
            .contact-form-section,
            .contact-info-section {
                padding: 30px 20px;
            }
            
            .back-home {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 20px;
                color: var(--primary-color);
            }
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-home">
        <i class="fas fa-arrow-left"></i>
        Volver al Inicio
    </a>
    
    <div class="contact-container">
        <div class="contact-header">
            <h1>Restaurante Gran Muralla China</h1>
            <p>¿Tienes alguna pregunta sobre nuestros servicios o quieres hacer una reserva? Estamos aquí para atenderte con la mejor comida china de Cúcuta.</p>
        </div>
        
        <div class="contact-content">
            <!-- Formulario de Contacto -->
            <div class="contact-form-section">
                <h2><i class="fas fa-envelope"></i> Envíanos un Mensaje</h2>
                
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
                
                <form method="POST" action="#contacto">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo *</label>
                        <input type="text" id="nombre" name="nombre" required 
                               value="<?php echo isset($nombre) ? $nombre : ''; ?>"
                               placeholder="Tu nombre completo">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo isset($email) ? $email : ''; ?>"
                               placeholder="tu@email.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="asunto">Asunto *</label>
                        <select id="asunto" name="asunto" required>
                            <option value="">Selecciona un asunto</option>
                            <option value="Reserva de Mesa" <?php echo (isset($asunto) && $asunto === 'Reserva de Mesa') ? 'selected' : ''; ?>>Reserva de Mesa</option>
                            <option value="Pedido a Domicilio" <?php echo (isset($asunto) && $asunto === 'Pedido a Domicilio') ? 'selected' : ''; ?>>Pedido a Domicilio</option>
                            <option value="Eventos Especiales" <?php echo (isset($asunto) && $asunto === 'Eventos Especiales') ? 'selected' : ''; ?>>Eventos Especiales</option>
                            <option value="Información del Menú" <?php echo (isset($asunto) && $asunto === 'Información del Menú') ? 'selected' : ''; ?>>Información del Menú</option>
                            <option value="Quejas y Sugerencias" <?php echo (isset($asunto) && $asunto === 'Quejas y Sugerencias') ? 'selected' : ''; ?>>Quejas y Sugerencias</option>
                            <option value="Otro" <?php echo (isset($asunto) && $asunto === 'Otro') ? 'selected' : ''; ?>>Otro</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="mensaje">Mensaje *</label>
                        <textarea id="mensaje" name="mensaje" required 
                                  placeholder="Cuéntanos más sobre tu consulta, reserva o pedido..."><?php echo isset($mensaje) ? $mensaje : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="submit-btn" id="submitBtn" onclick="disableButton()">
                        <i class="fas fa-paper-plane"></i>
                        Enviar Mensaje
                    </button>
                </form>
            </div>
            
            <!-- Información de Contacto -->
            <div class="contact-info-section">
                <h2><i class="fas fa-info-circle"></i> Información de Contacto</h2>
                
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-details">
                        <h3>Restaurante Gran Muralla China</h3>
                        <p>Calle 15 #3-33, Centro<br>Cúcuta, Norte de Santander<br>Colombia</p>
                    </div>
                </div>
                
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-details">
                        <h3>Teléfono</h3>
                        <p><a href="tel:+5775712345">+57 (7) 571-2345</a></p>
                        <p><a href="tel:+573001234567">+57 300 123-4567</a></p>
                    </div>
                </div>
                
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-details">
                        <h3>Email</h3>
                        <p><a href="mailto:info@granmurallachina.com">info@granmurallachina.com</a></p>
                        <p><a href="mailto:reservas@granmurallachina.com">reservas@granmurallachina.com</a></p>
                    </div>
                </div>
                
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="contact-details">
                        <h3>Horario de Atención</h3>
                        <p>Lunes a Domingo: 11:00 AM - 10:00 PM</p>
                        <p>Abierto todos los días del año</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mapa -->
        <div class="map-section">
            <h2><i class="fas fa-map"></i> Nuestra Ubicación</h2>
            <div class="map-placeholder">
                <div>
                    <i class="fas fa-map-marked-alt" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                    <p>Mapa interactivo de ubicación</p>
                    <p style="font-size: 0.9rem; margin-top: 10px;">En una implementación real, aquí se mostraría un mapa de Google Maps o similar</p>
                </div>
            </div>
            <p>Estamos ubicados en el centro histórico de Cúcuta, cerca del Parque Santander y la Catedral, fácilmente accesible desde cualquier punto de la ciudad.</p>
        </div>
        
        <!-- Horarios de Negocio -->
        <div class="business-hours">
            <h2><i class="fas fa-calendar-alt"></i> Horarios de Negocio</h2>
            <div class="hours-grid">
                <div class="day-item">
                    <div class="day-name">Lunes</div>
                    <div class="day-hours">11:00 AM - 10:00 PM</div>
                </div>
                <div class="day-item">
                    <div class="day-name">Martes</div>
                    <div class="day-hours">11:00 AM - 10:00 PM</div>
                </div>
                <div class="day-item">
                    <div class="day-name">Miércoles</div>
                    <div class="day-hours">11:00 AM - 10:00 PM</div>
                </div>
                <div class="day-item">
                    <div class="day-name">Jueves</div>
                    <div class="day-hours">11:00 AM - 10:00 PM</div>
                </div>
                <div class="day-item">
                    <div class="day-name">Viernes</div>
                    <div class="day-hours">11:00 AM - 10:00 PM</div>
                </div>
                <div class="day-item">
                    <div class="day-name">Sábado</div>
                    <div class="day-hours">11:00 AM - 10:00 PM</div>
                </div>
                <div class="day-item">
                    <div class="day-name">Domingo</div>
                    <div class="day-hours">11:00 AM - 10:00 PM</div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Deshabilitar botón después del envío
        function disableButton() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            
            // Re-habilitar después de 5 segundos (en caso de error)
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Mensaje';
            }, 5000);
        }
        
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
        
        // Enfocar el primer campo al cargar
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('nombre').focus();
        });
    </script>
</body>
</html>
