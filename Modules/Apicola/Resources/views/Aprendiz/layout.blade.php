<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal del Aprendiz • SENA APÍCOLA')</title>
    <link rel="icon" type="image/png" href="{{ asset('modules/apicola/img/logo.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <link rel="stylesheet" href="{{ asset('modules/apicola/css/aprendiz-layout.css') }}">
    @yield('styles')
</head>
<body>

    <!-- SIDEBAR APRENDIZ -->
    <aside class="ap-sidebar">
        <div class="ap-sidebar-header">
            <div class="ap-logo-hex">
                <img src="{{ asset('modules/apicola/img/logo.png') }}" 
                     alt="SENA Apícola" 
                     class="ap-logo-img"
                     onerror="this.onerror=null; this.src='{{ asset('apicola-assets/assets/img/logo-apicola-bee-original.png') }}';">
            </div>
            <div class="ap-brand-info">
                <div class="ap-brand-title">
                    <span style="color: #ffffff; font-weight: 900;">SENA</span>
                    <span>APÍCOLA</span>
                </div>
                <div class="ap-brand-sub">PORTAL DEL APRENDIZ</div>
            </div>
        </div>

        <ul class="ap-nav-list">
            @php
                $isInspeccionRoute = request()->routeIs('apicola.aprendiz.inspecciones.*') 
                    || request()->routeIs('apicola.aprendiz.colmenas.inspecciones')
                    || request()->is('*inspeccion*');
                $isColmenaDirect = (request()->routeIs('apicola.aprendiz.colmenas.*') || request()->is('apicola/aprendiz/colmenas*')) && !$isInspeccionRoute;
            @endphp
            <!-- Módulo: Colmenas -->
            <li class="ap-nav-item {{ $isColmenaDirect ? 'active' : '' }}">
                <a href="{{ route('apicola.aprendiz.colmenas.index') }}" class="ap-nav-link">
                    <span class="ap-nav-icon"><i class="fas fa-boxes-stacked"></i></span>
                    <span>Colmenas</span>
                </a>
                <!-- Submódulo: Producción e inspección -->
                <ul class="ap-sub-list">
                    <li>
                        <a href="{{ route('apicola.aprendiz.inspecciones.index') }}" 
                           class="ap-sub-link {{ $isInspeccionRoute ? 'active-sub' : '' }}">
                            <i class="fas fa-clipboard-check" style="font-size: 0.78rem;"></i>
                            <span>Producción e inspección</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>

        <div class="ap-sidebar-user">
            <div class="ap-user-left">
                <div class="ap-avatar-circle" style="background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%);">
                    {{ Auth::user()?->initials ?? strtoupper(substr(Auth::user()?->nickname ?? 'AP', 0, 2)) }}
                </div>
                <div class="ap-user-details">
                    <div class="ap-user-title" title="{{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Aprendiz SENA') }}">
                        {{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Aprendiz SENA') }}
                    </div>
                    <div class="ap-user-sub">
                        <i class="fas fa-graduation-cap" style="color: #fbbf24;"></i> Rol: Aprendiz Apícola
                    </div>
                </div>
            </div>
            <a href="{{ route('logout', ['redirect' => route('apicola.welcome')]) }}" class="ap-btn-exit" title="Cerrar sesión">
                <i class="fas fa-arrow-right-from-bracket"></i>
            </a>
        </div>
    </aside>

    <!-- MAIN CONTAINER -->
    <div class="ap-main-wrapper">
        <header class="ap-topbar">
            <div class="ap-breadcrumb">
                <i class="fas fa-home" style="font-size:0.8rem; color:#94a3b8;"></i>
                <a href="{{ route('apicola.welcome') }}">Inicio</a>
                <span>/</span>
                <a href="{{ route('apicola.aprendiz.dashboard') }}">Módulo Apícola</a>
                <span>/</span>
                @yield('breadcrumb')
            </div>

            <div class="ap-topbar-actions">
                <div class="ap-pill-climate">
                    <i class="fas fa-sun"></i>
                    <span>29°C Campoalegre • Floración Activa</span>
                </div>
                <a href="{{ route('apicola.welcome', ['public' => 1]) }}" class="ap-btn-public-portal" target="_blank">
                    <i class="fas fa-arrow-up-right-from-square"></i> Portal Público
                </a>
            </div>
        </header>

        <main class="ap-content-container">
            @yield('content')
        </main>
    </div>

    <!-- Modales del Módulo -->
    @yield('modals')

    <!-- Scripts Base -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('modules/apicola/js/aprendiz-layout.js') }}"></script>
    @yield('scripts')
</body>
</html>
