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
                            <span>Lun - Sáb: 9:00 - 20:00</span>
                        </li>
                    </ul>
                    <div class="branch-phone">
                        <a href="tel:+59122906962">
                            📞 2906962
                        </a>
                    </div>
                    <div class="branch-phone">
                        <a href="tel:+59122770838">
                            📞 2770838
                        </a>
                    </div>
                    <a href="https://maps.google.com/?q=Hotel+Gloria+La+Paz+Bolivia" target="_blank"
                        class="branch-map-link">
                        📍 Ver en Google Maps
                    </a>
                </div>

                <!-- Mega Center -->
                {{-- <div class="branch-card">
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
                </div> --}}

                <!-- Obrajes -->
                {{-- <div class="branch-card">
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
                </div> --}}

                <!-- San Miguel -->
                <div class="branch-card">
                    <div class="branch-zone">Zona Sur</div>
                    <h3>San Miguel</h3>
                    
                    <ul class="branch-details">
                        <li>
                            <span>🕒</span>
                            <span>Mar - Sáb: 9:00 - 20:00</span>
                        </li>
                    </ul>
                    <div class="branch-phone">
                        <a href="tel:+59122773147">
                            📞 2773147
                        </a>
                    </div>
                    <div class="branch-phone">
                        <a href="tel:+59122770838">
                            📞 2770838
                        </a>
                    </div>
                    <a href="https://maps.google.com/?q=San+Miguel+Bloque+L+La+Paz+Bolivia" target="_blank"
                        class="branch-map-link">
                        📍 Ver en Google Maps
                    </a>
                </div>

                <!-- Calacoto -->
                <div class="branch-card">
                    <div class="branch-zone">Zona Sur</div>
                    <h3>Calacoto</h3>
                    <ul class="branch-details">
                        <li>
                            <span>🕒</span>
                            <span>Lun - Sáb: 9:00 - 20:00</span>
                        </li>
                    </ul>
                    <div class="branch-phone">
                        <a href="tel:+59122770838">
                            📞 2770838
                        </a>
                    </div>
                    <div class="branch-phone">
                        <a href="tel:+59122770838">
                            📞 2770838
                        </a>
                    </div>
                    <a href="https://maps.google.com/?q=Calacoto+La+Paz+Bolivia" target="_blank"
                        class="branch-map-link">
                        📍 Ver en Google Maps
                    </a>
                </div>

                <!-- Achumani -->
                <div class="branch-card">
                    <div class="branch-zone">Zona Sur</div>
                    <h3>Achumani</h3>
                    <ul class="branch-details">
                        <li>
                            <span>🕒</span>
                            <span>Mar - Sáb: 9:00 - 20:00</span>
                        </li>
                    </ul>
                    <div class="branch-phone">
                        <a href="tel:+59122770838">
                            📞 2770838
                        </a>
                    </div>
                    <div class="branch-phone">
                        <a href="tel:+59122770838">
                            📞 2770838
                        </a>
                    </div>
                    <a href="https://maps.google.com/?q=Achumani+La+Paz+Bolivia" target="_blank"
                        class="branch-map-link">
                        📍 Ver en Google Maps
                    </a>
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

    <a href="https://wa.me/59122906962?text=Hola%2C%20quiero%20reservar%20una%20cita%20en%20Cristina%20Spa"
        class="whatsapp-float" target="_blank" rel="noopener noreferrer" aria-label="Chat en WhatsApp">
        <svg viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M16.004 0h-.008C7.174 0 0 7.176 0 16c0 3.5 1.128 6.744 3.046 9.378L1.054 31.29l6.156-1.97A15.89 15.89 0 0016.004 32C24.826 32 32 24.822 32 16S24.826 0 16.004 0zm9.316 22.594c-.39 1.1-1.932 2.012-3.156 2.28-.838.178-1.932.32-5.618-1.208-4.714-1.952-7.75-6.744-7.986-7.058-.228-.314-1.874-2.494-1.874-4.756 0-2.262 1.186-3.374 1.608-3.834.39-.424 1.032-.618 1.646-.618.198 0 .376.01.536.018.422.018.634.042.912.708.346.832 1.186 2.89 1.29 3.102.104.212.202.49.078.782-.114.294-.212.424-.424.66-.212.236-.414.416-.626.672-.198.228-.42.472-.18.912.24.432 1.068 1.762 2.294 2.854 1.578 1.406 2.906 1.842 3.318 2.044.314.154.69.132.94-.13.314-.332.702-.882 1.098-1.424.282-.386.638-.434.98-.294.346.132 2.192 1.034 2.568 1.222.376.19.628.284.72.44.09.156.09.896-.3 1.994z" />
        </svg>
        <span class="whatsapp-tooltip">¡Escríbenos!</span>
    </a>
@endsection
