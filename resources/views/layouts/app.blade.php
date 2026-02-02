<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cristina Spa - Belleza & Estilo')</title>

    <!-- SEO Meta Tags -->
    <meta name="description"
        content="Cristina Spa - Desde 2006, el mejor spa y salón de belleza en La Paz, Bolivia. Servicios de peluquería, spa, estética y paquetes especiales para novias y quinceañeras.">
    <meta name="keywords"
        content="spa la paz, peluquería la paz, salón de belleza bolivia, novias, quinceañeras, masajes, manicura, pedicura">
    <meta name="author" content="Cristina Spa">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Montserrat:wght@200;300;400;500;600&display=swap"
        rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">
                <h1>Cristina Spa</h1>
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
                @else
                    <li><a href="{{ route('login') }}" class="nav-login">Entrar</a></li>
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
                    <a href="https://www.facebook.com/cristinaspa" target="_blank" rel="noopener" aria-label="Facebook">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/cristinaspa" target="_blank" rel="noopener"
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
