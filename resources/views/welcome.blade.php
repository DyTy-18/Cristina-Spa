@extends('layouts.app')

@section('title', 'Cristina Spa - Belleza, Estilo & Bienestar | Desde 2006')

@section('content')
    <!-- Campaign Popup Modal -->
    <div id="campaign-popup" class="campaign-popup">
        <div class="campaign-overlay"></div>
        <div class="campaign-modal">
            <button class="campaign-close" aria-label="Cerrar">×</button>
            <a href="#contacto" class="campaign-link">
                <img src="{{ asset('images/campañas/coloracion para el CRM.png') }}"
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
                            <span class="stat-number">3</span>
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

            <div class="accordion">

                <!-- 1. Peluquería -->
                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>✂️ Peluquería</span>
                        <span class="accordion-icon">+</span>
                    </button>
                    <div class="accordion-content">
                        <div class="price-list-section">
                            <h4 class="price-subtitle">Cortes</h4>
                            <ul class="price-list">
                                <li class="price-list-item"><span>Corte de Mujer</span><span class="price-tag">250 Bs.</span></li>
                                <li class="price-list-item"><span>Corte de Varón</span><span class="price-tag">100 Bs.</span></li>
                                <li class="price-list-item"><span>Corte de Flequillo</span><span class="price-tag">80 Bs.</span></li>
                            </ul>
                        </div>
                        <div class="price-list-section">
                            <h4 class="price-subtitle">Peinados — por largo de cabello</h4>
                            <div class="table-responsive">
                                <table class="price-table">
                                    <thead>
                                        <tr>
                                            <th>Servicio</th>
                                            <th>Corto</th>
                                            <th>Mediano<br><small>(Hasta Hombros)</small></th>
                                            <th>Largo<br><small>(Hasta Media Espalda)</small></th>
                                            <th>Extra Largo<br><small>(Hasta Cintura)</small></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr><td>Lavado</td><td>80 Bs.</td><td>80 Bs.</td><td>80 Bs.</td><td>80 Bs.</td></tr>
                                        <tr><td>Planchado o Bucles</td><td>70 Bs.</td><td>80 Bs.</td><td>90 Bs.</td><td>100 Bs.</td></tr>
                                        <tr><td>Semi-recogido</td><td>100 Bs.</td><td>120 Bs.</td><td>150 Bs.</td><td>160 Bs.</td></tr>
                                        <tr><td>Recogido</td><td>150 Bs.</td><td>150 Bs.</td><td>200 Bs.</td><td>200 Bs.</td></tr>
                                        <tr><td>Peinado de Novia</td><td>160 Bs.</td><td>180 Bs.</td><td>200 Bs.</td><td>200 Bs.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Coloración -->
                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>🎨 Coloración</span>
                        <span class="accordion-icon">+</span>
                    </button>
                    <div class="accordion-content">
                        <div class="table-responsive">
                            <table class="price-table">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Corto</th>
                                        <th>Mediano<br><small>(Hasta Hombros)</small></th>
                                        <th>Largo<br><small>(Hasta Media Espalda)</small></th>
                                        <th>Extra Largo<br><small>(Hasta Cintura)</small></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>Retoque de Raíz</td><td>310 Bs. <small>(1cm)</small></td><td>350 Bs. <small>(3cm)</small></td><td>—</td><td>—</td></tr>
                                    <tr><td>Matizado con masque Kérastase</td><td>180 Bs.</td><td>200 Bs.</td><td>250 Bs.</td><td>270 Bs.</td></tr>
                                    <tr><td>Matizado Tono Sobre Tono</td><td>250 Bs.</td><td>250 Bs.</td><td>300 Bs.</td><td>300 Bs.</td></tr>
                                    <tr><td>Tiger Eye (Sin Fondo)</td><td>450 Bs.</td><td>650 Bs.</td><td>800 Bs.</td><td>950 Bs.</td></tr>
                                    <tr><td>Baby Lights (Sin Fondo)</td><td>450 Bs.</td><td>650 Bs.</td><td>850 Bs.</td><td>950 Bs.</td></tr>
                                    <tr><td>Balayage Platinado</td><td>750 Bs.</td><td>950 Bs.</td><td>1.150 Bs.</td><td>1.800 Bs.</td></tr>
                                    <tr><td>Color Global</td><td>500 Bs.</td><td>600 Bs.</td><td>700 Bs.</td><td>800 Bs.</td></tr>
                                    <tr><td>Morena Iluminada (sin decoloración)</td><td>550 Bs.</td><td>750 Bs.</td><td>950 Bs.</td><td>1.050 Bs.</td></tr>
                                    <tr><td>Mechas Creativas Rubias</td><td>700 Bs.</td><td>900 Bs.</td><td>1.100 Bs.</td><td>1.600 Bs.</td></tr>
                                    <tr><td>Color de Fondo (cualquier técnica)</td><td>300 Bs.</td><td>300 Bs.</td><td>350 Bs.</td><td>350 Bs.</td></tr>
                                    <tr><td>Metal-Detox</td><td>180 Bs.</td><td>180 Bs.</td><td>250 Bs.</td><td>250 Bs.</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="price-note">* Precios previa evaluación</p>
                    </div>
                </div>

                <!-- 3. Alisado u Ondulación -->
                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>💫 Alisado u Ondulación</span>
                        <span class="accordion-icon">+</span>
                    </button>
                    <div class="accordion-content">
                        <div class="table-responsive">
                            <table class="price-table">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Corto</th>
                                        <th>Mediano<br><small>(Hasta Hombros)</small></th>
                                        <th>Largo<br><small>(Hasta Media Espalda)</small></th>
                                        <th>Extra Largo<br><small>(Hasta Cintura)</small></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>Alisado Definitivo</td><td>750 Bs.</td><td>850 Bs.</td><td>1.300 Bs.</td><td>1.600 Bs.</td></tr>
                                    <tr><td>Ondulación o Permanente</td><td>500 Bs.</td><td>600 Bs.</td><td>700 Bs.</td><td>800 Bs.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 4. Depilado con Cera -->
                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>🌿 Depilado con Cera</span>
                        <span class="accordion-icon">+</span>
                    </button>
                    <div class="accordion-content">
                        <div class="table-responsive">
                            <table class="price-table">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Con Cera</th>
                                        <th>Con Hilo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>Facial</td><td>120 Bs.</td><td>80 Bs.</td></tr>
                                    <tr><td>Pierna Completa</td><td>150 Bs.</td><td>—</td></tr>
                                    <tr><td>Cuello</td><td>50 Bs.</td><td>—</td></tr>
                                    <tr><td>Axilas</td><td>80 Bs.</td><td>—</td></tr>
                                    <tr><td>Perfilado de Cejas</td><td>60 Bs.</td><td>30 Bs.</td></tr>
                                    <tr><td>Bozo</td><td>40 Bs.</td><td>30 Bs.</td></tr>
                                    <tr><td>Espalda</td><td>100 Bs.</td><td>—</td></tr>
                                    <tr><td>Abdomen</td><td>80 Bs.</td><td>—</td></tr>
                                    <tr><td>Brasilero</td><td>150 Bs.</td><td>—</td></tr>
                                    <tr><td>Brazo</td><td>120 Bs.</td><td>—</td></tr>
                                    <tr><td>Depilación Completa</td><td>500 Bs.</td><td>—</td></tr>
                                    <tr><td>Biquini</td><td>100 Bs.</td><td>—</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 5. Maquillaje -->
                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>💄 Maquillaje</span>
                        <span class="accordion-icon">+</span>
                    </button>
                    <div class="accordion-content">
                        <ul class="price-list">
                            <li class="price-list-item"><span>Maquillaje de Fiesta Sin Pestañas</span><span class="price-tag">150 Bs.</span></li>
                            <li class="price-list-item"><span>Pestañas 3D / 5D / 6D</span><span class="price-tag">80 Bs.</span></li>
                            <li class="price-list-item"><span>Cejas y Pestañas</span><span class="price-tag">120 Bs.</span></li>
                            <li class="price-list-item"><span>Rizado de Pestañas</span><span class="price-tag">100 Bs.</span></li>
                            <li class="price-list-item"><span>Laminado de Cejas</span><span class="price-tag">200 Bs.</span></li>
                            <li class="price-list-item"><span>Rizado de Pestañas + Laminado de Cejas</span><span class="price-tag">Consultar</span></li>
                        </ul>
                    </div>
                </div>

                <!-- 6. Manicura & Pedicura -->
                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>💅 Manicura & Pedicura</span>
                        <span class="accordion-icon">+</span>
                    </button>
                    <div class="accordion-content">
                        <ul class="price-list">
                            <li class="price-list-item"><span>Kit Manicura</span><span class="price-tag">60 Bs.</span></li>
                            <li class="price-list-item"><span>Pedicura</span><span class="price-tag">60 Bs.</span></li>
                            <li class="price-list-item"><span>Kit de Pedicura</span><span class="price-tag">80 Bs.</span></li>
                            <li class="price-list-item"><span>Extensiones de Uñas</span><span class="price-tag">80 Bs.</span></li>
                            <li class="price-list-item"><span>Esmaltado Normal</span><span class="price-tag">30 Bs.</span></li>
                            <li class="price-list-item"><span>Esmaltado Normal con Diseños</span><span class="price-tag">50 Bs.</span></li>
                            <li class="price-list-item"><span>Pintado en Gel</span><span class="price-tag">120 Bs.</span></li>
                            <li class="price-list-item"><span>Forrado de Uñas — Técnica Dipping</span><span class="price-tag">80 Bs.</span></li>
                            <li class="price-list-item"><span>Forrado de Acrílico con Tips</span><span class="price-tag">160 Bs.</span></li>
                            <li class="price-list-item"><span>Uñas Esculpidas con Acrílico</span><span class="price-tag">200 Bs.</span></li>
                            <li class="price-list-item"><span>Técnica Soft Gel</span><span class="price-tag">200 Bs.</span></li>
                        </ul>
                        <p class="price-note">* El precio varía de acuerdo al largo y al diseño</p>
                    </div>
                </div>

                <!-- 7. Spa -->
                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>🧖 Spa</span>
                        <span class="accordion-icon">+</span>
                    </button>
                    <div class="accordion-content">
                        <div class="price-list-section">
                            <h4 class="price-subtitle">Limpiezas</h4>
                            <ul class="price-list">
                                <li class="price-list-item"><span>Limpieza Facial</span><span class="price-tag">150 Bs.</span></li>
                                <li class="price-list-item"><span>Limpieza con Micro-Dermo Abrasión</span><span class="price-tag">250 Bs.</span></li>
                            </ul>
                        </div>
                        <div class="price-list-section">
                            <h4 class="price-subtitle">Masajes</h4>
                            <ul class="price-list">
                                <li class="price-list-item"><span>Masaje Relajante</span><span class="price-tag">150 Bs.</span></li>
                                <li class="price-list-item"><span>Exfoliación Corporal</span><span class="price-tag">180 Bs.</span></li>
                                <li class="price-list-item"><span>Envolvimiento Corporal</span><span class="price-tag">180 Bs.</span></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 8. Tratamientos Capilares -->
                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>💎 Tratamientos Capilares</span>
                        <span class="accordion-icon">+</span>
                    </button>
                    <div class="accordion-content">
                        <ul class="price-list">
                            <li class="price-list-item"><span>Tratamiento de Lujo</span><span class="price-tag">650 Bs.</span></li>
                            <li class="price-list-item"><span>Thermo Blindaje Capilar</span><span class="price-tag">500 Bs.</span></li>
                            <li class="price-list-item"><span>Tratamiento Nutrición Ampolla Kérastase</span><span class="price-tag">350 Bs.</span></li>
                            <li class="price-list-item"><span>Tratamiento 24 Kilates, Aurora Botania</span><span class="price-tag">350 Bs.</span></li>
                            <li class="price-list-item"><span>Tratamiento Express Intenso (Shampoo + Mascarilla)</span><span class="price-tag">200 Bs.</span></li>
                            <li class="price-list-item"><span>Tratamiento Essencial (Shampoo + Acondicionador)</span><span class="price-tag">150 Bs.</span></li>
                        </ul>
                    </div>
                </div>

                <!-- 9. Productos Kérastase -->
                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>🛍️ Productos Kérastase</span>
                        <span class="accordion-icon">+</span>
                    </button>
                    <div class="accordion-content">
                        <div class="prod-brand-bar prod-brand-kerastase">
                            <span class="prod-brand-name">Kérastase</span>
                            <span class="prod-brand-sub">París · Línea Profesional</span>
                        </div>
                        <div class="table-responsive">
                            <table class="price-table prod-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="prod-cat-row"><td colspan="2">Shampoos — Bains</td></tr>
                                    <tr><td>Bain</td><td>420 Bs.</td></tr>
                                    <tr><td>Bain Première</td><td>455 Bs.</td></tr>
                                    <tr><td>Bain Chronologiste</td><td>495 Bs.</td></tr>

                                    <tr class="prod-cat-row"><td colspan="2">Acondicionadores — Fondants</td></tr>
                                    <tr><td>Fondant</td><td>510 Bs.</td></tr>
                                    <tr><td>Fondant Première</td><td>560 Bs.</td></tr>

                                    <tr class="prod-cat-row"><td colspan="2">Mascarillas</td></tr>
                                    <tr><td>Máscara</td><td>720 Bs.</td></tr>
                                    <tr><td>Máscara Première</td><td>790 Bs.</td></tr>
                                    <tr><td>Máscara Chronologiste</td><td>820 Bs.</td></tr>

                                    <tr class="prod-cat-row"><td colspan="2">Tratamientos &amp; Finalizadores</td></tr>
                                    <tr><td>Elixir Ultimate</td><td>755 Bs.</td></tr>
                                    <tr><td>Texturizantes</td><td>710 Bs.</td></tr>
                                    <tr><td>Light Serum</td><td>1.030 Bs.</td></tr>
                                    <tr><td>Cure</td><td>1.260 Bs.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 10. Productos L'Oréal -->
                <div class="accordion-item">
                    <button class="accordion-header">
                        <span>🛍️ Productos L'Oréal — Serie Expert</span>
                        <span class="accordion-icon">+</span>
                    </button>
                    <div class="accordion-content">
                        <div class="prod-brand-bar prod-brand-loreal">
                            <span class="prod-brand-name">L'Oréal Professionnel</span>
                            <span class="prod-brand-sub">París · Serie Expert</span>
                        </div>
                        <div class="table-responsive">
                            <table class="price-table prod-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="prod-cat-row"><td colspan="2">Shampoos</td></tr>
                                    <tr><td>Shampoo — 300 ml</td><td>315 Bs.</td></tr>
                                    <tr><td>Shampoo Absolut Repair Molecular — 300 ml</td><td>340 Bs.</td></tr>

                                    <tr class="prod-cat-row"><td colspan="2">Acondicionadores</td></tr>
                                    <tr><td>Acondicionador — 200 ml</td><td>345 Bs.</td></tr>

                                    <tr class="prod-cat-row"><td colspan="2">Mascarillas</td></tr>
                                    <tr><td>Máscara — 250 ml</td><td>389 Bs.</td></tr>
                                    <tr><td>Máscara Absolut Repair Molecular — 250 ml</td><td>430 Bs.</td></tr>

                                    <tr class="prod-cat-row"><td colspan="2">Tratamientos &amp; Finalizadores</td></tr>
                                    <tr><td>Spray 10 en 1 — 190 ml</td><td>350 Bs.</td></tr>
                                    <tr><td>Aceite 10 en 1 — 190 ml</td><td>360 Bs.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div><!-- /.accordion -->
        </div>
    </section>

    <!-- Special Packages Section -->
    <section class="packages">
        <div class="container">
            <div class="services-intro" style="margin-bottom: 1.5rem;">
                <span class="section-label">Paquetes Especiales</span>
                <h2 class="section-title section-title-center">Elige tu Experiencia</h2>
                <p style="color: rgba(255,255,255,0.7);">Diseñados para novias, quinceañeras y eventos especiales.<br>Cada nivel ofrece una experiencia única con los mejores productos L'Oréal y Kérastase.</p>
            </div>

            <!-- Event type tabs -->
            <div class="pkg-tabs">
                <button class="pkg-tab active" data-panel="pkg-novias">Novias</button>
                <button class="pkg-tab" data-panel="pkg-quinceaneras">Quinceañeras</button>
                <button class="pkg-tab" data-panel="pkg-eventos">Eventos</button>
            </div>

            <!-- ── NOVIAS ── -->
            <div class="pkg-panel active" id="pkg-novias">
                <div class="packages-grid">

                    <!-- Esmeralda -->
                    <div class="package-card pkg-esmeralda">
                        <div class="pkg-gem-badge" style="--gem: #50c878;">
                            <span class="pkg-gem-icon">◆</span>
                            <span class="pkg-gem-label">Esmeralda</span>
                        </div>
                        <p class="pkg-tier-desc">Tu transformación esencial para el gran día</p>
                        <div class="pkg-price">Desde <strong>450 Bs.</strong></div>
                        <div class="pkg-card-img">
                            <img src="{{ asset('images/galeria/novia/novia_1.jpg') }}" alt="Paquete Novias Esmeralda">
                        </div>
                        <ul class="package-services">
                            <li>Lavado y peinado profesional</li>
                            <li>Maquillaje de novia básico</li>
                            <li>Manicura simple</li>
                            <li>Perfilado de cejas</li>
                            <li>Tratamiento capilar express</li>
                        </ul>
                        <a href="#contacto" class="package-cta">Reservar</a>
                    </div>

                    <!-- Rubí (destacado) -->
                    <div class="package-card featured pkg-rubi">
                        <div class="pkg-gem-badge" style="--gem: #c9a96e;">
                            <span class="pkg-gem-icon">◆</span>
                            <span class="pkg-gem-label">Rubí</span>
                        </div>
                        <p class="pkg-tier-desc">La elección más elegida por nuestras novias</p>
                        <div class="pkg-price">Desde <strong>850 Bs.</strong></div>
                        <div class="pkg-card-img">
                            <img src="{{ asset('images/galeria/novia/novia_2.jpg') }}" alt="Paquete Novias Rubí">
                        </div>
                        <ul class="package-services">
                            <li>Todo lo del Esmeralda</li>
                            <li>Maquillaje de gala con pestañas</li>
                            <li>Manicura en gel + pedicura</li>
                            <li>Tratamiento Kérastase</li>
                            <li>Rizado de pestañas</li>
                            <li>Sesión de prueba de look</li>
                        </ul>
                        <a href="#contacto" class="package-cta">Reservar</a>
                    </div>

                    <!-- Diamante -->
                    <div class="package-card pkg-diamante">
                        <div class="pkg-gem-badge" style="--gem: #a8d8ea;">
                            <span class="pkg-gem-icon">◆</span>
                            <span class="pkg-gem-label">Diamante</span>
                        </div>
                        <p class="pkg-tier-desc">La experiencia definitiva, sin límites</p>
                        <div class="pkg-price"><strong>A consultar</strong></div>
                        <div class="pkg-card-img">
                            <img src="{{ asset('images/galeria/novia/novia_3.jpg') }}" alt="Paquete Novias Diamante">
                        </div>
                        <ul class="package-services">
                            <li>Todo lo del Rubí</li>
                            <li>Colorimetría o balayage premium</li>
                            <li>Tratamiento de lujo Kérastase</li>
                            <li>Spa facial completo</li>
                            <li>Coordinación de imagen dedicada</li>
                            <li>Servicio a domicilio disponible</li>
                            <li>Productos Kérastase incluidos</li>
                        </ul>
                        <a href="#contacto" class="package-cta">Consultar</a>
                    </div>

                </div>
            </div>

            <!-- ── QUINCEAÑERAS ── -->
            <div class="pkg-panel" id="pkg-quinceaneras">
                <div class="packages-grid">

                    <div class="package-card pkg-esmeralda">
                        <div class="pkg-gem-badge" style="--gem: #50c878;">
                            <span class="pkg-gem-icon">◆</span>
                            <span class="pkg-gem-label">Esmeralda</span>
                        </div>
                        <p class="pkg-tier-desc">El inicio de tu noche de gala perfecta</p>
                        <div class="pkg-price">Desde <strong>380 Bs.</strong></div>
                        <div class="pkg-card-img">
                            <img src="{{ asset('images/galeria/quinceañera/CRISTINA SPA FOTOS_22.jpg') }}" alt="Paquete Quinceañeras Esmeralda">
                        </div>
                        <ul class="package-services">
                            <li>Lavado y peinado de gala</li>
                            <li>Maquillaje básico de fiesta</li>
                            <li>Manicura temática</li>
                            <li>Perfilado de cejas</li>
                            <li>Desmaquillado incluido</li>
                        </ul>
                        <a href="#contacto" class="package-cta">Reservar</a>
                    </div>

                    <div class="package-card featured pkg-rubi">
                        <div class="pkg-gem-badge" style="--gem: #c9a96e;">
                            <span class="pkg-gem-icon">◆</span>
                            <span class="pkg-gem-label">Rubí</span>
                        </div>
                        <p class="pkg-tier-desc">La elección de las princesas de La Paz</p>
                        <div class="pkg-price">Desde <strong>720 Bs.</strong></div>
                        <div class="pkg-card-img">
                            <img src="{{ asset('images/galeria/quinceañera/CRISTINA SPA FOTOS_32.jpg') }}" alt="Paquete Quinceañeras Rubí">
                        </div>
                        <ul class="package-services">
                            <li>Todo lo del Esmeralda</li>
                            <li>Maquillaje artístico con pestañas</li>
                            <li>Manicura en gel + pedicura</li>
                            <li>Tratamiento capilar L'Oréal</li>
                            <li>Rizado o laminado de pestañas</li>
                            <li>Prueba de look previa</li>
                        </ul>
                        <a href="#contacto" class="package-cta">Reservar</a>
                    </div>

                    <div class="package-card pkg-diamante">
                        <div class="pkg-gem-badge" style="--gem: #a8d8ea;">
                            <span class="pkg-gem-icon">◆</span>
                            <span class="pkg-gem-label">Diamante</span>
                        </div>
                        <p class="pkg-tier-desc">Una noche que recordarás toda la vida</p>
                        <div class="pkg-price"><strong>A consultar</strong></div>
                        <div class="pkg-card-img">
                            <img src="{{ asset('images/galeria/quinceañera/CRISTINA SPA FOTOS_33.jpg') }}" alt="Paquete Quinceañeras Diamante">
                        </div>
                        <ul class="package-services">
                            <li>Todo lo del Rubí</li>
                            <li>Colorimetría o mechas creativas</li>
                            <li>Tratamiento Kérastase premium</li>
                            <li>Spa facial y limpieza express</li>
                            <li>Manicura soft gel con diseño</li>
                            <li>Coordinación de imagen completa</li>
                            <li>Servicio a domicilio disponible</li>
                        </ul>
                        <a href="#contacto" class="package-cta">Consultar</a>
                    </div>

                </div>
            </div>

            <!-- ── EVENTOS ── -->
            <div class="pkg-panel" id="pkg-eventos">
                <div class="packages-grid">

                    <div class="package-card pkg-esmeralda">
                        <div class="pkg-gem-badge" style="--gem: #50c878;">
                            <span class="pkg-gem-icon">◆</span>
                            <span class="pkg-gem-label">Esmeralda</span>
                        </div>
                        <p class="pkg-tier-desc">Imagen profesional para tu evento</p>
                        <div class="pkg-price">Desde <strong>300 Bs.</strong></div>
                        <div class="pkg-card-img">
                            <img src="{{ asset('images/galeria/colorimetria/CRISTINA SPA FOTOS_23.jpg') }}" alt="Paquete Eventos Esmeralda">
                        </div>
                        <ul class="package-services">
                            <li>Peinado de fiesta</li>
                            <li>Maquillaje básico</li>
                            <li>Manicura simple</li>
                            <li>Asesoría de imagen express</li>
                            <li>Atención en sucursal</li>
                        </ul>
                        <a href="#contacto" class="package-cta">Reservar</a>
                    </div>

                    <div class="package-card featured pkg-rubi">
                        <div class="pkg-gem-badge" style="--gem: #c9a96e;">
                            <span class="pkg-gem-icon">◆</span>
                            <span class="pkg-gem-label">Rubí</span>
                        </div>
                        <p class="pkg-tier-desc">Para grupos y desfiles corporativos</p>
                        <div class="pkg-price">Desde <strong>580 Bs.</strong></div>
                        <div class="pkg-card-img">
                            <img src="{{ asset('images/galeria/colorimetria/CRISTINA SPA FOTOS_25.jpg') }}" alt="Paquete Eventos Rubí">
                        </div>
                        <ul class="package-services">
                            <li>Todo lo del Esmeralda</li>
                            <li>Maquillaje artístico de evento</li>
                            <li>Manicura + pedicura coordinada</li>
                            <li>Equipo de 2 estilistas</li>
                            <li>Coordinación de looks grupal</li>
                            <li>Atención express sin cita previa</li>
                        </ul>
                        <a href="#contacto" class="package-cta">Reservar</a>
                    </div>

                    <div class="package-card pkg-diamante">
                        <div class="pkg-gem-badge" style="--gem: #a8d8ea;">
                            <span class="pkg-gem-icon">◆</span>
                            <span class="pkg-gem-label">Diamante</span>
                        </div>
                        <p class="pkg-tier-desc">Experiencia VIP para desfiles y empresas</p>
                        <div class="pkg-price"><strong>A consultar</strong></div>
                        <div class="pkg-card-img">
                            <img src="{{ asset('images/galeria/spa/spa_1.jpg') }}" alt="Paquete Eventos Diamante">
                        </div>
                        <ul class="package-services">
                            <li>Todo lo del Rubí</li>
                            <li>Equipo completo de estilistas</li>
                            <li>Maquillaje artístico especializado</li>
                            <li>Asesoría de imagen integral</li>
                            <li>Servicio a domicilio o locación</li>
                            <li>Coordinación logística incluida</li>
                            <li>Productos de marca garantizados</li>
                        </ul>
                        <a href="#contacto" class="package-cta">Consultar</a>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- Branches Section -->
    <section id="sucursales" class="branches">
        <div class="branches-inner">

            <div class="branches-header">
                <span class="section-label">Encuéntranos</span>
                <h2 class="section-title">Nuestras<br>Sucursales</h2>
                <p class="branches-subtitle">Cuatro ubicaciones en La Paz para estar siempre cerca de ti.</p>
                <div class="branches-city-badge">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    La Paz, Bolivia
                </div>
            </div>

            <div class="branches-grid">

                <!-- 01 Hotel Gloria -->
                <div class="branch-card">
                    <span class="branch-num"></span>
                    <div class="branch-top">
                        <span class="branch-zone">Zona Central</span>
                        <h3 class="branch-name-link" onclick="openBranchModal('gloria')">Hotel Gloria</h3>
                    </div>
                    <div class="branch-body">
                        <div class="branch-row">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>Lun – Sáb &nbsp; 9:00 – 20:00</span>
                        </div>
                        <div class="branch-row">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.76A16 16 0 0 0 16 16.76l1.12-1.12a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 24 18v-.08z"/></svg>
                            <div class="branch-phones">
                                <a href="https://wa.me/59176768798?text=Hola%2C%20quiero%20reservar%20una%20cita%20en%20Cristina%20Spa%20%E2%80%93%20Hotel%20Gloria" target="_blank" rel="noopener">76768798</a>
                            </div>
                        </div>
                    </div>
                    <button class="branch-map-link" onclick="openBranchModal('gloria')">
                        Ver en Maps
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                    </button>
                </div>

                <!-- 02 San Miguel -->
                <div class="branch-card">
                    <span class="branch-num"></span>
                    <div class="branch-top">
                        <span class="branch-zone">Zona Sur</span>
                        <h3 class="branch-name-link" onclick="openBranchModal('sanmiguel')">San Miguel</h3>
                    </div>
                    <div class="branch-body">
                        <div class="branch-row">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>Mar – Sáb &nbsp; 9:00 – 20:00</span>
                        </div>
                        <div class="branch-row">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.76A16 16 0 0 0 16 16.76l1.12-1.12a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 24 18v-.08z"/></svg>
                            <div class="branch-phones">
                                <a href="https://wa.me/59176769723?text=Hola%2C%20quiero%20reservar%20una%20cita%20en%20Cristina%20Spa%20%E2%80%93%20San%20Miguel" target="_blank" rel="noopener">76769723</a>
                                <a href="tel:+59122773147">2773147</a>
                            </div>
                        </div>
                    </div>
                    <button class="branch-map-link" onclick="openBranchModal('sanmiguel')">
                        Ver en Maps
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                    </button>
                </div>

                <!-- 03 Calacoto -->
                <div class="branch-card">
                    <span class="branch-num"></span>
                    <div class="branch-top">
                        <span class="branch-zone">Zona Sur</span>
                        <h3 class="branch-name-link" onclick="openBranchModal('calacoto')">Calacoto</h3>
                    </div>
                    <div class="branch-body">
                        <div class="branch-row">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>Lun – Sáb &nbsp; 9:00 – 20:00</span>
                        </div>
                        <div class="branch-row">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.76A16 16 0 0 0 16 16.76l1.12-1.12a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 24 18v-.08z"/></svg>
                            <div class="branch-phones">
                                <a href="https://wa.me/59176768796?text=Hola%2C%20quiero%20reservar%20una%20cita%20en%20Cristina%20Spa%20%E2%80%93%20Calacoto" target="_blank" rel="noopener">76768796</a>
                                <a href="tel:+5912799352">2799352</a>
                            </div>
                        </div>
                    </div>
                    <button class="branch-map-link" onclick="openBranchModal('calacoto')">
                        Ver en Maps
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                    </button>
                </div>

                <!-- 04 Achumani -->
                <div class="branch-card">
                    <span class="branch-num"></span>
                    <div class="branch-top">
                        <span class="branch-zone">Zona Sur</span>
                        <h3 class="branch-name-link" onclick="openBranchModal('achumani')">Achumani</h3>
                    </div>
                    <div class="branch-body">
                        <div class="branch-row">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>Mar – Sáb &nbsp; 9:00 – 20:00</span>
                        </div>
                        <div class="branch-row">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.76A16 16 0 0 0 16 16.76l1.12-1.12a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 24 18v-.08z"/></svg>
                            <div class="branch-phones">
                                <a href="https://wa.me/59167097032?text=Hola%2C%20quiero%20reservar%20una%20cita%20en%20Cristina%20Spa%20%E2%80%93%20Achumani" target="_blank" rel="noopener">67097032</a>
                            </div>
                        </div>
                    </div>
                    <button class="branch-map-link" onclick="openBranchModal('achumani')">
                        Ver en Maps
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                    </button>
                </div>

            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <div class="services-intro">
                <span class="section-label">Lo Que Dicen Nuestros Clientes</span>
                <h2 class="section-title section-title-center">Testimonios</h2>
                <p>La satisfacción de nuestros clientes es nuestra mayor recompensa.</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-rating">★★★★★</div>
                    <p class="testimonial-text">
                        "Increíble experiencia. El equipo de Cristina Spa me hizo sentir como una reina
                        en mi día de boda. El peinado y maquillaje quedaron perfectos, ¡exactamente
                        como lo soñé!"
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">MR</div>
                        <div class="author-info">
                            <strong>María Rodríguez</strong>
                            <span>Novia - Paquete Premium</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-rating">★★★★★</div>
                    <p class="testimonial-text">
                        "Llevo más de 5 años siendo cliente fiel. La calidad del servicio y los
                        productos que usan son de primera. Siempre salgo feliz con mi nuevo look."
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">CP</div>
                        <div class="author-info">
                            <strong>Carolina Pérez</strong>
                            <span>Cliente frecuente - Colorimetría</span>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-rating">★★★★★</div>
                    <p class="testimonial-text">
                        "Mi hija tuvo sus XV años y el resultado fue espectacular. Todo el equipo
                        fue muy profesional y atento. ¡Gracias por hacer de ese día algo inolvidable!"
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">LM</div>
                        <div class="author-info">
                            <strong>Laura Mendoza</strong>
                            <span>Mamá de quinceañera</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @php
    $categorias = [
        [
            'id'      => 'colorimetria',
            'nombre'  => 'Colorimetría',
            'overlay' => 'Balayage Premium',
            'fotos'   => [
                'CRISTINA SPA FOTOS_23.jpg',
                'CRISTINA SPA FOTOS_24.jpg',
                'CRISTINA SPA FOTOS_25.jpg',
                'CRISTINA SPA FOTOS_26.jpg',
                'CRISTINA SPA FOTOS_27.jpg',
                'CRISTINA SPA FOTOS_28.jpg',
            ],
        ],
        [
            'id'      => 'novia',
            'nombre'  => 'Novia',
            'overlay' => 'Peinado Nupcial',
            'fotos'   => ['novia_1.jpg', 'novia_2.jpg', 'novia_3.jpg'],
        ],
        [
            'id'      => 'spa',
            'nombre'  => 'Spa',
            'overlay' => 'Masaje Relajante',
            'fotos'   => ['spa_1.jpg', 'spa_2.jpg', 'spa_3.jpg'],
        ],
        [
            'id'      => 'corte',
            'nombre'  => 'Corte',
            'overlay' => 'Corte Moderno',
            'fotos'   => [
                'CRISTINA SPA FOTOS_2.jpg',
                'CRISTINA SPA FOTOS_29.jpg',
                'CRISTINA SPA FOTOS_3.jpg',
                'CRISTINA SPA FOTOS_30.jpg',
                'CRISTINA SPA FOTOS_31.jpg',
                'CRISTINA SPA FOTOS_4.jpg',
            ],
        ],
        [
            'id'      => 'manicura',
            'nombre'  => 'Manicura',
            'overlay' => 'Nail Art Premium',
            'fotos'   => ['manicura_1.jpg', 'manicura_2.jpg', 'manicura_3.jpg'],
        ],
        [
            'id'      => 'quinceañera',
            'nombre'  => 'Quinceañera',
            'overlay' => 'Look de Gala',
            'fotos'   => [
                'CRISTINA SPA FOTOS_22.jpg',
                'CRISTINA SPA FOTOS_32.jpg',
                'CRISTINA SPA FOTOS_33.jpg',
                'CRISTINA SPA FOTOS_34.jpg',
                'CRISTINA SPA FOTOS_35.jpg',
                'CRISTINA SPA FOTOS_36.jpg',
                'CRISTINA SPA FOTOS_37.jpg',
                'CRISTINA SPA FOTOS_38.jpg',
                'CRISTINA SPA FOTOS_39.jpg',
                'CRISTINA SPA FOTOS_40.jpg',
            ],
        ],
    ];
    @endphp

    <!-- Gallery Section -->
    <section id="galeria" class="gallery">
        <div class="container">
            <div class="services-intro">
                <span class="section-label">Nuestro Trabajo</span>
                <h2 class="section-title section-title-center">Galería</h2>
                <p>Una muestra de las transformaciones y momentos especiales que creamos cada día.</p>
            </div>
            <div class="gallery-grid">
                @foreach($categorias as $cat)
                @php
                    $urls = array_map(
                        fn($f) => asset('images/galeria/' . $cat['id'] . '/' . rawurlencode($f)),
                        $cat['fotos']
                    );
                @endphp
                <div class="gallery-item"
                     data-title="{{ $cat['nombre'] }}"
                     data-overlay="{{ $cat['overlay'] }}"
                     data-images="{{ json_encode(array_values($urls), JSON_UNESCAPED_SLASHES) }}">
                    <img src="{{ $urls[0] }}" alt="{{ $cat['nombre'] }}" loading="lazy">
                    <div class="gallery-overlay">
                        <span>{{ $cat['overlay'] }}</span>
                        <span class="gallery-count">{{ count($cat['fotos']) }} foto{{ count($cat['fotos']) > 1 ? 's' : '' }}</span>
                    </div>
                    <div class="gallery-caption">
                        <span class="gallery-caption-label">{{ $cat['nombre'] }}</span>
                        <span class="gallery-caption-desc">{{ $cat['overlay'] }}</span>
                    </div>
                    <div class="gallery-watermark">
                        <img src="{{ asset('images/logos/logo-cristina_spa.png') }}" alt="Cristina Spa">
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Gallery Panel Mosaico -->
    <div id="gallery-panel" class="gallery-panel" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="gallery-panel-header">
            <span class="gallery-panel-label">Nuestro Trabajo</span>
            <h3 id="gp-title"></h3>
            <button class="gallery-panel-close" id="gp-close" aria-label="Cerrar">&times;</button>
        </div>
        <div class="gallery-panel-mosaic" id="gp-mosaic"></div>
    </div>

    <!-- Contact Section -->
    <section id="contacto" class="contact">
        <div class="contact-inner">

            <!-- Left: info -->
            <div class="contact-info">
                <span class="section-label">Contáctanos</span>
                <h2 class="section-title">Reserva<br>tu Cita</h2>
                <p class="contact-description">
                    Escríbenos y nuestro equipo te confirmará la disponibilidad en tu sucursal preferida. Atención personalizada, calidad de primera.
                </p>

                <div class="contact-items">
                    <div class="contact-item">
                        <div class="contact-item-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div>
                            <p class="contact-item-label">Sucursales</p>
                            <p class="contact-item-value">Hotel Gloria · San Miguel<br>Calacoto · Achumani</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-item-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.76A16 16 0 0 0 16 16.76l1.12-1.12a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 24 18v-.08z"/></svg>
                        </div>
                        <div>
                            <p class="contact-item-label">Teléfonos</p>
                            <p class="contact-item-value">
                                <a href="https://wa.me/59176768796?text=Hola%2C%20quiero%20reservar%20una%20cita%20en%20Cristina%20Spa" target="_blank" rel="noopener">76768796</a>
                            </p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-item-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div>
                            <p class="contact-item-label">Horario general</p>
                            <p class="contact-item-value">Lun – Sáb: 9:00 – 20:00</p>
                        </div>
                    </div>
                </div>

                <a href="https://wa.me/59176768796?text=Hola%2C%20quiero%20reservar%20una%20cita%20en%20Cristina%20Spa" target="_blank" rel="noopener noreferrer" class="contact-wa-btn">
                    <svg viewBox="0 0 32 32" width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><path d="M16.004 0h-.008C7.174 0 0 7.176 0 16c0 3.5 1.128 6.744 3.046 9.378L1.054 31.29l6.156-1.97A15.89 15.89 0 0016.004 32C24.826 32 32 24.822 32 16S24.826 0 16.004 0zm9.316 22.594c-.39 1.1-1.932 2.012-3.156 2.28-.838.178-1.932.32-5.618-1.208-4.714-1.952-7.75-6.744-7.986-7.058-.228-.314-1.874-2.494-1.874-4.756 0-2.262 1.186-3.374 1.608-3.834.39-.424 1.032-.618 1.646-.618.198 0 .376.01.536.018.422.018.634.042.912.708.346.832 1.186 2.89 1.29 3.102.104.212.202.49.078.782-.114.294-.212.424-.424.66-.212.236-.414.416-.626.672-.198.228-.42.472-.18.912.24.432 1.068 1.762 2.294 2.854 1.578 1.406 2.906 1.842 3.318 2.044.314.154.69.132.94-.13.314-.332.702-.882 1.098-1.424.282-.386.638-.434.98-.294.346.132 2.192 1.034 2.568 1.222.376.19.628.284.72.44.09.156.09.896-.3 1.994z"/></svg>
                    Escribir por WhatsApp
                </a>
            </div>

            <!-- Right: form -->
            <div class="contact-form-wrap">
                <form class="contact-form" id="reservaForm">
                    <p class="contact-form-eyebrow">Solicitud de cita</p>
                    <h3 class="contact-form-title">¿Lista para transformarte?</h3>

                    <div class="form-row">
                        <div class="form-field">
                            <label>Nombre completo</label>
                            <input type="text" name="nombre" placeholder="Tu nombre" required>
                        </div>
                        <div class="form-field">
                            <label>Teléfono / WhatsApp</label>
                            <input type="tel" name="telefono" placeholder="Ej. 7xxxxxxx" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-field">
                            <label>Sucursal</label>
                            <select name="sucursal" required>
                                <option value="">Selecciona sucursal</option>
                                <option value="Hotel Gloria">Hotel Gloria – Zona Central</option>
                                <option value="San Miguel">San Miguel – Zona Sur</option>
                                <option value="Calacoto">Calacoto – Zona Sur</option>
                                <option value="Achumani">Achumani – Zona Sur</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label>Servicio</label>
                            <select name="servicio" required>
                                <option value="">Selecciona un servicio</option>
                                <optgroup label="Peluquería">
                                    <option>Corte de Cabello</option>
                                    <option>Colorimetría</option>
                                    <option>Balayage</option>
                                    <option>Tratamiento Capilar</option>
                                </optgroup>
                                <optgroup label="Spa & Bienestar">
                                    <option>Masaje Relajante</option>
                                    <option>Masaje Terapéutico</option>
                                    <option>Tratamiento Corporal</option>
                                </optgroup>
                                <optgroup label="Estética">
                                    <option>Manicura</option>
                                    <option>Pedicura</option>
                                    <option>Extensiones de Pestañas</option>
                                    <option>Diseño de Cejas</option>
                                </optgroup>
                                <optgroup label="Paquetes Especiales">
                                    <option>Paquete Novia</option>
                                    <option>Paquete Quinceañera</option>
                                    <option>Evento Especial</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div class="form-field">
                        <label>Comentarios <span class="form-optional">(opcional)</span></label>
                        <textarea name="mensaje" placeholder="Fecha preferida, alguna solicitud especial…" rows="3"></textarea>
                    </div>

                    <button type="submit" class="submit-button">
                        <svg viewBox="0 0 32 32" width="18" height="18" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><path d="M16.004 0h-.008C7.174 0 0 7.176 0 16c0 3.5 1.128 6.744 3.046 9.378L1.054 31.29l6.156-1.97A15.89 15.89 0 0016.004 32C24.826 32 32 24.822 32 16S24.826 0 16.004 0zm9.316 22.594c-.39 1.1-1.932 2.012-3.156 2.28-.838.178-1.932.32-5.618-1.208-4.714-1.952-7.75-6.744-7.986-7.058-.228-.314-1.874-2.494-1.874-4.756 0-2.262 1.186-3.374 1.608-3.834.39-.424 1.032-.618 1.646-.618.198 0 .376.01.536.018.422.018.634.042.912.708.346.832 1.186 2.89 1.29 3.102.104.212.202.49.078.782-.114.294-.212.424-.424.66-.212.236-.414.416-.626.672-.198.228-.42.472-.18.912.24.432 1.068 1.762 2.294 2.854 1.578 1.406 2.906 1.842 3.318 2.044.314.154.69.132.94-.13.314-.332.702-.882 1.098-1.424.282-.386.638-.434.98-.294.346.132 2.192 1.034 2.568 1.222.376.19.628.284.72.44.09.156.09.896-.3 1.994z"/></svg>
                        Enviar por WhatsApp
                    </button>
                </form>
            </div>

        </div>
    </section>

    <script>
    (function() {
        document.getElementById('reservaForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var nombre   = this.nombre.value.trim();
            var telefono = this.telefono.value.trim();
            var sucursal = this.sucursal.value;
            var servicio = this.servicio.value;
            var mensaje  = this.mensaje.value.trim();

            var text = '¡Hola! Quiero reservar una cita en Cristina Spa.\n\n'
                + '👤 Nombre: ' + nombre + '\n'
                + '📱 Teléfono: ' + telefono + '\n'
                + '📍 Sucursal: ' + sucursal + '\n'
                + '💆 Servicio: ' + servicio;
            if (mensaje) text += '\n📝 Comentarios: ' + mensaje;

            window.open('https://wa.me/59176768796?text=' + encodeURIComponent(text), '_blank');
        });
    })();
    </script>

    <!-- WhatsApp Floating Button -->
    <script>
    // Package tabs
    (function () {
        var tabs   = document.querySelectorAll('.pkg-tab');
        var panels = document.querySelectorAll('.pkg-panel');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) { t.classList.remove('active'); });
                panels.forEach(function (p) { p.classList.remove('active'); });
                tab.classList.add('active');
                document.getElementById(tab.dataset.panel).classList.add('active');
            });
        });
    })();

    (function () {
        var panel   = document.getElementById('gallery-panel');
        var gpTitle = document.getElementById('gp-title');
        var gpMosaic = document.getElementById('gp-mosaic');
        var logoUrl  = "{{ asset('images/logos/logo-cristina_spa.png') }}";

        function openPanel(images, title, overlay) {
            gpTitle.textContent = title;
            gpMosaic.innerHTML = '';
            images.forEach(function (src) {
                var wrapper = document.createElement('div');
                wrapper.className = 'mosaic-item';

                var img = document.createElement('img');
                img.src = src;
                img.alt = title;
                img.loading = 'lazy';

                var caption = document.createElement('div');
                caption.className = 'gallery-caption';
                caption.innerHTML =
                    '<span class="gallery-caption-label">' + title + '</span>' +
                    '<span class="gallery-caption-desc">' + overlay + '</span>';

                var wm = document.createElement('div');
                wm.className = 'gallery-watermark';
                wm.innerHTML = '<img src="' + logoUrl + '" alt="Cristina Spa">';

                wrapper.appendChild(img);
                wrapper.appendChild(caption);
                wrapper.appendChild(wm);
                gpMosaic.appendChild(wrapper);
            });
            panel.classList.add('active');
            panel.setAttribute('aria-hidden', 'false');
            panel.scrollTop = 0;
            document.body.style.overflow = 'hidden';
        }

        function closePanel() {
            panel.classList.remove('active');
            panel.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        document.getElementById('gp-close').addEventListener('click', closePanel);
        panel.addEventListener('click', function (e) { if (e.target === panel) closePanel(); });
        document.addEventListener('keydown', function (e) {
            if (panel.classList.contains('active') && e.key === 'Escape') closePanel();
        });

        document.querySelectorAll('.gallery-item[data-images]').forEach(function (item) {
            item.addEventListener('click', function () {
                openPanel(JSON.parse(this.dataset.images), this.dataset.title, this.dataset.overlay);
            });
        });
    })();
    </script>

    <!-- ── Branch Modal ── -->
    <div class="bmodal-overlay" id="branchModal" aria-hidden="true">
        <div class="bmodal" role="dialog" aria-modal="true">

            <button class="bmodal-close" id="branchModalClose" aria-label="Cerrar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>

            <!-- Left: info -->
            <div class="bmodal-info">
                <span class="bmodal-num" id="bmodalNum"></span>
                <span class="bmodal-zone" id="bmodalZone"></span>
                <h2 class="bmodal-name" id="bmodalName"></h2>

                <div class="bmodal-detail">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <div>
                        <p class="bmodal-label">Horario de atención</p>
                        <p class="bmodal-value" id="bmodalHours"></p>
                    </div>
                </div>

                <div class="bmodal-detail">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.76A16 16 0 0 0 16 16.76l1.12-1.12a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 24 18v-.08z"/></svg>
                    <div>
                        <p class="bmodal-label">Teléfonos</p>
                        <div class="bmodal-phones" id="bmodalPhones"></div>
                    </div>
                </div>

                <a class="bmodal-gmaps-btn" id="bmodalGmapsBtn" target="_blank" rel="noopener noreferrer">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Abrir en Google Maps
                </a>
            </div>

            <!-- Right: map -->
            <div class="bmodal-map">
                <iframe id="bmodalIframe" src="" frameborder="0" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>

        </div>
    </div>

    <script>
    (function() {
        var branches = {
            gloria: {
                num: '', zone: 'Zona Central', name: 'Hotel Gloria',
                hours: 'Lun – Sáb: 9:00 – 20:00',
                phones: [{tel:'tel:+59122906962', label:'2906962'}, {tel:'tel:+59122770838', label:'2770838'}],
                share: 'https://share.google/YZBmY2INSRjvVPOwA',
                embed: 'https://maps.google.com/maps?q=Hotel+Gloria+La+Paz+Bolivia&output=embed&z=17'
            },
            sanmiguel: {
                num: '', zone: 'Zona Sur', name: 'San Miguel',
                hours: 'Mar – Sáb: 9:00 – 20:00',
                phones: [{tel:'tel:+59122773147', label:'2773147'}, {tel:'tel:+59122770838', label:'2770838'}],
                share: 'https://share.google/zqx1Q9LdyQo1F0xLW',
                embed: 'https://maps.google.com/maps?q=Cristina+Spa+San+Miguel+La+Paz+Bolivia&output=embed&z=17'
            },
            calacoto: {
                num: '', zone: 'Zona Sur', name: 'Calacoto',
                hours: 'Lun – Sáb: 9:00 – 20:00',
                phones: [{tel:'tel:+59122770838', label:'2770838'}],
                share: 'https://share.google/AjGQMWwxCCluXj0Ej',
                embed: 'https://maps.google.com/maps?q=Cristina+Spa+Calacoto+La+Paz+Bolivia&output=embed&z=17'
            },
            achumani: {
                num: '', zone: 'Zona Sur', name: 'Achumani',
                hours: 'Mar – Sáb: 9:00 – 20:00',
                phones: [{tel:'tel:+59122770838', label:'2770838'}],
                share: 'https://share.google/haFHDhdFR7DASz92z',
                embed: 'https://maps.google.com/maps?q=Cristina+Spa+Achumani+La+Paz+Bolivia&output=embed&z=17'
            }
        };

        var overlay  = document.getElementById('branchModal');
        var iframe   = document.getElementById('bmodalIframe');
        var closeBtn = document.getElementById('branchModalClose');

        window.openBranchModal = function(id) {
            var b = branches[id];
            if (!b) return;

            document.getElementById('bmodalNum').textContent   = b.num;
            document.getElementById('bmodalZone').textContent  = b.zone;
            document.getElementById('bmodalName').textContent  = b.name;
            document.getElementById('bmodalHours').textContent = b.hours;

            var phonesEl = document.getElementById('bmodalPhones');
            phonesEl.innerHTML = b.phones.map(function(p) {
                return '<a href="' + p.tel + '">' + p.label + '</a>';
            }).join('');

            document.getElementById('bmodalGmapsBtn').href = b.share;
            iframe.src = b.embed;

            overlay.classList.add('active');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        };

        function closeBranchModal() {
            overlay.classList.remove('active');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            iframe.src = '';
        }

        closeBtn.addEventListener('click', closeBranchModal);
        overlay.addEventListener('click', function(e) { if (e.target === overlay) closeBranchModal(); });
        document.addEventListener('keydown', function(e) {
            if (overlay.classList.contains('active') && e.key === 'Escape') closeBranchModal();
        });
    })();
    </script>

    <a href="https://wa.me/59176768796?text=Hola%2C%20quiero%20reservar%20una%20cita%20en%20Cristina%20Spa"
        class="whatsapp-float" target="_blank" rel="noopener noreferrer" aria-label="Chat en WhatsApp">
        <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M16.004 0h-.008C7.174 0 0 7.176 0 16c0 3.5 1.128 6.744 3.046 9.378L1.054 31.29l6.156-1.97A15.89 15.89 0 0016.004 32C24.826 32 32 24.822 32 16S24.826 0 16.004 0zm9.316 22.594c-.39 1.1-1.932 2.012-3.156 2.28-.838.178-1.932.32-5.618-1.208-4.714-1.952-7.75-6.744-7.986-7.058-.228-.314-1.874-2.494-1.874-4.756 0-2.262 1.186-3.374 1.608-3.834.39-.424 1.032-.618 1.646-.618.198 0 .376.01.536.018.422.018.634.042.912.708.346.832 1.186 2.89 1.29 3.102.104.212.202.49.078.782-.114.294-.212.424-.424.66-.212.236-.414.416-.626.672-.198.228-.42.472-.18.912.24.432 1.068 1.762 2.294 2.854 1.578 1.406 2.906 1.842 3.318 2.044.314.154.69.132.94-.13.314-.332.702-.882 1.098-1.424.282-.386.638-.434.98-.294.346.132 2.192 1.034 2.568 1.222.376.19.628.284.72.44.09.156.09.896-.3 1.994z" />
        </svg>
        <span class="whatsapp-tooltip">¡Escríbenos!</span>
    </a>
@endsection
