<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Admin') - Cristina Spa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Montserrat:wght@200;300;400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
</head>

<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="sidebar-logo">Cristina Spa</h1>
                <p class="sidebar-subtitle">Panel Admin</p>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('admin.dashboard') }}"
                    class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="nav-icon">📊</span>
                    <span>Dashboard</span>
                </a>

                @if (auth()->user()->hasAnyPermission(['ver citas', 'crear citas']))
                    <a href="{{ route('admin.citas.index') }}"
                        class="nav-item {{ request()->routeIs('admin.citas.*') ? 'active' : '' }}">
                        <span class="nav-icon">📅</span>
                        <span>Citas</span>
                    </a>
                @endif

                @if (auth()->user()->hasPermissionTo('ver clientes'))
                    <a href="{{ route('admin.clientes.index') }}"
                        class="nav-item {{ request()->routeIs('admin.clientes.*') ? 'active' : '' }}">
                        <span class="nav-icon">👥</span>
                        <span>Clientes</span>
                    </a>
                @endif

                @if (auth()->user()->hasPermissionTo('ver servicios'))
                    <a href="{{ route('admin.servicios.index') }}"
                        class="nav-item {{ request()->routeIs('admin.servicios.*') ? 'active' : '' }}">
                        <span class="nav-icon">✂️</span>
                        <span>Servicios</span>
                    </a>
                @endif

                @if (auth()->user()->hasPermissionTo('ver empleados'))
                    <a href="{{ route('admin.empleados.index') }}"
                        class="nav-item {{ request()->routeIs('admin.empleados.*') ? 'active' : '' }}">
                        <span class="nav-icon">💼</span>
                        <span>Empleados</span>
                    </a>
                @endif

                @if (auth()->user()->hasPermissionTo('gestionar usuarios'))
                    <a href="{{ route('admin.usuarios.index') }}"
                        class="nav-item {{ request()->routeIs('admin.usuarios.*') ? 'active' : '' }}">
                        <span class="nav-icon">👤</span>
                        <span>Usuarios</span>
                    </a>
                @endif
            </nav>

            <div class="sidebar-footer">
                <div class="user-info">
                    <span class="user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    <div class="user-details">
                        <span class="user-name">{{ auth()->user()->name }}</span>
                        <span class="user-role">{{ auth()->user()->roles->first()?->name ?? 'Usuario' }}</span>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn" title="Cerrar sesión">
                        🚪
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">☰</button>
                    <h2 class="page-title">@yield('page-title', 'Dashboard')</h2>
                </div>
                <div class="header-right">
                    <a href="{{ route('home') }}" class="btn-view-site" target="_blank">
                        Ver Sitio ↗
                    </a>
                </div>
            </header>

            <div class="content-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
    @stack('scripts')
</body>

</html>
