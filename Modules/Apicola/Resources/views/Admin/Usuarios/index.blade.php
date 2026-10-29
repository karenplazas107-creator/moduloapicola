@extends('apicola::Admin.layout')

@section('title', 'Gestión de Usuarios • SENA APÍCOLA')

@section('breadcrumb')
    <a href="{{ route('apicola.admin.dashboard') }}">Panel Principal</a>
    <span>/</span>
    <span class="active-page">Gestión de Usuarios</span>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('modules/apicola/css/usuarios.css') }}">
@endsection

@section('content')
    <!-- Encabezado de la vista -->
    <div class="ap-view-header-row">
        <div>
            <span class="ap-view-subtitle-tag">ADMINISTRACIÓN & SEGURIDAD</span>
            <h1 class="ap-view-main-title">Gestión de Usuarios</h1>
        </div>
        <div class="ap-user-chip">
            <div class="ap-user-chip-avatar" style="background: #047857;">
                {{ Auth::user()?->initials ?? 'AD' }}
            </div>
            <div class="ap-user-chip-info">
                <span class="ap-user-chip-name">
                    {{ Auth::user()?->full_name ?? (Auth::user()?->nickname ?? 'Administrador Apícola') }}
                </span>
                <span class="ap-user-chip-role" style="display:flex; align-items:center; gap:4px;">
                    Rol: {{ Auth::user()?->primary_role ?? 'Administrador Apícola' }}
                </span>
            </div>
        </div>
    </div>

    <!-- 4 Tarjetas de Métricas -->
    <div class="ap-metrics-grid">
        <!-- 1. Total Usuarios -->
        <div class="ap-metric-card border-green">
            <div class="ap-metric-card-title">TOTAL USUARIOS APÍCOLA</div>
            <div class="ap-metric-card-num" id="metricTotal">{{ $metrics['total'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">Cuentas vinculadas al módulo</div>
        </div>

        <!-- 2. Administradores -->
        <div class="ap-metric-card border-amber">
            <div class="ap-metric-card-title">ADMINISTRADORES</div>
            <div class="ap-metric-card-num" id="metricAdmins">{{ $metrics['admins'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">Acceso administrativo completo</div>
        </div>

        <!-- 3. Aprendices -->
        <div class="ap-metric-card border-sky">
            <div class="ap-metric-card-title">APRENDICES</div>
            <div class="ap-metric-card-num" id="metricAprendices">{{ $metrics['aprendices'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">Perfil de consulta y registro técnico</div>
        </div>

        <!-- 4. Usuarios Activos -->
        <div class="ap-metric-card border-teal">
            <div class="ap-metric-card-title">USUARIOS ACTIVOS</div>
            <div class="ap-metric-card-num" id="metricActivos">{{ $metrics['activos'] ?? 0 }}</div>
            <div class="ap-metric-card-sub">Con inicio de sesión habilitado</div>
        </div>
    </div>

    <!-- Sección de Listado de Usuarios -->
    <div class="ap-list-section">
        <div class="ap-list-header">
            <h2 class="ap-list-title">Directorio de Usuarios</h2>
            <p class="ap-list-desc">Consulta, crea y administra los usuarios y roles asignados a la unidad productiva apícola.</p>
        </div>

        <!-- Barra de Búsqueda y Filtros -->
        <div class="ap-controls-bar">
            <div class="ap-controls-left">
                <!-- Buscador -->
                <div class="ap-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" class="ap-search-input" placeholder="Buscar por nombre, documento o usuario...">
                </div>

                <!-- Filtro por Rol -->
                <select id="filterRole" class="ap-filter-dropdown">
                    <option value="all">Todos los roles</option>
                    <option value="apicola.admin">Administrador Apícola</option>
                    <option value="apicola.aprendiz">Aprendiz Apícola</option>
                </select>

                <!-- Filtro por Estado -->
                <select id="filterEstado" class="ap-filter-dropdown">
                    <option value="all">Todos los estados</option>
                    <option value="activo">Activos</option>
                    <option value="inactivo">Inactivos</option>
                </select>
            </div>

            <!-- Botón Crear Nuevo Usuario -->
            <button type="button" class="ap-btn-new-item" onclick="abrirModalCrear()">
                <i class="fas fa-user-plus"></i>
                <span>Nuevo Usuario</span>
            </button>
        </div>

        <!-- Tabla de Usuarios -->
        <div class="ap-table-wrap">
            <table class="ap-inv-table" id="usersTable">
                <thead>
                    <tr>
                        <th>Persona / Titular</th>
                        <th>Usuario</th>
                        <th>Correo Electrónico</th>
                        <th>Rol Apícola</th>
                        <th>Estado</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    @forelse($users as $u)
                        @php
                            $person = $u->person;
                            $fullName = $person ? ($person->first_name . ' ' . $person->first_last_name . ' ' . ($person->second_last_name ?? '')) : ($u->name ?? $u->nickname);
                            $formattedName = mb_convert_case(trim($fullName), MB_CASE_TITLE, "UTF-8");
                            $docType = trim($person?->document_type ?? '');
                            $docNum = trim($person?->document_number ?? '');
                            $docTypeShort = match(strtoupper($docType)) {
                                'CÉDULA DE CIUDADANÍA', 'CEDULA DE CIUDADANIA', 'CC' => 'CC',
                                'TARJETA DE IDENTIDAD', 'TI' => 'TI',
                                'CÉDULA DE EXTRANJERÍA', 'CEDULA DE EXTRANJERIA', 'CE' => 'CE',
                                'PASAPORTE', 'PAS' => 'PAS',
                                'PEP' => 'PEP',
                                default => ($docType ? (strlen($docType) > 4 ? substr($docType, 0, 4) : $docType) : '')
                            };
                            $isAdmin = $u->roles->contains('slug', 'apicola.admin');
                            $isAprendiz = $u->roles->contains('slug', 'apicola.aprendiz');
                            $isActive = !$u->trashed();
                            $initials = $u->initials ?? strtoupper(substr($u->nickname, 0, 2));
                            $appr = $person?->apprentices?->first();
                        @endphp
                        <tr data-id="{{ $u->id }}" data-role="{{ $isAdmin ? 'apicola.admin' : ($isAprendiz ? 'apicola.aprendiz' : 'other') }}" data-status="{{ $isActive ? 'activo' : 'inactivo' }}">
                            <td>
                                <div class="ap-user-cell">
                                    <div class="ap-user-row-avatar {{ $isAprendiz ? 'aprendiz' : ($isAdmin ? 'admin' : 'superadmin') }}">
                                        {{ $initials }}
                                    </div>
                                    <div class="ap-user-cell-info">
                                        <div class="ap-user-cell-name">
                                            {{ $formattedName }}
                                        </div>
                                        <div class="ap-user-cell-meta">
                                            @if($docNum)
                                                <span class="ap-user-doc-chip" title="{{ $docType }}: {{ $docNum }}">
                                                    <i class="far fa-id-card"></i>
                                                    <span>{{ $docTypeShort ? $docTypeShort . ' ' : '' }}{{ $docNum }}</span>
                                                </span>
                                            @else
                                                <span class="ap-user-doc-chip muted">
                                                    <i class="far fa-id-card"></i>
                                                    <span>Sin documento</span>
                                                </span>
                                            @endif

                                            @if($appr && $appr->course)
                                                <span class="ap-user-ficha-chip" title="Ficha: {{ $appr->course->code }} • {{ $appr->course->program?->name ?? 'Programa SENA' }}">
                                                    <i class="fas fa-graduation-cap"></i>
                                                    <span class="ficha-code">{{ $appr->course->code }}</span>
                                                    <span class="ficha-sep">•</span>
                                                    <span class="ficha-program">{{ Str::limit($appr->course->program?->name ?? 'SENA', 24) }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="ap-user-nickname-pill" title="Usuario: {{ $u->nickname }}">
                                    <span class="ap-at-prefix">@</span>
                                    <span class="ap-nickname-val">{{ $u->nickname }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="ap-user-email-wrap" title="{{ $u->email }}">
                                    <i class="far fa-envelope ap-email-icon"></i>
                                    <span class="ap-email-text">{{ $u->email }}</span>
                                </div>
                            </td>
                            <td>
                                @if($isAdmin)
                                    <span class="ap-role-badge admin">
                                        <i class="fas fa-shield-halved"></i>
                                        <span>Administrador Apícola</span>
                                    </span>
                                @elseif($isAprendiz)
                                    <span class="ap-role-badge aprendiz">
                                        <i class="fas fa-graduation-cap"></i>
                                        <span>Aprendiz Apícola</span>
                                    </span>
                                @else
                                    <span class="ap-role-badge superadmin">
                                        <i class="fas fa-user-gear"></i>
                                        <span>{{ $u->primary_role }}</span>
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($isActive)
                                    <span class="badge-status-pill activo">
                                        <span class="status-indicator-dot"></span>
                                        <span>Activo</span>
                                    </span>
                                @else
                                    <span class="badge-status-pill inactivo">
                                        <span class="status-indicator-dot"></span>
                                        <span>Inactivo</span>
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div class="ap-table-actions" style="justify-content: center;">
                                    <button type="button" class="ap-table-action-link" onclick="editarUsuario({{ $u->id }})" title="Editar usuario">
                                        <i class="fas fa-pen-to-square"></i>
                                    </button>
                                    @if(auth()->id() !== $u->id)
                                        @if($isActive)
                                            <button type="button" class="ap-table-action-link danger" onclick="toggleEstadoUsuario({{ $u->id }}, 'desactivar')" title="Desactivar usuario">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        @else
                                            <button type="button" class="ap-table-action-link activate" onclick="toggleEstadoUsuario({{ $u->id }}, 'activar')" title="Activar usuario">
                                                <i class="fas fa-user-check"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    <i class="fas fa-users" style="font-size: 2.2rem; color: #cbd5e1;"></i>
                                    <span style="font-weight: 600; font-size: 0.95rem; color: #334155;">No se encontraron usuarios registrados</span>
                                    <span style="font-size: 0.82rem; color: #94a3b8;">Usa el botón "Nuevo Usuario" para registrar un nuevo integrante.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==================== MODAL: REGISTRAR / EDITAR USUARIO ==================== -->
    <div class="ap-modal-backdrop" id="modalUsuarioBackdrop">
        <div class="ap-modal-window" style="max-width: 580px;">
            <div class="ap-modal-body" style="padding: 1.5rem 1.75rem 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.25rem;">
                    <div>
                        <span class="ap-view-subtitle-tag" id="modalTag">REGISTRO</span>
                        <h2 class="ap-modal-title" id="modalTitle" style="margin-bottom: 0.2rem; font-size: 1.35rem;">Registrar Nuevo Usuario</h2>
                    </div>
                    <button type="button" class="ap-modal-close-btn" onclick="cerrarModalUsuario()" title="Cerrar">&times;</button>
                </div>
                <p class="modal-subtitle" id="modalSubtitle" style="font-size: 0.83rem; color: #64748b; margin-bottom: 1.15rem;">
                    Ingresa los datos personales y de formación para conceder acceso a la plataforma.
                </p>

                <form id="formUsuario" onsubmit="guardarUsuario(event)">
                    @csrf
                    <input type="hidden" id="userId" name="userId" value="">
                    <input type="hidden" id="role_slug" name="role_slug" value="apicola.aprendiz">

                    <!-- Fila 1: Nombres y Apellidos -->
                    <div class="ap-form-grid-2" style="margin-bottom: 11px;">
                        <div class="ap-form-field" style="margin-bottom: 0;">
                            <label class="ap-field-label" for="first_name">NOMBRES *</label>
                            <input type="text" id="first_name" name="first_name" class="ap-field-input" placeholder="Ej. Carlos Andrés" required>
                        </div>
                        <div class="ap-form-field" style="margin-bottom: 0;">
                            <label class="ap-field-label" for="last_name">APELLIDOS *</label>
                            <input type="text" id="last_name" name="last_name" class="ap-field-input" placeholder="Ej. Gómez Pérez" required>
                        </div>
                    </div>

                    <!-- Fila 2: Documento de Identidad y Ficha -->
                    <div class="ap-form-grid-2" style="margin-bottom: 11px;">
                        <div class="ap-form-field" style="margin-bottom: 0;">
                            <label class="ap-field-label" for="document_number">DOCUMENTO IDENTIDAD *</label>
                            <div style="position: relative;">
                                <input type="number" id="document_number" name="document_number" class="ap-field-input" placeholder="Ej. 1075258901" required onblur="autoConsultarPersona()">
                                <span id="docSearchSpinner" style="display: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #059669;">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                            </div>
                        </div>
                        <div class="ap-form-field" style="margin-bottom: 0;">
                            <label class="ap-field-label" for="ficha" id="labelFicha">FICHA <span id="fichaReqBadge" style="color: #ef4444;">*</span></label>
                            <input type="text" id="ficha" name="ficha" list="listaFichas" class="ap-field-input" placeholder="Ej. 3406034" oninput="onFichaChanged()">
                            <datalist id="listaFichas">
                                @foreach($activeCourses ?? [] as $course)
                                    <option value="{{ $course->code }}">{{ $course->program ? $course->program->name : '' }}</option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    <!-- Fila 3: Programa y Correo -->
                    <div class="ap-form-grid-2" style="margin-bottom: 11px;">
                        <div class="ap-form-field" style="margin-bottom: 0;">
                            <label class="ap-field-label" for="programa" id="labelPrograma">PROGRAMA <span id="progReqBadge" style="color: #ef4444;">*</span></label>
                            <input type="text" id="programa" name="programa" list="listaProgramas" class="ap-field-input" placeholder="Ej. Gestión Agroempresarial">
                            <datalist id="listaProgramas">
                                @foreach($activeCourses ?? [] as $course)
                                    @if($course->program)
                                        <option value="{{ $course->program->name }}"></option>
                                    @endif
                                @endforeach
                            </datalist>
                        </div>
                        <div class="ap-form-field" style="margin-bottom: 0;">
                            <label class="ap-field-label" for="email">CORREO ELECTRÓNICO *</label>
                            <input type="email" id="email" name="email" class="ap-field-input" placeholder="Ej. usuario@sena.edu.co" required>
                        </div>
                    </div>

                    <!-- Fila 4: Contraseña y Rol -->
                    <div class="ap-form-grid-2" style="margin-bottom: 12px;">
                        <div class="ap-form-field" style="margin-bottom: 0;">
                            <label class="ap-field-label" for="password" id="labelPassword">CONTRASEÑA *</label>
                            <div style="position: relative;">
                                <input type="password" id="password" name="password" class="ap-field-input" placeholder="Mínimo 6 caracteres" style="padding-right: 38px;" required>
                                <button type="button" onclick="togglePasswordVisibility()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; padding: 4px;">
                                    <i class="fas fa-eye" id="iconEye"></i>
                                </button>
                            </div>
                            <div id="passwordHint" style="font-size: 0.72rem; color: #64748b; margin-top: 3px; display: none;">
                                Dejar en blanco para conservar actual.
                            </div>
                        </div>
                        <div class="ap-form-field" style="margin-bottom: 0;">
                            <label class="ap-field-label">ROL DE ACCESO *</label>
                            <div class="ap-role-pill-group">
                                <button type="button" class="ap-role-pill active aprendiz" id="btnRoleAprendiz" onclick="seleccionarRolPill('apicola.aprendiz')">
                                    <i class="fas fa-graduation-cap"></i> Aprendiz
                                </button>
                                <button type="button" class="ap-role-pill" id="btnRoleAdmin" onclick="seleccionarRolPill('apicola.admin')">
                                    <i class="fas fa-shield-halved"></i> Administrador
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Alerta de SICA -->
                    <div id="alertPersonFound" class="ap-person-alert found" style="margin-bottom: 10px; display: none;">
                        <i class="fas fa-check-circle" style="font-size: 1rem;"></i>
                        <span id="textPersonFound" style="font-size: 0.8rem;"></span>
                    </div>

                    <!-- Botones del Modal -->
                    <div class="ap-modal-footer" style="padding: 1rem 0 0; margin-top: 0.5rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" class="ap-btn-modal-cancel" onclick="cerrarModalUsuario()">Cancelar</button>
                        <button type="submit" class="ap-btn-modal-save" style="background: #059669;" id="btnSubmitUsuario">
                            <i class="fas fa-check"></i> <span id="btnSubmitText">Registrar Usuario</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@php
    $coursesMap = [];
    foreach($activeCourses ?? [] as $c) {
        $coursesMap[$c->code] = $c->program?->name ?? '';
    }
@endphp
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.UsuariosConfig = {
        csrfToken: '{{ csrf_token() }}',
        routeStore: '{{ route("apicola.admin.usuarios.store") }}',
        routeSearchPerson: '{{ route("apicola.admin.usuarios.search-person") }}',
        baseUsersUrl: '{{ url("apicola/usuarios") }}',
        coursesMap: @json($coursesMap)
    };
</script>
<script src="{{ asset('modules/apicola/js/usuarios.js') }}?v={{ time() }}"></script>
@endsection
