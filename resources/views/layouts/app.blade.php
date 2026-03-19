<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-NLLGX42L');</script>
    <!-- End Google Tag Manager -->

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $pageTitle = trim(strip_tags(View::yieldContent('title', 'Cristina Spa - Belleza, Estilo & Bienestar | Desde 2006 | La Paz, Bolivia')));
        $pageDescription = trim(strip_tags(View::yieldContent('meta_description', 'Cristina Spa — Desde 2006, el spa y salón de belleza de referencia en La Paz, Bolivia. Peluquería, coloración, spa, estética, manicura, pedicura y paquetes especiales para novias y quinceañeras. 6 sucursales.')));
        $canonicalUrl = trim(strip_tags(View::yieldContent('canonical', config('app.url') . request()->getPathInfo())));
        $ogImage = trim(strip_tags(View::yieldContent('og_image', asset('images/logos/logo-cristina_spa.png'))));
    @endphp

    <title>{{ $pageTitle }}</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $pageDescription }}">
    <meta name="keywords" content="spa la paz, peluquería la paz, salón de belleza bolivia, cristina spa, coloración cabello, balayage, tinte, manicura, pedicura, masajes, facial, paquete novias, quinceañeras, belleza la paz, zona sur la paz, obrajes, calacoto, san miguel">
    <meta name="author" content="Cristina Spa">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="Cristina Spa - La Paz, Bolivia">
    <meta property="og:locale" content="es_BO">
    <meta property="og:site_name" content="Cristina Spa">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <!-- Schema.org JSON-LD: Local Business -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BeautySalon",
        "name": "Cristina Spa",
        "description": "Spa y salón de belleza de referencia en La Paz, Bolivia desde 2006. Peluquería, coloración, spa, estética, manicura, pedicura y paquetes especiales para novias y quinceañeras.",
        "url": "{{ config('app.url') }}",
        "logo": "{{ asset('images/logos/logo-cristina_spa.png') }}",
        "image": "{{ asset('images/logos/logo-cristina_spa.png') }}",
        "telephone": "+591-2-2906962",
        "email": "info@cristinaspa.com",
        "foundingDate": "2006",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "La Paz",
            "addressCountry": "BO"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": "-16.5000",
            "longitude": "-68.1193"
        },
        "priceRange": "$$",
        "openingHours": "Mo-Sa 09:00-20:00",
        "sameAs": [
            "https://www.facebook.com/CristinaSpaOficial",
            "https://www.instagram.com/cristinaspaoficial",
            "https://www.tiktok.com/@cristinaspa"
        ]
    }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Nunito:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    @yield('head')
</head>

<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NLLGX42L"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <header class="header">
        <nav class="nav">
            <div class="logo">
                <a href="#inicio">
                    <img src="{{ asset('images/logos/logo-cristina_spa_black.png') }}" alt="Cristina Spa" class="logo-img">
                </a>
            </div>
            <ul class="nav-menu">
                <li><a href="#inicio">Inicio</a></li>
                <li><a href="#servicios">Servicios</a></li>
                <li><a href="#sucursales">Sucursales</a></li>
                <li><a href="#galeria">Galería</a></li>
                <li><a href="#contacto">Contacto</a></li>
                @auth
                    <li><a href="{{ route('admin.dashboard') }}" class="nav-login">Panel</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="nav-logout-btn">Salir</button>
                        </form>
                    </li>
                @endauth
            </ul>
            <div class="nav-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <div class="footer-main">
            <div class="footer-brand">
                <h3>Cristina Spa</h3>
                <p>
                    Desde 2006, transformando vidas a través de la belleza y el bienestar.
                    Tu santuario de cuidado personal en La Paz, Bolivia.
                </p>
                <div class="social-links">
                    <a href="https://www.facebook.com/CristinaSpaOficial" target="_blank" rel="noopener"
                        aria-label="Facebook">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/cristinaspaoficial" target="_blank" rel="noopener"
                        aria-label="Instagram">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" fill="none"
                                stroke="currentColor" stroke-width="2"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" stroke="currentColor"
                                stroke-width="2"></line>
                        </svg>
                    </a>
                    <a href="https://www.tiktok.com/@cristinaspa" target="_blank" rel="noopener" aria-label="TikTok">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z">
                            </path>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="footer-section">
                <h4>Servicios</h4>
                <ul>
                    <li><a href="#servicios">Peluquería</a></li>
                    <li><a href="#servicios">Spa & Bienestar</a></li>
                    <li><a href="#servicios">Estética</a></li>
                    <li><a href="#servicios">Facial</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Especiales</h4>
                <ul>
                    <li><a href="#contacto">Paquete Novias</a></li>
                    <li><a href="#contacto">Quinceañeras</a></li>
                    <li><a href="#contacto">Eventos</a></li>
                    <li><a href="#contacto">Empresas</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Contacto</h4>
                <p>
                    📞 2906962 (Central)<br>
                    ✉️ info@cristinaspa.com<br><br>
                    <strong>Zona Central:</strong><br>
                    Hotel Gloria<br><br>
                    <strong>Zona Sur:</strong><br>
                    Mega Center, Obrajes, San Miguel, Calacoto
                </p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} Cristina Spa. Todos los derechos reservados. | Desde 2006 en La Paz, Bolivia.
            </p>
        </div>
    </footer>

    <script src="{{ asset('js/script.js') }}"></script>
</body>

</html>
