@extends('evidencias::layouts.master')

@section('title', 'Dashboard de Evidencias')
@section('page-title', 'Dashboard de Evidencias')

@section('breadcrumb')
    <li class="breadcrumb-item active">Inicio</li>
@endsection

@section('content')

{{-- ══════════════════════════════════════════════
     TARJETAS DE RESUMEN
══════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">

    {{-- Total --}}
    <div class="col-6 col-xl-3">
        <div class="ev-stat-card bg-white">
            <div class="stat-icon" style="background:#eff6ff">
                <i class="bi bi-folder2-open" style="color:#3b82f6"></i>
            </div>
            <div>
                <div class="stat-value" style="color:#1e40af">{{ $total }}</div>
                <div class="stat-label">Total de evidencias</div>
            </div>
        </div>
    </div>

    {{-- Pendientes --}}
    <div class="col-6 col-xl-3">
        <div class="ev-stat-card bg-white">
            <div class="stat-icon" style="background:#fffbeb">
                <i class="bi bi-hourglass-split" style="color:#f59e0b"></i>
            </div>
            <div>
                <div class="stat-value" style="color:#b45309">{{ $pendientes }}</div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
    </div>

    {{-- Aprobadas --}}
    <div class="col-6 col-xl-3">
        <div class="ev-stat-card bg-white">
            <div class="stat-icon" style="background:#f0fdf4">
                <i class="bi bi-check-circle-fill" style="color:#22c55e"></i>
            </div>
            <div>
                <div class="stat-value" style="color:#15803d">{{ $aprobadas }}</div>
                <div class="stat-label">Aprobadas</div>
            </div>
        </div>
    </div>

    {{-- Rechazadas --}}
    <div class="col-6 col-xl-3">
        <div class="ev-stat-card bg-white">
            <div class="stat-icon" style="background:#fef2f2">
                <i class="bi bi-x-circle-fill" style="color:#ef4444"></i>
            </div>
            <div>
                <div class="stat-value" style="color:#b91c1c">{{ $rechazadas }}</div>
                <div class="stat-label">Rechazadas</div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     BARRA DE FILTROS Y BOTÓN NUEVO
══════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-2 px-3">
        <form method="GET" action="{{ route('evidencias.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0"
                           placeholder="Buscar por título…"
                           value="{{ request('q') }}">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="estado" class="form-select form-select-sm">
                    <option value="">— Todos los estados —</option>
                    <option value="pendiente"  {{ request('estado') === 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
                    <option value="aprobada"   {{ request('estado') === 'aprobada'   ? 'selected' : '' }}>Aprobada</option>
                    <option value="rechazada"  {{ request('estado') === 'rechazada'  ? 'selected' : '' }}>Rechazada</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">— Todos los tipos —</option>
                    <option value="documento" {{ request('tipo') === 'documento' ? 'selected' : '' }}>Documento</option>
                    <option value="imagen"    {{ request('tipo') === 'imagen'    ? 'selected' : '' }}>Imagen</option>
                    <option value="video"     {{ request('tipo') === 'video'     ? 'selected' : '' }}>Video</option>
                    <option value="otro"      {{ request('tipo') === 'otro'      ? 'selected' : '' }}>Otro</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm text-white flex-grow-1"
                        style="background:var(--sena-green);border-color:var(--sena-green)">
                    <i class="bi bi-funnel-fill"></i> Filtrar
                </button>
                @if(request()->hasAny(['q','estado','tipo']))
                    <a href="{{ route('evidencias.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════
     TABLA DE EVIDENCIAS
══════════════════════════════════════════════ --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-table me-2" style="color:var(--sena-green)"></i>
            Mis Evidencias
            <span class="badge rounded-pill ms-1" style="background:var(--sena-green);font-size:.7rem">
                {{ $evidencias->total() }}
            </span>
        </h6>
        <a href="{{ route('evidencias.create') }}" class="btn btn-sm text-white"
           style="background:var(--sena-green);border-color:var(--sena-green)">
            <i class="bi bi-plus-lg me-1"></i>Nueva Evidencia
        </a>
    </div>

    <div class="card-body p-0">
        @if($evidencias->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size:3rem;color:#cbd5e1"></i>
                <p class="mt-3 text-muted">No hay evidencias registradas aún.</p>
                <a href="{{ route('evidencias.create') }}" class="btn btn-sm text-white"
                   style="background:var(--sena-green);border-color:var(--sena-green)">
                    <i class="bi bi-plus-lg me-1"></i>Registrar primera evidencia
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 ev-table">
                    <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Archivo</th>
                            <th>Fecha de subida</th>
                            <th style="width:130px" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($evidencias as $evidencia)
                            <tr>
                                <td class="text-muted" style="font-size:.8rem">{{ $evidencia->id }}</td>

                                <td>
                                    <div class="fw-semibold" style="font-size:.9rem">{{ $evidencia->titulo }}</div>
                                    @if($evidencia->descripcion)
                                        <div class="text-muted" style="font-size:.75rem">
                                            {{ Str::limit($evidencia->descripcion, 60) }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:.75rem">
                                        @switch($evidencia->tipo)
                                            @case('documento') <i class="bi bi-file-earmark-text me-1"></i> @break
                                            @case('imagen')    <i class="bi bi-image me-1"></i> @break
                                            @case('video')     <i class="bi bi-camera-video me-1"></i> @break
                                            @default           <i class="bi bi-paperclip me-1"></i>
                                        @endswitch
                                        {{ ucfirst($evidencia->tipo) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-{{ $evidencia->estado_badge }} bg-opacity-15 text-{{ $evidencia->estado_badge }}"
                                          style="font-size:.78rem">
                                        @switch($evidencia->estado)
                                            @case('pendiente')  <i class="bi bi-hourglass-split me-1"></i> @break
                                            @case('aprobada')   <i class="bi bi-check-circle me-1"></i> @break
                                            @case('rechazada')  <i class="bi bi-x-circle me-1"></i> @break
                                        @endswitch
                                        {{ $evidencia->estado_label }}
                                    </span>
                                </td>

                                <td>
                                    @if($evidencia->archivo)
                                        <a href="{{ Storage::url($evidencia->archivo) }}"
                                           target="_blank"
                                           class="btn btn-link btn-sm p-0 text-decoration-none"
                                           style="color:var(--sena-green);font-size:.8rem">
                                            <i class="bi bi-download me-1"></i>Ver archivo
                                        </a>
                                    @else
                                        <span class="text-muted" style="font-size:.8rem">—</span>
                                    @endif
                                </td>

                                <td style="font-size:.82rem;color:#64748b">
                                    {{ $evidencia->created_at->format('d/m/Y H:i') }}
                                </td>

                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        {{-- Ver --}}
                                        <a href="{{ route('evidencias.show', $evidencia->id) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Ver detalle" style="padding:.25rem .5rem">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        {{-- Editar --}}
                                        <a href="{{ route('evidencias.edit', $evidencia->id) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Editar" style="padding:.25rem .5rem">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        {{-- Eliminar --}}
                                        <form method="POST"
                                              action="{{ route('evidencias.destroy', $evidencia->id) }}"
                                              onsubmit="return confirm('¿Eliminar esta evidencia? Esta acción no se puede deshacer.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Eliminar" style="padding:.25rem .5rem">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($evidencias->hasPages())
                <div class="px-3 py-3 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted" style="font-size:.8rem">
                        Mostrando {{ $evidencias->firstItem() }}–{{ $evidencias->lastItem() }}
                        de {{ $evidencias->total() }} registros
                    </span>
                    {{ $evidencias->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        @endif
    </div>
</div>

@endsection
