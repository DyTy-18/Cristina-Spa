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
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Cristina Spa - Belleza, Estilo & Bienestar | Desde 2006 | La Paz, Bolivia')</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('meta_description', 'Cristina Spa — Desde 2006, el spa y salón de belleza de referencia en La Paz, Bolivia. Peluquería, coloración, spa, estética, manicura, pedicura y paquetes especiales para novias y quinceañeras. 6 sucursales.')">
    <meta name="keywords" content="spa la paz, peluquería la paz, salón de belleza bolivia, cristina spa, coloración cabello, balayage, tinte, manicura, pedicura, masajes, facial, paquete novias, quinceañeras, belleza la paz, zona sur la paz, obrajes, calacoto, san miguel">
    <meta name="author" content="Cristina Spa">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="@yield('canonical', config('app.url'))">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="@yield('canonical', config('app.url'))">
    <meta property="og:title" content="@yield('title', 'Cristina Spa - Belleza, Estilo & Bienestar | Desde 2006 | La Paz, Bolivia')">
    <meta property="og:description" content="@yield('meta_description', 'Cristina Spa — Desde 2006, el spa y salón de belleza de referencia en La Paz, Bolivia. Peluquería, coloración, spa, estética, manicura, pedicura y paquetes especiales para novias y quinceañeras. 6 sucursales.')">
    <meta property="og:image" content="@yield('og_image', asset('images/logos/logo-cristina_spa.png'))">
    <meta property="og:image:alt" content="Cristina Spa - La Paz, Bolivia">
    <meta property="og:locale" content="es_BO">
    <meta property="og:site_name" content="Cristina Spa">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Cristina Spa - Belleza, Estilo & Bienestar | Desde 2006 | La Paz, Bolivia')">
    <meta name="twitter:description" content="@yield('meta_description', 'Cristina Spa — Desde 2006, el spa y salón de belleza de referencia en La Paz, Bolivia. Peluquería, coloración, spa, estética, manicura, pedicura y paquetes especiales para novias y quinceañeras. 6 sucursales.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/logos/logo-cristina_spa.png'))">

    <!-- Schema.org JSON-LD: Local Business -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BeautySalon",
        "name": "Cristina Spa",
        "description": "Spa y sal\u00f3n de belleza de referencia en La Paz, Bolivia desde 2006. Peluquer\u00eda, coloraci\u00f3n, spa, est\u00e9tica, manicura, pedicura y paquetes especiales para novias y quincea\u00f1eras.",
        "url": "{{ config('app.url') }}",
        "logo": "{{ asset('images/logos/logo-cristina_spa.png') }}",
        "image": "{{ asset('images/logos/logo-cristina_spa.png') }}",
        "telephone": "+591-2-2906962",
        "email": "info@cristinaspa.com",
        "foundingDate": "2006",
        "address": {
            "@@type": "PostalAddress",
            "addressLocality": "La Paz",
            "addressCountry": "BO"
        },
        "geo": {
            "@@type": "GeoCoordinates",
            "latitude": "-16.5000",
            "longitude": "-68.1193"
        },
        "priceRange": "$$",
        "openingHours": "Mo-Sa 09:00-20:00",
        "sameAs": [
            "https://www.facebook.com/CristinaSpaOficial",
            "https://www.instagram.com/cristinaspaoficial",
            "https://www.tiktok.com/@cristinaspalapaz"
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
                <li><a href="{{ route('nosotros') }}">Nosotros</a></li>
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
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/cristinaspaoficial" target="_blank" rel="noopener"
                        aria-label="Instagram">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/>
                        </svg>
                    </a>
                    <a href="https://www.tiktok.com/@@cristinaspa" target="_blank" rel="noopener" aria-label="TikTok">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
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
                    <a href="https://wa.me/59176768796?text=Hola%2C%20quiero%20reservar%20una%20cita%20en%20Cristina%20Spa" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:4px;color:inherit;text-decoration:none;">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                        76768796
                    </a><br>
                    <a href="mailto:info@cristinaspa.com" style="color:inherit;text-decoration:none;">info@cristinaspa.com</a><br><br>
                    <strong>Zona Central:</strong><br>
                    Hotel Gloria<br><br>
                    <strong>Zona Sur:</strong><br>
                    San Miguel · Calacoto · Achumani
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
