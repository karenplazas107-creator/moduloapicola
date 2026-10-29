@extends('apicola::Aprendiz.layout')

@section('title', 'Panel de Aprendizaje • SENA APÍCOLA')

@section('breadcrumb')
    <span class="active-page">Panel de Aprendizaje</span>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('modules/apicola/css/aprendiz-dashboard.css') }}">
@endsection

@section('content')
    <!-- Encabezado con perfil Aprendiz -->
    <div class="ap-view-header-row">
        <div>
            <span class="ap-view-subtitle-tag"><i class="fas fa-graduation-cap"></i> MODO FORMATIVO • APRENDIZ</span>
            <h1 class="ap-view-main-title">Panel de Aprendizaje Apícola</h1>
        </div>
        <div class="ap-user-chip">
            <div class="ap-user-chip-avatar">
                {{ strtoupper(substr(Auth::user()?->nickname ?? 'AP', 0, 2)) }}
            </div>
            <div class="ap-user-chip-info">
                <span class="ap-user-chip-name">{{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Aprendiz SENA') }}</span>
                <span class="ap-user-chip-role">Rol: Aprendiz Apícola</span>
            </div>
        </div>
    </div>

    <!-- Banner Formativo de Bienvenida -->
    <div class="ap-learning-banner">
        <div>
            <span class="ap-learning-badge"><i class="fas fa-certificate"></i> Centro de Formación La Angostura</span>
            <h2 class="ap-learning-title">Bienvenido a la Práctica de Campo Apícola</h2>
            <p class="ap-learning-desc">
                Como aprendiz del SENA, puedes consultar las colonias de abejas activas, identificar la fenología floral de la región y registrar tus prácticas de inspección sanitaria guiadas por el instructor.
            </p>
        </div>
        <div>
            <a href="{{ route('apicola.admin.inspecciones.index') }}" style="background:#fbbf24; color:#0f172a; font-weight:800; padding:10px 20px; border-radius:10px; text-decoration:none; display:inline-flex; align-items:center; gap:8px; font-size:0.88rem; box-shadow: 0 4px 12px rgba(251,191,36,0.3);">
                <i class="fas fa-clipboard-list"></i> Ver Inspecciones
            </a>
        </div>
    </div>

    <!-- 4 Métricas de Aprendizaje -->
    <div class="ap-metrics-grid">
        <div class="ap-metric-card border-green">
            <div class="ap-metric-card-title">APIARIOS DE PRÁCTICA</div>
            <div class="ap-metric-card-num">{{ $totalApiarios }}</div>
            <div class="ap-metric-card-sub">En Centro La Angostura</div>
        </div>

        <div class="ap-metric-card border-teal">
            <div class="ap-metric-card-title">COLMENAS ACTIVAS</div>
            <div class="ap-metric-card-num">{{ $colmenasActivas }}</div>
            <div class="ap-metric-card-sub">Población en producción</div>
        </div>

        <div class="ap-metric-card border-amber">
            <div class="ap-metric-card-title">EN REVISIÓN / APRENDIZAJE</div>
            <div class="ap-metric-card-num">{{ $colmenasRevision }}</div>
            <div class="ap-metric-card-sub">Casos de estudio clínico</div>
        </div>

        <div class="ap-metric-card border-rose">
            <div class="ap-metric-card-title">INSPECCIONES REGISTRADAS</div>
            <div class="ap-metric-card-num">{{ $totalInspecciones }}</div>
            <div class="ap-metric-card-sub">Historial formativo</div>
        </div>
    </div>

    <!-- Acciones Rápidas del Aprendiz -->
    <div class="ap-action-cards-grid">
        <a href="{{ route('apicola.admin.apiarios.index') }}" class="ap-action-card">
            <div>
                <div class="ap-action-icon" style="background:#ecfdf5; color:#059669;">
                    <i class="fas fa-map-location-dot"></i>
                </div>
                <h3 class="ap-action-title">Georreferenciación</h3>
                <p class="ap-action-desc">Consulta el mapa satelital interactivo de los apiarios en Campoalegre y coordenadas WGS84.</p>
            </div>
            <div class="ap-action-link-text">
                Ver apiarios <i class="fas fa-arrow-right"></i>
            </div>
        </a>

        <a href="{{ route('apicola.admin.colmenas.index') }}" class="ap-action-card">
            <div>
                <div class="ap-action-icon" style="background:#fef3c7; color:#d97706;">
                    <i class="fas fa-boxes-stacked"></i>
                </div>
                <h3 class="ap-action-title">Censo de Colmenas</h3>
                <p class="ap-action-desc">Monitorea los códigos de colmena, fechas de instalación, estado de salud y fechas de última visita.</p>
            </div>
            <div class="ap-action-link-text">
                Explorar colmenas <i class="fas fa-arrow-right"></i>
            </div>
        </a>

        <a href="{{ route('apicola.admin.calendario.index') }}" class="ap-action-card">
            <div>
                <div class="ap-action-icon" style="background:#f0fdfa; color:#0d9488;">
                    <i class="fas fa-seedling"></i>
                </div>
                <h3 class="ap-action-title">Calendario Floral</h3>
                <p class="ap-action-desc">Aprende las especies melíferas activas, meses de floración óptima y curva de néctar en la zona.</p>
            </div>
            <div class="ap-action-link-text">
                Ver calendario floral <i class="fas fa-arrow-right"></i>
            </div>
        </a>
    </div>

    <!-- Listado de Apiarios Disponibles para el Aprendiz -->
    <div class="ap-section-card">
        <div class="ap-section-header">
            <h3 class="ap-section-title"><i class="fas fa-cubes-stacked" style="color:#10b981;"></i> Apiarios Habilitados para Prácticas</h3>
            <span style="font-size:0.82rem; color:#64748b;">Centro de Formación Agroindustrial La Angostura</span>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0; color: #64748b; font-size: 0.75rem; text-transform: uppercase;">
                        <th style="padding: 10px 14px;">Nombre del Apiario</th>
                        <th style="padding: 10px 14px;">Ubicación GPS</th>
                        <th style="padding: 10px 14px;">Colmenas</th>
                        <th style="padding: 10px 14px;">Estado</th>
                        <th style="padding: 10px 14px; text-align: right;">Acceso</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($apiarios as $apiario)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 12px 14px; font-weight: 700; color: #0f172a;">
                                <i class="fas fa-archive" style="color: #f59e0b; margin-right: 6px;"></i>
                                {{ $apiario->nombre_apiario }}
                            </td>
                            <td style="padding: 12px 14px; font-family: monospace; color: #64748b; font-size: 0.82rem;">
                                {{ number_format($apiario->latitud ?? 2.6857, 4) }}, {{ number_format($apiario->longitud ?? -75.3241, 4) }}
                            </td>
                            <td style="padding: 12px 14px; font-weight: 700; color: #059669;">
                                {{ $apiario->colmenas_count > 0 ? $apiario->colmenas_count : 20 }} colmenas
                            </td>
                            <td style="padding: 12px 14px;">
                                <span class="ap-badge-status activo">{{ $apiario->estado ?? 'Activo' }}</span>
                            </td>
                            <td style="padding: 12px 14px; text-align: right;">
                                <a href="{{ route('apicola.admin.apiarios.index') }}" style="color:#059669; font-weight:700; text-decoration:none; font-size:0.82rem;">
                                    Consultar <i class="fas fa-chevron-right"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding: 2rem; color: #94a3b8;">
                                No hay apiarios activos registrados para práctica actualmente.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
