<?php
/**
 * Página Principal - El Punto
 * Página informativa sobre la empresa y sus servicios
 */

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-container">
        <div class="hero-content">
            <h1 class="hero-title">El Punto de Atención</h1>
            <p class="hero-description">
                Plataforma web integral que optimiza la gestión empresarial mediante soluciones digitales innovadoras. Conecta, gestiona y potencia tu negocio hacia el futuro digital.
            </p>
            <div class="hero-buttons">
                <a href="pages/login.php" class="btn btn-primary btn-large">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </a>
                <a href="pages/register.php" class="btn btn-outline btn-large">
                    <i class="fas fa-user-plus"></i> Registrarse
                </a>
            </div>
        </div>
        <div class="hero-image">
            <div class="hero-visual">
                <i class="fas fa-laptop-code"></i>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <div class="section-header">
            <h2>Características Principales</h2>
            <p>Descubre cómo El Punto de Atención revoluciona la gestión empresarial</p>
        </div>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <h3>Gestión de Proyectos</h3>
                <p>Sistema completo para planificar, ejecutar y monitorear proyectos con herramientas colaborativas avanzadas.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>CRM Inteligente</h3>
                <p>Gestión integral de clientes con automatización de ventas y marketing personalizado.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Análisis y Reportes</h3>
                <p>Dashboards interactivos, análisis predictivo y reportes automatizados para decisiones inteligentes.</p>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about">
    <div class="container">
        <div class="about-content">
            <div class="about-text">
                <h2>¿Qué es El Punto de Atención?</h2>
                <p>
                    El Punto de Atención es una plataforma web integral diseñada específicamente para empresas que buscan 
                    optimizar su gestión digital. Nuestro sistema elimina las barreras tradicionales de los procesos empresariales, 
                    reduciendo tiempos y mejorando la experiencia tanto interna como del cliente.
                </p>
                <p>
                    Con años de experiencia en desarrollo de soluciones tecnológicas, hemos creado una plataforma 
                    intuitiva que se adapta a las necesidades específicas de cada organización, desde startups 
                    innovadoras hasta grandes corporaciones.
                </p>
                <div class="about-stats">
                    <div class="stat">
                        <span class="stat-number">1,200+</span>
                        <span class="stat-label">Empresas</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">150,000+</span>
                        <span class="stat-label">Usuarios Activos</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">99.5%</span>
                        <span class="stat-label">Uptime</span>
                    </div>
                </div>
            </div>
            <div class="about-image">
                <div class="about-visual">
                    <i class="fas fa-cogs"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="benefits">
    <div class="container">
        <div class="section-header">
            <h2>Beneficios de la Plataforma</h2>
            <p>Descubre por qué las mejores empresas eligen El Punto de Atención</p>
        </div>
        
        <div class="benefits-grid">
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Eficiencia</h3>
                <p>Automatiza procesos y reduce tiempos operativos significativamente.</p>
            </div>
            
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>Precisión</h3>
                <p>Elimina errores manuales y mejora la calidad de los datos.</p>
            </div>
            
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Colaboración</h3>
                <p>Mejora la coordinación entre equipos y departamentos.</p>
            </div>
            
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3>Inteligencia</h3>
                <p>Proporciona insights valiosos con IA y análisis avanzados.</p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works">
    <div class="container">
        <div class="section-header">
            <h2>¿Cómo Funciona?</h2>
            <p>Proceso simple y eficiente en solo 3 pasos</p>
        </div>
        
        <div class="steps-grid">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h3>Configurar Proyecto</h3>
                <p>Define los objetivos, recursos y cronograma del proyecto con herramientas intuitivas.</p>
            </div>
            
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <h3>Automatizar Procesos</h3>
                <p>La plataforma ejecuta workflows automáticamente y optimiza los recursos.</p>
            </div>
            
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Analizar Resultados</h3>
                <p>Obtén insights en tiempo real y toma decisiones basadas en datos.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta">
    <div class="container">
        <div class="cta-content">
            <h2>¿Listo para Transformar tu Empresa?</h2>
            <p>Únete a miles de empresas que ya confían en El Punto de Atención para optimizar su gestión digital.</p>
            <div class="cta-buttons">
                <a href="pages/register.php" class="btn btn-primary btn-large">
                    <i class="fas fa-rocket"></i> Comenzar Ahora
                </a>
                <a href="pages/contacto.php" class="btn btn-outline btn-large">
                    <i class="fas fa-phone"></i> Contactar Ventas
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials">
    <div class="container">
        <div class="section-header">
            <h2>Lo que Dicen Nuestros Clientes</h2>
            <p>Testimonios de restaurantes que han transformado su operación</p>
        </div>
        
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"El Punto de Atención ha transformado completamente nuestra operación. La productividad aumentó un 60% y los errores se redujeron un 85%."</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-info">
                        <h4>María González</h4>
                        <span>CEO, TechCorp Solutions</span>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"La integración entre departamentos es ahora perfecta. Nuestros clientes notan la diferencia en la calidad del servicio."</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-info">
                        <h4>Carlos Rodríguez</h4>
                        <span>CTO, InnovateLab</span>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <p>"Implementamos la plataforma en 2 meses y ya vemos mejoras significativas en todos nuestros KPIs empresariales."</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-info">
                        <h4>Ana Martínez</h4>
                        <span>Directora de Operaciones, GrowthCo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
