@extends('apicola::Admin.layout')

@section('title', 'Producción • ' . ($selected ?: 'Colmenas') . ' • SENA APÍCOLA')

@section('breadcrumb')
    <a href="{{ route('apicola.admin.colmenas.index') }}">Colmenas</a>
    <span>/</span>
    <span style="font-weight: 700; color: #059669;" id="currentColmenaCode">{{ $selected ?: 'Todas las colmenas' }}</span>
    <span>/</span>
    <span class="active-page">Producción</span>
@endsection

@section('styles')
<style>
    .swal2-container { z-index: 999999 !important; }
    .prod-related-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1rem 1.25rem; margin-top: 1.25rem; }
    .prod-related-title { display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 0.6rem; }
    .prod-related-link { font-size: 0.82rem; font-weight: 700; color: #047857; text-decoration: none; }
    .prod-related-link:hover { text-decoration: underline; }
    .prod-related-row { display: flex; justify-content: space-between; gap: 10px; padding: 7px 0; border-top: 1px solid #f1f5f9; font-size: 0.85rem; color: #334155; }
    .prod-related-empty { font-size: 0.85rem; color: #64748b; font-style: italic; margin: 4px 0; }
</style>
@endsection

@section('content')
    <!-- Encabezado de la vista -->
    <div class="ap-view-header-row">
        <div>
            <span class="ap-view-subtitle-tag">
                COLMENAS / <span id="colmenaTag">{{ $selected ?: 'GENERAL' }}</span> / PRODUCCIÓN
            </span>
            <h1 class="ap-view-main-title">Producción</h1>
        </div>
        <div class="ap-user-chip">
            <div class="ap-user-chip-avatar" style="background: #047857;">
                {{ auth()->check() ? strtoupper(substr(auth()->user()->nickname ?? 'AP', 0, 2)) : 'AP' }}
            </div>
            <div class="ap-user-chip-info">
                <span class="ap-user-chip-name">{{ auth()->check() && auth()->user()->person ? trim(auth()->user()->person->first_name . ' ' . auth()->user()->person->first_last_name) : (auth()->check() ? auth()->user()->nickname : 'Apicultor') }}</span>
                <span class="ap-user-chip-role">Rol: {{ auth()->check() && auth()->user()->roles->first() ? auth()->user()->roles->first()->name : 'Apicultor' }}</span>
            </div>
        </div>
    </div>

    <!-- 3 Tarjetas de Métricas -->
    <div class="ap-metrics-grid cols-3">
        <div class="ap-metric-card border-green">
            <div class="ap-metric-card-title">PRODUCCIONES REGISTRADAS</div>
            <div class="ap-metric-card-num" id="metricTotalProd">0</div>
            <div class="ap-metric-card-sub" id="metricColmenaSub">{{ $selected ? 'Colmena ' . $selected : 'Todas las colmenas' }}</div>
        </div>

        <div class="ap-metric-card border-amber">
            <div class="ap-metric-card-title">MIEL ACUMULADA</div>
            <div class="ap-metric-card-num" id="metricMielTotal">0,0</div>
            <div class="ap-metric-card-sub">Kilogramos en el período</div>
        </div>

        <div class="ap-metric-card border-green">
            <div class="ap-metric-card-title">ÚLTIMA COSECHA</div>
            <div class="ap-metric-card-num" id="metricUltima" style="font-size: 1.6rem;">-</div>
            <div class="ap-metric-card-sub">Fecha del último registro</div>
        </div>
    </div>

    <!-- Sección: Historial de producción -->
    <div class="ap-list-section">
        <div class="ap-list-top-bar" style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:1rem;">
            <div>
                <h2 class="ap-list-title" id="historyColmenaTitle">{{ $selected ? 'Producción de la colmena ' . $selected : 'Producción de todas las colmenas' }}</h2>
                <p class="ap-list-desc">Una fila por cada cosecha de miel registrada.</p>
            </div>

            <!-- Selector de colmena rápido -->
            <div style="display:flex; align-items:center; gap:8px;">
                <label class="ap-field-label" style="margin-bottom:0;" for="selectChangeColmena">Cambiar colmena:</label>
                <select id="selectChangeColmena" class="ap-filter-select" onchange="changeActiveColmena(this.value)">
                    <option value="" {{ $selected === '' ? 'selected' : '' }}>Todas las colmenas</option>
                    @foreach($colmenas as $colm)
                        <option value="{{ $colm->code }}" {{ $selected == $colm->code ? 'selected' : '' }}>
                            {{ $colm->code }} ({{ $colm->apiario_name ?? 'Apiario' }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Fila de Controles: Filtro de fechas y Botón + Registrar producción -->
        <div class="ap-controls-bar" style="margin-top: 1rem;">
            <div class="ap-controls-left">
                <input type="date" id="filterFechaInicio" class="ap-date-input" value="" title="Fecha inicial" onclick="try{this.showPicker()}catch(e){}" style="cursor: pointer;">
                <input type="date" id="filterFechaFin" class="ap-date-input" value="" title="Fecha final" onclick="try{this.showPicker()}catch(e){}" style="cursor: pointer;">
                <button type="button" class="ap-btn-outline-green" onclick="filterProducciones()">
                    Filtrar
                </button>
            </div>

            <div class="ap-controls-right" style="display: flex; gap: 10px; align-items: center;">
                <button type="button" class="ap-btn-solid-green" onclick="openNewProduccionModal()">
                    <i class="fas fa-plus"></i> Registrar producción
                </button>
            </div>
        </div>

        <!-- Tabla de producciones -->
        <div class="ap-table-card">
            <div class="ap-table-scroll">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>FECHA</th>
                            <th>N.° REGISTRO</th>
                            <th>COLMENA</th>
                            <th>MIEL (KG)</th>
                            <th>RESPONSABLE</th>
                            <th style="text-align: center;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="produccionesTableBody">
                        <!-- Renderizado dinámicamente con JavaScript -->
                    </tbody>
                </table>
            </div>

            <div class="ap-table-footer-row">
                <div id="produccionesSummaryText">0 producciones registradas</div>
                <div id="mielHarvestSummaryText" style="font-weight: 700; color: #0f172a;">Total cosechado: 0,0 kg</div>
            </div>
        </div>

        <!-- Relación con las inspecciones de la misma colmena -->
        <div class="prod-related-card" id="relatedInspecciones">
            <!-- Renderizado por JavaScript -->
        </div>
    </div>
@endsection

@section('modals')
    <!-- ==================== MODAL: REGISTRAR / EDITAR PRODUCCIÓN ==================== -->
    <div class="ap-modal-backdrop" id="modalProduccionBackdrop">
        <div class="ap-modal-window" style="max-width: 520px;">
            <div class="ap-modal-body" style="padding: 2rem 2.2rem 1.5rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.4rem;">
                    <h2 class="ap-modal-title" id="modalProduccionTitle" style="font-size: 1.65rem; font-weight: 900; color: #0f172a; margin-bottom: 0;">Registrar producción</h2>
                    <button type="button" class="ap-modal-close-btn" onclick="closeProduccionModal()" title="Cerrar">&times;</button>
                </div>

                <form id="formProduccion" onsubmit="saveProduccionForm(event)">
                    <input type="hidden" id="modalProduccionId" value="">

                    <div class="ap-coords-row cols-2" style="margin-bottom: 0.9rem; gap: 10px;">
                        <div>
                            <label class="ap-field-label" for="modalProduccionColmena">COLMENA</label>
                            <select id="modalProduccionColmena" class="ap-field-input" required style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.90rem; color: #0f172a; background: #ffffff;">
                                @foreach($colmenas as $colm)
                                    @php $bloqueo = $colm->motivoNoRegistrable(); @endphp
                                    <option value="{{ $colm->code }}" {{ $bloqueo ? 'disabled' : '' }}>
                                        {{ $colm->code }}{{ $colm->status === 'Inactiva' ? ' (Inactiva)' : ($bloqueo ? ' (apiario desactivado)' : '') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="ap-field-label" for="modalProduccionFecha">FECHA</label>
                            <input type="date" id="modalProduccionFecha" class="ap-field-input" value="" max="{{ date('Y-m-d') }}" required onclick="try{this.showPicker()}catch(e){}" style="cursor: pointer;">
                        </div>
                    </div>

                    <div class="ap-form-field" style="margin-bottom: 0.9rem;">
                        <label class="ap-field-label" for="modalProduccionMiel">MIEL COSECHADA (KG)</label>
                        <input type="number" id="modalProduccionMiel" class="ap-field-input" min="0.1" step="0.1" placeholder="Ej: 4.5" required>
                    </div>

                    <div class="ap-form-field" style="margin-bottom: 0.9rem;">
                        <label class="ap-field-label" for="modalProduccionRespSelect">RESPONSABLE</label>
                        <select id="modalProduccionRespSelect" class="ap-field-input" onchange="handleProduccionRespSelect(this.value)" required style="width: 100%; padding: 10px 14px; border-radius: 10px; border: 1px solid #cbd5e1; font-size: 0.90rem; color: #0f172a; background: #ffffff;">
                        </select>

                        <div id="containerOtroRespProduccion" style="display: none; margin-top: 8px; background: #f0fdf4; padding: 10px; border-radius: 10px; border: 1px solid #bbf7d0;">
                            <label style="display: block; font-size: 0.72rem; font-weight: 700; color: #166534; margin-bottom: 4px; text-transform: uppercase;">Nombre del nuevo responsable:</label>
                            <div style="display: flex; gap: 6px;">
                                <input type="text" id="modalProduccionOtroRespInput" class="ap-field-input" placeholder="Escribe el nombre del nuevo responsable..." style="flex: 1; padding: 7px 10px; border-radius: 8px; border: 1px solid #10b981; font-size: 0.85rem; color: #0f172a; background: #ffffff;" onkeydown="if(event.key==='Enter'){event.preventDefault(); confirmarOtroResponsableProduccion();}">
                                <button type="button" onclick="confirmarOtroResponsableProduccion()" style="background: #047857; color: #ffffff; border: none; padding: 7px 12px; border-radius: 8px; font-size: 0.80rem; font-weight: 700; cursor: pointer; white-space: nowrap;">
                                    <i class="fas fa-plus"></i> Usar este
                                </button>
                            </div>
                            <span style="display: block; font-size: 0.70rem; color: #15803d; margin-top: 4px;">Solo nombres de personas (sin números).</span>
                        </div>
                        <input type="hidden" id="modalProduccionResponsable" value="">
                    </div>

                    <div class="ap-form-field" style="margin-bottom: 1.25rem;">
                        <label class="ap-field-label" for="modalProduccionObs">OBSERVACIONES (OPCIONAL)</label>
                        <textarea id="modalProduccionObs" class="ap-field-input" rows="3" maxlength="500" placeholder="Escribe una observación..." style="resize: vertical;"></textarea>
                    </div>

                    <div class="ap-modal-footer" style="padding:0; border:none;">
                        <button type="button" class="ap-btn-modal-cancel" onclick="closeProduccionModal()">Cancelar</button>
                        <button type="submit" class="ap-btn-modal-save" id="btnSaveProduccion">Guardar producción</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ==================== MODAL: DETALLE DE PRODUCCIÓN ==================== -->
    <div class="ap-modal-backdrop" id="modalProduccionDetailBackdrop">
        <div class="ap-modal-window" style="max-width: 500px;">
            <div class="ap-modal-body" style="padding: 2rem 2.2rem 1.5rem;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
                    <div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span class="ap-view-subtitle-tag" id="detailProdRegNum">PRO-0001</span>
                            <span id="detailProdEstado" class="ap-status-badge bueno" style="font-size:0.75rem; padding: 2px 9px;">Activa</span>
                        </div>
                        <h2 class="ap-modal-title" id="detailProdColmena" style="margin-bottom:0; margin-top:4px;">Producción</h2>
                    </div>
                    <button type="button" class="ap-modal-close-btn" onclick="closeProduccionDetailModal()" title="Cerrar">&times;</button>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:1.25rem; margin-bottom:1.25rem;">
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.75rem;">
                        <div>
                            <span class="ap-field-label">FECHA</span>
                            <strong id="detailProdFecha" style="font-size:0.9rem; color:#0f172a;">-</strong>
                        </div>
                        <div>
                            <span class="ap-field-label">MIEL COSECHADA</span>
                            <strong id="detailProdMiel" style="font-size:1.1rem; color:#059669;">0 kg</strong>
                        </div>
                        <div>
                            <span class="ap-field-label">RESPONSABLE</span>
                            <span id="detailProdResp" style="font-size:0.85rem; color:#334155; font-weight:600;">-</span>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <span class="ap-field-label">OBSERVACIONES</span>
                    <p id="detailProdNotas" style="font-size:0.85rem; color:#475569; line-height:1.5; background: #f1f5f9; padding: 10px 14px; border-radius: 8px;"></p>
                </div>

                <div class="ap-modal-footer" style="padding:0; border:none;">
                    <button type="button" class="ap-btn-modal-cancel" onclick="closeProduccionDetailModal()">Cerrar</button>
                    <button type="button" class="ap-btn-modal-save" id="btnEditFromProduccionDetail">Editar producción</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    window.ProduccionConfig = {
        selected: @json($selected),
        produccionesList: @json($produccionesInit),
        inspeccionesResumen: @json($inspeccionesResumen),
        responsables: @json($responsablesSugeridos ?? []),
        responsablesUrl: "{{ route('apicola.admin.responsables.store') }}",
        storeUrl: "{{ route('apicola.admin.producciones.store') }}",
        baseUrl: "{{ url('apicola/producciones') }}",
        inspeccionesBaseUrl: "{{ url('apicola/colmenas') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('modules/apicola/js/produccion.js') }}?v={{ time() }}"></script>
@endsection
