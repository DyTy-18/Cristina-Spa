@extends('layouts.app')

@section('title', 'Nosotros — Cristina Spa | Desde 2006 | La Paz, Bolivia')
@section('meta_description', 'Conoce la historia de Cristina Spa: 18 años transformando la belleza en La Paz. Equipo de más de 50 profesionales, alianzas con L\'Oréal y Kérastase, y 3 sucursales de lujo.')

@section('content')

    <!-- Page Hero -->
    <section class="nosotros-hero">
        <div class="nosotros-hero-inner">
            <span class="section-label">Desde 2006</span>
            <h1 class="nosotros-hero-title">Más que belleza,<br>una experiencia de bienestar integral.</h1>
            <p class="nosotros-hero-sub">18 años transformando el estilo y la confianza de los paceños a través de la excelencia y la exclusividad.</p>
        </div>
    </section>

    <!-- Historia -->
    <section class="nosotros-ext">
        <div class="container">

            <!-- Historia & Fundadora -->
            <div class="nosotros-historia">
                <div class="nosotros-historia-text">
                    <span class="section-label">El Legado</span>
                    <h2 class="nosotros-hero-title">El sueño detrás de la marca</h2>
                    <p class="nosotros-p">
                        Cristina Spa no nació simplemente como un salón de belleza; nació de una visión clara de empoderamiento y sofisticación. En 2006, su fundadora <strong>Cristina Mamani</strong> inició este proyecto con la convicción de que la belleza debe ser tratada con maestría técnica y un servicio profundamente personalizado.
                    </p>
                    <p class="nosotros-p">
                        Formada en Marketing y experta en gestión estética, Cristina entendió desde el primer día que el éxito residía en la capacitación constante. Lo que comenzó como un equipo pequeño hace casi dos décadas, ha evolucionado bajo su liderazgo en una cadena referente en Bolivia. Hoy, con más de 50 profesionales altamente calificados, Cristina Spa es sinónimo de vanguardia, siendo pioneros en introducir protocolos internacionales y productos de élite en el mercado paceño.
                    </p>
                </div>
                <div class="nosotros-quote-block">
                    <blockquote class="nosotros-quote">
                        <p>"Nuestra misión no es solo transformar el exterior de nuestros clientes, sino brindarles un refugio de paz donde redescubran su mejor versión."</p>
                        <footer>— Cristina Mamani, <cite>Directora &amp; Fundadora</cite></footer>
                    </blockquote>
                </div>
            </div>

            <!-- Cifras -->
            <div class="nosotros-stats">
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
                <div class="stat-item">
                    <span class="stat-number">10k+</span>
                    <span class="stat-label">Clientes Satisfechos</span>
                </div>
            </div>

            <!-- Pilares -->
            <div class="nosotros-pilares">
                <span class="section-label" style="display:block;text-align:center;margin-bottom:0.5rem;">Nuestros Diferenciales</span>
                <h2 class="nosotros-hero-title section-title-center" style="margin-bottom:2.5rem;">Por qué elegirnos</h2>
                <div class="pilares-grid">
                    <div class="pilar-card">
                        <div class="pilar-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <h3 class="pilar-title">18 Años de Trayectoria</h3>
                        <p class="pilar-desc">Una historia de éxito y estabilidad que garantiza resultados probados y clientes que regresan generación tras generación.</p>
                    </div>
                    <div class="pilar-card">
                        <div class="pilar-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        </div>
                        <h3 class="pilar-title">Alianzas Premium</h3>
                        <p class="pilar-desc">Trabajamos exclusivamente con marcas líderes a nivel mundial: <strong>L'Oréal Professionnel</strong> y <strong>Kérastase</strong>, garantizando productos de élite en cada servicio.</p>
                    </div>
                    <div class="pilar-card">
                        <div class="pilar-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <h3 class="pilar-title">Equipo Especializado</h3>
                        <p class="pilar-desc">Un staff de más de 50 expertos en constante actualización sobre las últimas tendencias de París y Nueva York para traerte lo mejor del mundo.</p>
                    </div>
                    <div class="pilar-card">
                        <div class="pilar-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <h3 class="pilar-title">Presencia Estratégica</h3>
                        <p class="pilar-desc">Tres sucursales diseñadas para tu comodidad: <strong>Central</strong>, <strong>Hotel Gloria</strong> e <strong>Irpavi — Megacenter</strong>, en los mejores puntos de La Paz.</p>
                    </div>
                </div>
            </div>

            <!-- Misión / Visión -->
            <div class="nosotros-mv">
                <span class="section-label" style="display:block;text-align:center;margin-bottom:0.5rem;">Filosofía Corporativa</span>
                <h2 class="nosotros-hero-title section-title-center" style="margin-bottom:2.5rem;">Nuestro Compromiso</h2>
                <div class="mv-grid">
                    <div class="mv-card">
                        <div class="mv-badge">Misión</div>
                        <p>Brindar servicios de belleza y bienestar integral con estándares de clase mundial, utilizando tecnología de punta y productos premium para superar las expectativas de cada cliente.</p>
                    </div>
                    <div class="mv-card">
                        <div class="mv-badge">Visión</div>
                        <p>Ser el referente indiscutible del cuidado personal en Bolivia, inspirando confianza y bienestar en cada rincón del país a través de la innovación constante.</p>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="nosotros-cta">
                <h3 class="nosotros-cta-title">Tu transformación comienza aquí</h3>
                <p class="nosotros-cta-sub">Te invitamos a vivir la experiencia Cristina Spa en cualquiera de nuestras sucursales.</p>
                <a href="{{ route('home') }}#contacto" class="cta-button">Reservar una Cita</a>
            </div>

        </div>
    </section>

@endsection
