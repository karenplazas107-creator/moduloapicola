<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel de Administración • SENA APÍCOLA')</title>
    <link rel="icon" type="image/png" href="{{ asset('modules/apicola/img/logo.png') }}">

    <!-- Google Fonts: Caveat (Slogan artesanal) + Outfit + Plus Jakarta Sans + Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Leaflet CSS (Mapas interactivos de apiarios) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <link rel="stylesheet" href="{{ asset('modules/apicola/css/layout.css') }}">

    @yield('styles')
</head>
<body>

    <!-- ================= BARRA LATERAL (SIDEBAR) ================= -->
    <aside class="ap-sidebar" id="apSidebar">
        
        <!-- Encabezado del Logo SENA APÍCOLA -->
        <div class="ap-sidebar-header" style="gap: 14px; padding: 1.35rem 1.25rem;">
            <div class="ap-logo-hex" style="border-radius: 14px; border: 2px solid #fbbf24; background: #ffffff;">
                <img src="{{ asset('modules/apicola/img/bee_mascot.jpg') }}" 
                     alt="SENA Apícola" 
                     class="ap-logo-img"
                     onerror="this.onerror=null; this.src='{{ asset('modules/apicola/img/logo.png') }}';">
            </div>
            <div class="ap-brand-info">
                <div class="ap-brand-title" style="font-size: 1.18rem; font-weight: 800; letter-spacing: -0.3px;">
                    <span style="color: #ffffff;">SENA </span><span style="color: #fbbf24;">APÍCOLA</span>
                </div>
                <div class="ap-brand-sub" style="font-size: 0.72rem; color: #a7f3d0; letter-spacing: 0.5px; font-weight: 600;">SENA - LA ANGOSTURA</div>
            </div>
        </div>

        <!-- Menú de Navegación -->
        <ul class="ap-nav-list" id="apNavList">

            <!-- 1. Inicio -->
            <li class="ap-nav-item {{ request()->routeIs('apicola.admin.dashboard') || request()->routeIs('apicola.dashboard') ? 'active' : '' }}">
                <a href="{{ route('apicola.admin.dashboard') }}" class="ap-nav-link">
                    <span class="ap-nav-icon">
                        <i class="fas fa-house"></i>
                    </span>
                    <span>Inicio</span>
                </a>
            </li>

            <!-- 2. Usuarios -->
            <li class="ap-nav-item {{ request()->routeIs('apicola.admin.usuarios.*') ? 'active' : '' }}">
                <a href="{{ route('apicola.admin.usuarios.index') }}" class="ap-nav-link">
                    <span class="ap-nav-icon">
                        <i class="far fa-user"></i>
                    </span>
                    <span>Usuarios</span>
                </a>
            </li>

            <!-- 3. Apiarios -->
            <li class="ap-nav-item {{ request()->routeIs('apicola.admin.apiarios.*') ? 'active' : '' }}">
                <a href="{{ route('apicola.admin.apiarios.index') }}" class="ap-nav-link">
                    <span class="ap-nav-icon">
                        <i class="fas fa-location-dot"></i>
                    </span>
                    <span>Apiarios</span>
                </a>
            </li>

            @php
                $isInspeccionRoute = request()->routeIs('apicola.admin.inspecciones.*') 
                    || request()->routeIs('apicola.admin.colmenas.inspecciones')
                    || request()->is('*inspeccion*');
                $isColmenaDirect = (request()->routeIs('apicola.colmenas.*') || request()->routeIs('apicola.admin.colmenas.*') || request()->is('apicola/colmenas*')) && !$isInspeccionRoute;
            @endphp
            <!-- 4. Módulo: Colmenas -->
            <li class="ap-nav-item {{ $isColmenaDirect ? 'active' : '' }}">
                <a href="{{ route('apicola.admin.colmenas.index') }}" class="ap-nav-link">
                    <span class="ap-nav-icon">
                        <i class="fas fa-boxes-stacked"></i>
                    </span>
                    <span>Colmenas</span>
                </a>
                <!-- Submódulo: Producción e inspección -->
                <ul class="ap-sub-list">
                    <li>
                        <a href="{{ route('apicola.admin.inspecciones.index') }}" 
                           class="ap-sub-link {{ $isInspeccionRoute ? 'active-sub' : '' }}">
                            <i class="fas fa-clipboard-check" style="font-size: 0.78rem;"></i>
                            <span>Producción e inspección</span>
                        </a>
                    </li>
                </ul>
            </li>

            @php
                $isMovimientosRoute = request()->routeIs('apicola.admin.inventario.movimientos.*') 
                    || request()->is('apicola/inventario/movimientos*')
                    || request()->is('apicola/movimientos*');
                $isInventarioDirect = (request()->routeIs('apicola.admin.inventario.*') || request()->is('apicola/inventario*')) && !$isMovimientosRoute;
            @endphp
            <!-- 5. Inventario -->
            <li class="ap-nav-item {{ $isInventarioDirect ? 'active' : '' }}">
                <a href="{{ route('apicola.admin.inventario.index') }}" class="ap-nav-link">
                    <span class="ap-nav-icon">
                        <i class="fas fa-box-archive"></i>
                    </span>
                    <span>Inventario</span>
                </a>
                <!-- Submódulo: Movimientos -->
                <ul class="ap-sub-list">
                    <li>
                        <a href="{{ route('apicola.admin.inventario.movimientos.index') }}" 
                           class="ap-sub-link {{ $isMovimientosRoute ? 'active-sub' : '' }}">
                            <i class="fas fa-arrow-right-arrow-left" style="font-size: 0.78rem;"></i>
                            <span>Movimientos</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- 6. Calendario Floral -->
            <li class="ap-nav-item {{ request()->routeIs('apicola.admin.calendario.*') ? 'active' : '' }}">
                <a href="{{ route('apicola.admin.calendario.index') }}" class="ap-nav-link">
                    <span class="ap-nav-icon">
                        <i class="fas fa-calendar-days"></i>
                    </span>
                    <span>Calendario floral</span>
                </a>
            </li>

            <!-- 7. Reportes -->
            <li class="ap-nav-item {{ request()->routeIs('apicola.admin.reportes.*') ? 'active' : '' }}">
                <a href="{{ route('apicola.admin.reportes.index') }}" class="ap-nav-link">
                    <span class="ap-nav-icon">
                        <i class="fas fa-chart-column"></i>
                    </span>
                    <span>Reportes</span>
                </a>
            </li>
        </ul>

        <!-- Slogan Sostenible Artístico en el pie del Sidebar -->
        <div class="ap-sidebar-slogan-box">
            <span class="ap-slogan-handwritten">Juntos por una apicultura sostenible</span>
            <div class="ap-slogan-bee-deco">
                <i class="fas fa-seedling"></i>
                <span style="font-size: 0.75rem; letter-spacing: 1px;">SENA CEFA</span>
                <i class="fas fa-feather-pointed"></i>
            </div>
        </div>

        <!-- Pie de Usuario (Conectado al fondo) -->
        <div class="ap-sidebar-user">
            <div class="ap-user-left">
                <div class="ap-avatar-circle">
                    {{ Auth::user()?->initials ?? strtoupper(substr(Auth::user()?->nickname ?? 'A', 0, 2)) }}
                </div>
                <div class="ap-user-details">
                    <div class="ap-user-title" title="{{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Administrador') }}">
                        {{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Karen Michelle Plazas') }}
                    </div>
                    <div class="ap-user-sub">
                        <i class="fas fa-shield-alt" style="color: #34d399;"></i> {{ Auth::user()?->primary_role ?? 'Administrador Apícola' }}
                    </div>
                </div>
            </div>
            <a href="{{ route('logout', ['redirect' => route('apicola.welcome')]) }}" class="ap-btn-exit" title="Cerrar sesión">
                <i class="fas fa-arrow-right-from-bracket"></i>
            </a>
        </div>
    </aside>

    <!-- ================= CONTENIDO PRINCIPAL ================= -->
    <div class="ap-main-area">

        <!-- Barra Superior ApiGestión -->
        <header class="ap-top-nav" style="justify-content: flex-end;">

            <div class="ap-top-right" style="gap: 1.15rem;">
                <!-- Perfil del Administrador -->
                <div class="ap-nav-profile-pill" title="Cuenta activa">
                    <div class="ap-nav-profile-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="ap-nav-profile-meta">
                        <span class="ap-nav-profile-name">
                            {{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Karen Michelle Plazas') }}
                        </span>
                        <span class="ap-nav-profile-role">
                            {{ Auth::user()?->primary_role ?? 'Administrador' }}
                        </span>
                    </div>
                    <i class="fas fa-chevron-down" style="font-size: 0.72rem; color: #94a3b8; margin-left: 2px;"></i>
                </div>
            </div>
        </header>

        <!-- Contenido Específico de cada Vista -->
        <main class="ap-inner-content">
            @yield('content')
        </main>
    </div>

    <!-- Modales inyectados por cada submódulo -->
    @yield('modals')

    <!-- Toast Alert Flotante -->
    <div class="ap-toast-alert" id="apToast">
        <i class="fas fa-check-circle" style="color: #34d399; font-size: 1.15rem;"></i>
        <span id="apToastText">Acción completada con éxito</span>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts Globales -->
    <script src="{{ asset('modules/apicola/js/layout.js') }}"></script>

    @yield('scripts')
</body>
</html>
