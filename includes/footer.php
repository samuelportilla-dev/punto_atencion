            </div> <!-- .page-content -->
        </div> <!-- .container -->
    </main> <!-- .main-content -->

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo $siteName; ?></h3>
                    <p>Sistema de intercomunicación entre cocina y meseros para una gestión eficiente y profesional del servicio en restaurantes.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Enlaces Rápidos</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo $basePath; ?>index.php">Inicio</a></li>
                        <li><a href="<?php echo $basePath; ?>pages/nosotros.php">Nosotros</a></li>
                        <li><a href="<?php echo $basePath; ?>pages/servicios.php">Servicios</a></li>
                        <li><a href="<?php echo $basePath; ?>pages/contacto.php">Contacto</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Sistema</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo $basePath; ?>pages/pedidos.php">Pedidos</a></li>
                        <li><a href="<?php echo $basePath; ?>pages/mesas.php">Mesas</a></li>
                        <li><a href="<?php echo $basePath; ?>pages/historial.php">Historial</a></li>
                        <li><a href="<?php echo $basePath; ?>pages/galeria.php">Galería</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contacto</h4>
                    <div class="contact-info">
                        <p><i class="fas fa-map-marker-alt"></i> Calle Principal #123, Ciudad</p>
                        <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                        <p><i class="fas fa-envelope"></i> info@elpunto.com</p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo $siteName; ?>. Todos los derechos reservados.</p>
                    <div class="footer-bottom-links">
                        <a href="#">Política de Privacidad</a>
                        <a href="#">Términos de Servicio</a>
                        <a href="#">Cookies</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modales globales -->
    <div id="globalModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalContent"></div>
        </div>
    </div>

    <!-- Loading spinner -->
    <div id="loadingSpinner" class="loading-spinner">
        <div class="spinner"></div>
        <p>Cargando...</p>
    </div>

    <!-- JavaScript -->
    <script src="<?php echo $basePath; ?>assets/js/main.js"></script>
    
    <!-- Auto-refresh para páginas que lo necesiten -->
    <?php if (isset($autoRefresh) && $autoRefresh): ?>
    <script>
        // Auto-refresh cada 30 segundos
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
    <?php endif; ?>

    <!-- Scripts específicos de la página -->
    <?php if (isset($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <script src="<?php echo $basePath . $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
