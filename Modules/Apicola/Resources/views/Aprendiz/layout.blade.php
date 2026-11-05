<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal del Aprendiz • SENA APÍCOLA')</title>
    <link rel="icon" type="image/png" href="{{ asset('modules/apicola/img/logo.png') }}?v={{ time() }}">

    <!-- Google Fonts: Tipografía oficial ERP SENA Empresa -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,700&family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Mapbox GL CSS & JS -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.33.0/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.33.0/mapbox-gl.js"></script>

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
                <img src="{{ asset('modules/apicola/img/logo.png') }}?v={{ time() }}" 
                     alt="SENA Apícola" 
                     class="ap-logo-img"
                     onerror="this.onerror=null; this.src='{{ asset('apicola-assets/assets/img/logo-apicola-bee-original.png') }}?v={{ time() }}';">
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

            <div class="ap-topbar-actions" style="display:flex; align-items:center; gap:0.85rem;">
                <div class="ap-pill-climate">
                    <i class="fas fa-sun"></i>
                    <span>29°C Campoalegre • Floración Activa</span>
                </div>
                <a href="{{ route('logout', ['redirect' => route('apicola.welcome')]) }}" class="ap-btn-logout-aprendiz" title="Cerrar sesión">
                    <i class="fas fa-arrow-right-from-bracket"></i>
                    <span>Cerrar sesión</span>
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
