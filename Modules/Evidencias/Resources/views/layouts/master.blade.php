<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestión de Evidencias') — ERP SENA Empresa</title>

    {{-- Bootstrap 5.3 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --sena-green  : #39A900;
            --sena-dark   : #00131E;
            --sena-navy   : #003B5C;
            --sena-neon   : #7FD400;
            --ev-sidebar  : #00131E;
            --ev-sidebar-w: 260px;
        }

        /* ── Layout general ── */
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* ── Sidebar ── */
        #ev-sidebar {
            width: var(--ev-sidebar-w);
            min-height: 100vh;
            background: var(--ev-sidebar);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            transition: width .25s;
            display: flex;
            flex-direction: column;
        }

        #ev-sidebar .sidebar-brand {
            padding: 1.4rem 1.2rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }

        #ev-sidebar .sidebar-brand .brand-logo {
            width: 38px;
            height: 38px;
            background: var(--sena-green);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #fff;
            flex-shrink: 0;
        }

        #ev-sidebar .nav-link {
            color: rgba(255,255,255,.7);
            padding: .6rem 1.2rem;
            border-radius: 6px;
            margin: 2px 8px;
            transition: background .2s, color .2s;
            font-size: .9rem;
        }

        #ev-sidebar .nav-link:hover,
        #ev-sidebar .nav-link.active {
            background: var(--sena-green);
            color: #fff;
        }

        #ev-sidebar .nav-link i {
            width: 22px;
            text-align: center;
            margin-right: 8px;
        }

        #ev-sidebar .sidebar-section-label {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: rgba(255,255,255,.35);
            padding: .8rem 1.4rem .3rem;
        }

        /* ── Contenido principal ── */
        #ev-main {
            margin-left: var(--ev-sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Topbar ── */
        #ev-topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: .75rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        /* ── Tarjetas de resumen ── */
        .ev-stat-card {
            border: none;
            border-radius: 12px;
            padding: 1.4rem 1.2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,.07);
        }

        .ev-stat-card .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .ev-stat-card .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            line-height: 1;
        }

        .ev-stat-card .stat-label {
            font-size: .8rem;
            color: #64748b;
            margin-top: 2px;
        }

        /* ── Tabla ── */
        .ev-table thead th {
            background: #f8fafc;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #64748b;
            border-top: none;
            font-weight: 600;
        }

        .ev-table tbody tr:hover {
            background: #f0fdf4;
        }

        /* ── Footer ── */
        #ev-footer {
            margin-top: auto;
            padding: 1rem 1.5rem;
            font-size: .78rem;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            background: #fff;
        }

        /* ── Responsive: ocultar sidebar en móvil ── */
        @media (max-width: 768px) {
            #ev-sidebar { width: 0; overflow: hidden; }
            #ev-main    { margin-left: 0; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ══════════════════ SIDEBAR ══════════════════ --}}
<nav id="ev-sidebar">
    {{-- Marca --}}
    <div class="sidebar-brand d-flex align-items-center gap-2">
        <div class="brand-logo">
            <i class="bi bi-shield-check"></i>
        </div>
        <div>
            <div class="text-white fw-bold" style="font-size:.92rem; line-height:1.1">Panel Evidencias</div>
            <div style="font-size:.7rem; color:rgba(255,255,255,.45)">ERP SENA Empresa</div>
        </div>
    </div>

    {{-- Menú principal --}}
    <div class="sidebar-section-label">Principal</div>
    <ul class="nav flex-column px-1">
        <li class="nav-item">
            <a href="{{ route('evidencias.index') }}"
               class="nav-link {{ request()->routeIs('evidencias.index') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('evidencias.create') }}"
               class="nav-link {{ request()->routeIs('evidencias.create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle"></i> Nueva Evidencia
            </a>
        </li>
    </ul>

    <div class="sidebar-section-label">Mis Evidencias</div>
    <ul class="nav flex-column px-1">
        <li class="nav-item">
            <a href="{{ route('evidencias.index') }}?estado=pendiente"
               class="nav-link {{ request()->is('evidencias*') && request()->get('estado') === 'pendiente' ? 'active' : '' }}">
                <i class="bi bi-hourglass-split text-warning"></i> Pendientes
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('evidencias.index') }}?estado=aprobada"
               class="nav-link {{ request()->get('estado') === 'aprobada' ? 'active' : '' }}">
                <i class="bi bi-check-circle text-success"></i> Aprobadas
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('evidencias.index') }}?estado=rechazada"
               class="nav-link {{ request()->get('estado') === 'rechazada' ? 'active' : '' }}">
                <i class="bi bi-x-circle text-danger"></i> Rechazadas
            </a>
        </li>
    </ul>

    {{-- Separador inferior + cerrar sesión --}}
    <div class="mt-auto p-3 border-top" style="border-color:rgba(255,255,255,.1) !important">
        <div class="d-flex align-items-center gap-2 mb-3">
            <div style="width:36px;height:36px;border-radius:50%;background:var(--sena-green);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.9rem;flex-shrink:0">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
            </div>
            <div>
                <div class="text-white" style="font-size:.82rem;line-height:1.2">{{ Auth::user()->name ?? 'Gestor' }}</div>
                <div style="font-size:.7rem;color:rgba(255,255,255,.45)">Gestor de Evidencias</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm w-100 text-start"
                    style="background:rgba(255,255,255,.07);color:rgba(255,255,255,.7);border:none">
                <i class="bi bi-box-arrow-left me-2"></i>Cerrar sesión
            </button>
        </form>
    </div>
</nav>

{{-- ══════════════════ CONTENIDO PRINCIPAL ══════════════════ --}}
<div id="ev-main">

    {{-- Topbar --}}
    <header id="ev-topbar" class="d-flex align-items-center justify-content-between">
        <div>
            <h6 class="mb-0 fw-semibold text-dark">@yield('page-title', 'Dashboard')</h6>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="font-size:.75rem">
                    <li class="breadcrumb-item">
                        <a href="{{ route('evidencias.index') }}" class="text-decoration-none" style="color:var(--sena-green)">
                            Evidencias
                        </a>
                    </li>
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill" style="background:var(--sena-green);font-size:.7rem">
                <i class="bi bi-shield-fill-check me-1"></i>Gestor Autorizado
            </span>
            <a href="{{ route('evidencias.create') }}" class="btn btn-sm text-white"
               style="background:var(--sena-green);border-color:var(--sena-green)">
                <i class="bi bi-plus-lg me-1"></i>Nueva
            </a>
        </div>
    </header>

    {{-- Mensajes flash --}}
    <div class="px-4 pt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show py-2" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Corrige los siguientes errores:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach($errors->all() as $error)
                        <li style="font-size:.875rem">{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- Zona de contenido principal --}}
    <main class="px-4 py-3 flex-grow-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer id="ev-footer" class="d-flex justify-content-between align-items-center">
        <span>&copy; {{ date('Y') }} ERP SENA Empresa — Panel de Gestión de Evidencias</span>
        <span>Usuario ID: {{ Auth::id() }}</span>
    </footer>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
