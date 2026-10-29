@extends('apicola::Aprendiz.layout')

@section('title', 'Gestión de Colmenas • SENA APÍCOLA')

@section('breadcrumb')
    <span class="active-page">Gestión de colmenas</span>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('modules/apicola/css/aprendiz-colmenas.css') }}">
@endsection

@section('content')
    <!-- Encabezado de la vista con perfil aprendiz -->
    <div class="ap-view-header-row">
        <div>
            <span class="ap-view-subtitle-tag">COLMENAS</span>
            <h1 class="ap-view-main-title">Gestión de colmenas</h1>
        </div>
        <div class="ap-user-chip">
            <div class="ap-user-chip-avatar" style="background: #f59e0b;">
                {{ strtoupper(substr(Auth::user()?->nickname ?? 'AP', 0, 2)) }}
            </div>
            <div class="ap-user-chip-info">
                <span class="ap-user-chip-name">{{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Jhon Ramírez Ortiz') }}</span>
                <span class="ap-user-chip-role">Rol: Aprendiz Apícola</span>
            </div>
        </div>
    </div>

    <!-- 4 Tarjetas de Métricas -->
    <div class="ap-metrics-grid">
        <div class="ap-metric-card border-green">
            <div class="ap-metric-card-title">COLMENAS REGISTRADAS</div>
            <div class="ap-metric-card-num" id="colmenaMetricTotal">{{ $metrics['total'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">En apiarios activos</div>
        </div>

        <div class="ap-metric-card border-green">
            <div class="ap-metric-card-title">ACTIVAS</div>
            <div class="ap-metric-card-num" id="colmenaMetricActivas">{{ $metrics['activas'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">En producción</div>
        </div>

        <div class="ap-metric-card border-amber">
            <div class="ap-metric-card-title">EN REVISIÓN</div>
            <div class="ap-metric-card-num" id="colmenaMetricRevision">{{ $metrics['en_revision'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">Cría abierta / seguimiento</div>
        </div>

        <div class="ap-metric-card border-red">
            <div class="ap-metric-card-title">INACTIVAS</div>
            <div class="ap-metric-card-num" id="colmenaMetricInactivas">{{ $metrics['inactivas'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">No admiten visitas</div>
        </div>
    </div>

    <!-- Sección: Listado de Colmenas -->
    <div class="ap-list-section">
        <div class="ap-list-top-bar">
            <h2 class="ap-list-title">Listado de colmenas</h2>
            <p class="ap-list-desc">Toda colmena pertenece a un apiario. Solo se registran visitas a colmenas activas o en revisión.</p>
        </div>

        <!-- Fila de Controles: Búsqueda, Filtros y Botón Nuevo -->
        <div class="ap-controls-bar">
            <div class="ap-controls-left" style="max-width: 680px;">
                <div class="ap-search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="colmenaSearchInput" placeholder="Buscar por código..." oninput="filterColmenas()">
                </div>
                <select id="colmenaApiarioFilter" class="ap-filter-select" onchange="filterColmenas()">
                    <option value="all">Apiario: todos</option>
                    @foreach($apiarios as $ap)
                        <option value="{{ $ap->nombre_apiario }}">{{ $ap->nombre_apiario }}</option>
                    @endforeach
                </select>
                <select id="colmenaEstadoFilter" class="ap-filter-select" onchange="filterColmenas()">
                    <option value="all">Estado: todos</option>
                    <option value="Activa">Activa</option>
                    <option value="En revisión">En revisión</option>
                    <option value="Inactiva">Inactiva</option>
                </select>
            </div>
        </div>

        <!-- Tabla de Colmenas con columna TIPO DE ABEJA -->
        <div class="ap-table-card">
            <div class="ap-table-scroll">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>CÓDIGO</th>
                            <th>APIARIO</th>
                            <th>TIPO DE ABEJA</th>
                            <th>RESPONSABLE</th>
                            <th>INSTALACIÓN</th>
                            <th>ÚLTIMA VISITA</th>
                            <th>ESTADO</th>
                            <th style="text-align: center;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="colmenasTableBody">
                        <!-- Generado dinámicamente con JavaScript -->
                    </tbody>
                </table>
            </div>

            <div class="ap-table-footer-row">
                <div>
                    Mostrando <strong id="colmenasShownCount">8</strong> de <strong id="colmenasTotalCount">8</strong> colmenas
                </div>
                <div>
                    Las colmenas se mantienen activas o en revisión para su seguimiento formativo.
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="ap-toast" id="colmenaToast"></div>
@endsection

@section('modals')
    <!-- ==================== MODAL: DETALLE DE COLMENA ==================== -->
    <div class="ap-modal-backdrop" id="modalColmenaDetailBackdrop">
        <div class="ap-modal-window" style="max-width: 480px;">
            <div class="ap-modal-body">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                    <div>
                        <span class="ap-view-subtitle-tag" style="font-size:0.68rem;">DETALLE DE COLMENA</span>
                        <h2 class="ap-modal-title" id="colmenaDetailCode" style="margin-bottom:0;">COL-001</h2>
                    </div>
                    <button type="button" class="ap-modal-close-btn" onclick="closeColmenaDetailModal()" title="Cerrar">&times;</button>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:1.2rem; margin-bottom:1.2rem;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:0.75rem;">
                        <div>
                            <span class="ap-field-label">APIARIO</span>
                            <strong id="colmenaDetailApiario" style="font-size:0.88rem; color:#0f172a;">Apiario La Angostura</strong>
                        </div>
                        <div>
                            <span class="ap-field-label">ESTADO SANITARIO</span>
                            <span id="colmenaDetailStatusBadge" class="ap-status-badge activa">Activa</span>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:0.75rem;">
                        <div>
                            <span class="ap-field-label">TIPO DE ABEJA</span>
                            <span id="colmenaDetailTipoAbeja" style="font-size:0.86rem; color:#334155; font-weight: 600;">Apis mellifera</span>
                        </div>
                        <div>
                            <span class="ap-field-label">RESPONSABLE</span>
                            <span id="colmenaDetailResp" style="font-size:0.86rem; color:#334155;">Jhon Ramírez Ortiz</span>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                        <div>
                            <span class="ap-field-label">INSTALACIÓN</span>
                            <span id="colmenaDetailInstalacion" style="font-size:0.86rem; color:#64748b;">12/01/2026</span>
                        </div>
                        <div>
                            <span class="ap-field-label">ÚLTIMA VISITA</span>
                            <span id="colmenaDetailLastVisit" style="font-size:0.86rem; color:#059669; font-weight:700;">15/08/2026</span>
                        </div>
                    </div>
                </div>

                <p id="colmenaDetailNotas" style="font-size:0.84rem; color:#475569; line-height:1.5; margin-bottom:1.5rem;"></p>

                <div class="ap-modal-footer" style="justify-content:flex-end;">
                    <button type="button" class="ap-btn-modal-cancel" onclick="closeColmenaDetailModal()">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @php
        $colmenasFormatted = $colmenas->map(function($c) {
            return [
                'id' => $c->id,
                'code' => $c->code,
                'apiario' => $c->apiario_name,
                'tipoAbeja' => $c->tipo_abeja ?? 'Apis mellifera',
                'responsable' => $c->responsable,
                'instalacion' => $c->installation_date ? \Carbon\Carbon::parse($c->installation_date)->format('d/m/Y') : '',
                'instalacionRaw' => $c->installation_date ? \Carbon\Carbon::parse($c->installation_date)->format('Y-m-d') : '',
                'ultimaVisita' => $c->last_visit_date ? \Carbon\Carbon::parse($c->last_visit_date)->format('d/m/Y') : ($c->installation_date ? \Carbon\Carbon::parse($c->installation_date)->format('d/m/Y') : '-'),
                'ultimaVisitaRaw' => $c->last_visit_date ? \Carbon\Carbon::parse($c->last_visit_date)->format('Y-m-d') : '',
                'estado' => $c->status,
                'notas' => $c->notes ?? 'Sin observaciones.',
            ];
        });
    @endphp
<script>
    window.AprendizColmenasConfig = {
        colmenasList: @json($colmenasFormatted),
        baseUrl: "{{ url('apicola/aprendiz/colmenas') }}",
        storeUrl: "{{ route('apicola.aprendiz.colmenas.store') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('modules/apicola/js/aprendiz-colmenas.js') }}"></script>
@endsection
