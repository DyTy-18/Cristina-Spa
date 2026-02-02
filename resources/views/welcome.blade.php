@extends('layouts.app')

@section('title', 'Cristina Spa - Belleza, Estilo & Bienestar | Desde 2006')

@section('content')
    <!-- Campaign Popup Modal -->
    <div id="campaign-popup" class="campaign-popup">
        <div class="campaign-overlay"></div>
        <div class="campaign-modal">
            <button class="campaign-close" aria-label="Cerrar">×</button>
            <a href="#contacto" class="campaign-link">
                <img src="https://deeppink-pheasant-693696.hostingersite.com/recursos/img-CAMPA%C3%91A%20NUEVO%20LOOK%20PARA%20CADA%20MOMENTO.png"
                    alt="Campaña Nuevo Look Para Cada Momento - Cristina Spa" class="campaign-image">
            </a>
        </div>
    </div>

    <!-- Hero Section Premium -->
    <section id="inicio" class="hero">
        <div class="hero-content">
            <div class="hero-badge">
                <div class="badge-icon"></div>
                <span>Desde 2006 | La Paz, Bolivia</span>
            </div>
            <h2 class="hero-subtitle">Bienvenido a</h2>
            <h1 class="hero-title">Cristina Spa</h1>
            <p class="hero-description">
                Un santuario de belleza y bienestar donde la excelencia
                y la exclusividad se encuentran para realzar tu mejor versión.
            </p>
            <div class="hero-buttons">
                <a href="#contacto" class="cta-button">Reservar Cita</a>
                <a href="#servicios" class="cta-button-outline">Nuestros Servicios</a>
            </div>
        </div>
        <div class="scroll-indicator">
            <span></span>
        </div>
    </section>

    <!-- About Section -->
    <section class="about">
        <div class="container">
            <div class="about-content">
                <div class="about-text-content">
                    <span class="section-label">Nuestra Historia</span>
                    <h2 class="section-title">Excelencia en Belleza & Bienestar</h2>
                    <p class="about-text">
                        Con casi dos décadas de experiencia, Cristina Spa se ha consolidado
                        como referente en la industria de la belleza en Bolivia. Nuestro
                        equipo de profesionales altamente capacitados combina técnica,
                        creatividad y pasión para ofrecer experiencias únicas.
                    </p>
                    <p class="about-text">
                        Trabajamos con productos de gama alta como L'Oréal e INOA,
                        garantizando resultados excepcionales que superan las expectativas
                        de nuestros clientes más exigentes.
                    </p>
                    <div class="about-stats">
                        <div class="stat-item">
                            <span class="stat-number">18+</span>
                            <span class="stat-label">Años de Experiencia</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">6</span>
                            <span class="stat-label">Sucursales</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">50+</span>
                            <span class="stat-label">Profesionales</span>
                        </div>
                    </div>
                </div>
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                        alt="Cristina Spa Interior">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="servicios" class="services">
        <div class="container">
            <div class="services-intro">
                <span class="section-label">Lo Que Ofrecemos</span>
                <h2 class="section-title section-title-center">Nuestros Servicios</h2>
                <p>Descubre nuestra amplia gama de servicios diseñados para realzar tu belleza natural y proporcionarte
                    momentos de relajación absoluta.</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1560066984-138dadb4c035?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80"
                            alt="Peluquería">
                    </div>
                    <h3>Peluquería</h3>
                    <p>Cortes de tendencia y colorimetría profesional con productos premium.</p>
                    <ul class="service-features">
                        <li>Cortes personalizados</li>
                        <li>Colorimetría L'Oréal</li>
                        <li>Balayage & Matizados</li>
                        <li>Tratamientos capilares</li>
                    </ul>
                </div>
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1544161515-4ab6ce6db874?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80"
                            alt="Spa & Bienestar">
                    </div>
                    <h3>Spa & Bienestar</h3>
                    <p>Experiencias relajantes que renuevan cuerpo y mente.</p>
                    <ul class="service-features">
                        <li>Masajes relajantes</li>
                        <li>Masajes terapéuticos</li>
                        <li>Tratamientos corporales</li>
                        <li>Aromaterapia</li>
                    </ul>
                </div>
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1604654894610-df63bc536371?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80"
                            alt="Estética">
                    </div>
                    <h3>Estética</h3>
                    <p>Cuidado profesional para manos, pies y rostro.</p>
                    <ul class="service-features">
                        <li>Manicura & Pedicura</li>
                        <li>Extensiones de pestañas</li>
                        <li>Diseño de cejas</li>
                        <li>Depilación profesional</li>
                    </ul>
                </div>
                <div class="service-card">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80"
                            alt="Facial">
                    </div>
                    <h3>Facial</h3>
                    <p>Tratamientos faciales personalizados para una piel radiante.</p>
                    <ul class="service-features">
                        <li>Limpieza profunda</li>
                        <li>Hidratación intensiva</li>
                        <li>Anti-edad</li>
                        <li>Revitalización</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Special Packages Section -->
    <section class="packages">
        <div class="container">
            <div class="services-intro" style="margin-bottom: 4rem;">
                <span class="section-label">Paquetes Especiales</span>
                <h2 class="section-title section-title-center">Momentos Inolvidables</h2>
                <p style="color: rgba(255,255,255,0.7);">Diseñados para tus eventos más importantes. Cada paquete incluye
                    atención personalizada y productos de la más alta calidad.</p>
            </div>
            <div class="packages-grid">
                <div class="package-card">
                    <div class="package-image">
                        <img src="https://images.unsplash.com/photo-1519741497674-611481863552?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80"
                            alt="Novias">
                    </div>
                    <h3>Novias</h3>
                    <p>Tu día perfecto merece una preparación perfecta. Incluye prueba previa.</p>
                    <ul class="package-services">
                        <li>Maquillaje profesional</li>
                        <li>Peinado de novia</li>
                        <li>Manicura & Pedicura</li>
                        <li>Tratamiento facial</li>
                        <li>Sesión de prueba</li>
                    </ul>
                    <a href="#contacto" class="package-cta">Consultar</a>
                </div>
                <div class="package-card featured">
                    <div class="package-image">
                        <img src="https://images.unsplash.com/photo-1595981234058-a9302fb97229?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80"
                            alt="Quinceañeras">
                    </div>
                    <h3>Quinceañeras</h3>
                    <p>Celebra tu gran día luciendo espectacular con nuestro paquete completo.</p>
                    <ul class="package-services">
                        <li>Maquillaje profesional</li>
                        <li>Peinado de gala</li>
                        <li>Manicura temática</li>
                        <li>Spa facial express</li>
                        <li>Prueba de look</li>
                    </ul>
                    <a href="#contacto" class="package-cta">Consultar</a>
                </div>
                <div class="package-card">
                    <div class="package-image">
                        <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80"
                            alt="Eventos">
                    </div>
                    <h3>Eventos</h3>
                    <p>Servicios para desfiles, empresas y eventos especiales.</p>
                    <ul class="package-services">
                        <li>Equipo de estilistas</li>
                        <li>Maquillaje grupal</li>
                        <li>Peinados coordinados</li>
                        <li>Servicio a domicilio</li>
                        <li>Asesoría de imagen</li>
                    </ul>
                    <a href="#contacto" class="package-cta">Consultar</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Branches Section -->
    <section id="sucursales" class="branches">
        <div class="container">
            <div class="services-intro">
                <span class="section-label">Encuéntranos</span>
                <h2 class="section-title section-title-center">Nuestras Sucursales</h2>
                <p>Con 6 ubicaciones en La Paz, siempre hay un Cristina Spa cerca de ti.</p>
            </div>
            <div class="branches-grid">
                <!-- Zona Central -->
                <div class="branch-card">
                    <div class="branch-zone">Zona Central</div>
                    <h3>Hotel Gloria</h3>
                    <ul class="branch-details">
                        <li>
                            <span>🕒</span>
                            <span>Lun - Vie: 8:00 - 22:00</span>
                        </li>
                        <li>
                            <span>🕒</span>
                            <span>Sáb: 9:00 - 20:00</span>
                        </li>
                        <li>
                            <span>🕒</span>
                            <span>Dom: 9:00 - 15:00</span>
                        </li>
                    </ul>
                    <div class="branch-phone">
                        <a href="tel:+59122906962">
                            📞 2906962
                        </a>
                    </div>
                    <a href="https://maps.google.com/?q=Hotel+Gloria+La+Paz+Bolivia" target="_blank"
                        class="branch-map-link">
                        📍 Ver en Google Maps
                    </a>
                </div>

                <!-- Mega Center -->
                <div class="branch-card">
                    <div class="branch-zone">Zona Sur</div>
                    <h3>Mega Center</h3>
                    <ul class="branch-details">
                        <li>
                            <span>🕒</span>
                            <span>Lun - Dom: 10:00 - 22:00</span>
                        </li>
                    </ul>
                    <div class="branch-phone">
                        <a href="tel:+59122770838">
                            📞 2770838
                        </a>
                    </div>
                    <a href="https://maps.google.com/?q=Mega+Center+Irpavi+La+Paz+Bolivia" target="_blank"
                        class="branch-map-link">
                        📍 Ver en Google Maps
                    </a>
                </div>

                <!-- Obrajes -->
                <div class="branch-card">
                    <div class="branch-zone">Zona Sur</div>
                    <h3>Obrajes</h3>
                    <span class="branch-location">Calle 15 Av. Hernando Siles</span>
                    <ul class="branch-details">
                        <li>
                            <span>🕒</span>
                            <span>Lun - Sáb: 9:00 - 20:00</span>
                        </li>
                    </ul>
                    <div class="branch-phone">
                        <a href="tel:+59122787766">
                            📞 2787766
                        </a>
                    </div>
                    <a href="https://maps.google.com/?q=Calle+15+Avenida+Hernando+Siles+Obrajes+La+Paz+Bolivia"
                        target="_blank" class="branch-map-link">
                        📍 Ver en Google Maps
                    </a>
                </div>

                <!-- San Miguel -->
                <div class="branch-card">
                    <div class="branch-zone">Zona Sur</div>
                    <h3>San Miguel</h3>
                    <span class="branch-location">Bloque L</span>
                    <ul class="branch-details">
                        <li>
                            <span>🕒</span>
                            <span>Mar - Sáb: 8:30 - 20:30</span>
                        </li>
                    </ul>
                    <div class="branch-phone">
                        <a href="tel:+59122773147">
                            📞 2773147
                        </a>
                    </div>
                    <a href="https://maps.google.com/?q=San+Miguel+Bloque+L+La+Paz+Bolivia" target="_blank"
                        class="branch-map-link">
                        📍 Ver en Google Maps
                    </a>
                </div>

                <!-- Calacoto C.19 -->
                <div class="branch-card">
                    <div class="branch-zone">Zona Sur</div>
                    <h3>Calacoto</h3>
                    <span class="branch-location">Calle 19</span>
                    <ul class="branch-details">
                        <li>
                            <span>🕒</span>
                            <span>Lun - Sáb: 9:00 - 20:00</span>
                        </li>
                        <li>
                            <span>🕒</span>
                            <span>Dom: 9:00 - 15:00</span>
                        </li>
                    </ul>
                    <div class="branch-phone">
                        <a href="tel:+59122770838">
                            📞 2770838
                        </a>
                    </div>
                    <a href="https://maps.google.com/?q=Calacoto+Calle+19+La+Paz+Bolivia" target="_blank"
                        class="branch-map-link">
                        📍 Ver en Google Maps
                    </a>
                </div>

                <!-- Calacoto C.13 -->
                <div class="branch-card">
                    <div class="branch-zone">Zona Sur</div>
                    <h3>Calacoto</h3>
                    <span class="branch-location">Calle 13</span>
                    <ul class="branch-details">
                        <li>
                            <span>🕒</span>
                            <span>Lun: 14:00 - 21:00</span>
                        </li>
                        <li>
                            <span>🕒</span>
                            <span>Mar - Sáb: 9:00 - 21:00</span>
                        </li>
                    </ul>
                    <div class="branch-phone">
                        <a href="tel:+59122799352">
                            📞 2799352
                        </a>
                    </div>
                    <a href="https://maps.google.com/?q=Calacoto+Calle+13+La+Paz+Bolivia" target="_blank"
                        class="branch-map-link">
                        📍 Ver en Google Maps
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="galeria" class="gallery">
        <div class="container">
            <div class="services-intro">
                <span class="section-label">Nuestro Trabajo</span>
                <h2 class="section-title section-title-center">Galería</h2>
                <p>Una muestra de las transformaciones y momentos especiales que creamos cada día.</p>
            </div>
            <div class="gallery-grid">
                <div class="gallery-item">
                    <div class="gallery-placeholder">
                        <span>Colorimetría</span>
                    </div>
                    <div class="gallery-overlay">
                        <span>Balayage Premium</span>
                    </div>
                </div>
                <div class="gallery-item">
                    <div class="gallery-placeholder">
                        <span>Novia</span>
                    </div>
                    <div class="gallery-overlay">
                        <span>Peinado Nupcial</span>
                    </div>
                </div>
                <div class="gallery-item">
                    <div class="gallery-placeholder">
                        <span>Spa</span>
                    </div>
                    <div class="gallery-overlay">
                        <span>Masaje Relajante</span>
                    </div>
                </div>
                <div class="gallery-item">
                    <div class="gallery-placeholder">
                        <span>Corte</span>
                    </div>
                    <div class="gallery-overlay">
                        <span>Corte Moderno</span>
                    </div>
                </div>
                <div class="gallery-item">
                    <div class="gallery-placeholder">
                        <span>Manicura</span>
                    </div>
                    <div class="gallery-overlay">
                        <span>Nail Art Premium</span>
                    </div>
                </div>
                <div class="gallery-item">
                    <div class="gallery-placeholder">
                        <span>Quinceañera</span>
                    </div>
                    <div class="gallery-overlay">
                        <span>Look de Gala</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contacto" class="contact">
        <div class="container">
            <div class="services-intro">
                <span class="section-label">Contáctanos</span>
                <h2 class="section-title section-title-center">Reserva tu Cita</h2>
            </div>
            <div class="contact-content">
                <div class="contact-info">
                    <h3>¿Lista para transformarte?</h3>
                    <p class="contact-description">
                        Reserva tu cita online y vive la experiencia Cristina Spa.
                        Nuestro equipo te espera para brindarte un servicio personalizado
                        y de la más alta calidad.
                    </p>
                    <div class="info-item">
                        <span class="info-icon">📍</span>
                        <div>
                            <p><strong>6 Sucursales</strong></p>
                            <p>Hotel Gloria, Mega Center, Obrajes, San Miguel, Calacoto C.19, Calacoto C.13</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="info-icon">📞</span>
                        <div>
                            <p><strong>Teléfono Central</strong></p>
                            <p>2906962</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="info-icon">✉️</span>
                        <div>
                            <p><strong>Email</strong></p>
                            <p>info@cristinaspa.com</p>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="info-icon">🕒</span>
                        <div>
                            <p><strong>Horario General</strong></p>
                            <p>Lunes a Sábados</p>
                            <p>Ver horarios por sucursal</p>
                        </div>
                    </div>
                </div>
                <form class="contact-form">
                    <h3>Solicitar Cita</h3>
                    <div class="form-row">
                        <input type="text" placeholder="Nombre completo" required>
                        <input type="email" placeholder="Correo electrónico" required>
                    </div>
                    <div class="form-row">
                        <input type="tel" placeholder="Teléfono / WhatsApp" required>
                        <select required>
                            <option value="">Selecciona sucursal</option>
                            <option value="hotel-gloria">Hotel Gloria - Zona Central</option>
                            <option value="mega-center">Mega Center - Irpavi</option>
                            <option value="obrajes">Obrajes - Calle 15</option>
                            <option value="san-miguel">San Miguel - Bloque L</option>
                            <option value="calacoto-c19">Calacoto - Calle 19</option>
                            <option value="calacoto-c13">Calacoto - Calle 13</option>
                        </select>
                    </div>
                    <select required>
                        <option value="">Selecciona un servicio</option>
                        <optgroup label="Peluquería">
                            <option value="corte">Corte de Cabello</option>
                            <option value="color">Colorimetría</option>
                            <option value="balayage">Balayage</option>
                            <option value="tratamiento-capilar">Tratamiento Capilar</option>
                        </optgroup>
                        <optgroup label="Spa & Bienestar">
                            <option value="masaje-relajante">Masaje Relajante</option>
                            <option value="masaje-terapeutico">Masaje Terapéutico</option>
                            <option value="tratamiento-corporal">Tratamiento Corporal</option>
                        </optgroup>
                        <optgroup label="Estética">
                            <option value="manicura">Manicura</option>
                            <option value="pedicura">Pedicura</option>
                            <option value="pestanas">Extensiones de Pestañas</option>
                            <option value="cejas">Diseño de Cejas</option>
                        </optgroup>
                        <optgroup label="Paquetes Especiales">
                            <option value="novia">Paquete Novia</option>
                            <option value="quinceanera">Paquete Quinceañera</option>
                            <option value="evento">Evento Especial</option>
                        </optgroup>
                    </select>
                    <textarea placeholder="Mensaje o comentarios adicionales (opcional)" rows="4"></textarea>
                    <button type="submit" class="submit-button">Enviar Solicitud</button>
                </form>
            </div>
        </div>
    </section>

    <!-- WhatsApp Floating Button -->
    <a href="https://wa.me/59122906962?text=Hola%2C%20quiero%20reservar%20una%20cita%20en%20Cristina%20Spa"
        class="whatsapp-float" target="_blank" rel="noopener noreferrer" aria-label="Chat en WhatsApp">
        <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M16.004 0h-.008C7.174 0 0 7.176 0 16c0 3.5 1.128 6.744 3.046 9.378L1.054 31.29l6.156-1.97A15.89 15.89 0 0016.004 32C24.826 32 32 24.822 32 16S24.826 0 16.004 0zm9.316 22.594c-.39 1.1-1.932 2.012-3.156 2.28-.838.178-1.932.32-5.618-1.208-4.714-1.952-7.75-6.744-7.986-7.058-.228-.314-1.874-2.494-1.874-4.756 0-2.262 1.186-3.374 1.608-3.834.39-.424 1.032-.618 1.646-.618.198 0 .376.01.536.018.422.018.634.042.912.708.346.832 1.186 2.89 1.29 3.102.104.212.202.49.078.782-.114.294-.212.424-.424.66-.212.236-.414.416-.626.672-.198.228-.42.472-.18.912.24.432 1.068 1.762 2.294 2.854 1.578 1.406 2.906 1.842 3.318 2.044.314.154.69.132.94-.13.314-.332.702-.882 1.098-1.424.282-.386.638-.434.98-.294.346.132 2.192 1.034 2.568 1.222.376.19.628.284.72.44.09.156.09.896-.3 1.994z" />
        </svg>
        <span class="whatsapp-tooltip">¡Escríbenos!</span>
    </a>
@endsection
