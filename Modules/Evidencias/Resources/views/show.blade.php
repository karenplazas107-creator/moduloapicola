@extends('evidencias::layouts.master')

@section('title', 'Detalle — ' . $evidencia->titulo)
@section('page-title', 'Detalle de Evidencia')

@section('breadcrumb')
    <li class="breadcrumb-item active">#{{ $evidencia->id }}</li>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm">

            {{-- Encabezado con estado --}}
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-start gap-2">
                <div>
                    <h5 class="mb-1 fw-bold">{{ $evidencia->titulo }}</h5>
                    <span class="text-muted" style="font-size:.8rem">
                        ID #{{ $evidencia->id }} &nbsp;·&nbsp;
                        Subida el {{ $evidencia->created_at->format('d/m/Y \a \l\a\s H:i') }}
                    </span>
                </div>
                <span class="badge bg-{{ $evidencia->estado_badge }} bg-opacity-15 text-{{ $evidencia->estado_badge }} fs-6 mt-1"
                      style="font-size:.85rem !important; white-space:nowrap">
                    @switch($evidencia->estado)
                        @case('pendiente')  <i class="bi bi-hourglass-split me-1"></i> @break
                        @case('aprobada')   <i class="bi bi-check-circle-fill me-1"></i> @break
                        @case('rechazada')  <i class="bi bi-x-circle-fill me-1"></i> @break
                    @endswitch
                    {{ $evidencia->estado_label }}
                </span>
            </div>

            <div class="card-body p-4">

                {{-- Descripción --}}
                <div class="mb-4">
                    <div class="text-muted mb-1" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;font-weight:600">
                        Descripción
                    </div>
                    <p class="mb-0" style="font-size:.9rem;color:#374151">
                        {{ $evidencia->descripcion ?? '—' }}
                    </p>
                </div>

                {{-- Metadatos en grid --}}
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded text-center" style="background:#f8fafc">
                            <div class="text-muted mb-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em">Tipo</div>
                            <div class="fw-semibold" style="font-size:.9rem">{{ ucfirst($evidencia->tipo) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded text-center" style="background:#f8fafc">
                            <div class="text-muted mb-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em">Estado</div>
                            <div class="fw-semibold text-{{ $evidencia->estado_badge }}" style="font-size:.9rem">
                                {{ $evidencia->estado_label }}
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded text-center" style="background:#f8fafc">
                            <div class="text-muted mb-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em">Creada</div>
                            <div class="fw-semibold" style="font-size:.82rem">{{ $evidencia->created_at->format('d/m/Y') }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded text-center" style="background:#f8fafc">
                            <div class="text-muted mb-1" style="font-size:.7rem;text-transform:uppercase;letter-spacing:.05em">Actualizada</div>
                            <div class="fw-semibold" style="font-size:.82rem">{{ $evidencia->updated_at->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Archivo --}}
                @if($evidencia->archivo)
                    <div class="mb-4 p-3 rounded d-flex align-items-center gap-3"
                         style="background:#f0fdf4;border:1px solid #bbf7d0">
                        <i class="bi bi-file-earmark-arrow-down" style="font-size:1.8rem;color:var(--sena-green)"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold" style="font-size:.875rem">Archivo adjunto</div>
                            <div class="text-muted" style="font-size:.78rem">{{ basename($evidencia->archivo) }}</div>
                        </div>
                        <a href="{{ Storage::url($evidencia->archivo) }}"
                           target="_blank"
                           class="btn btn-sm text-white"
                           style="background:var(--sena-green);border-color:var(--sena-green)">
                            <i class="bi bi-download me-1"></i>Descargar
                        </a>
                    </div>
                @else
                    <div class="mb-4 p-3 rounded text-muted text-center"
                         style="background:#f8fafc;border:1px dashed #e2e8f0;font-size:.875rem">
                        <i class="bi bi-paperclip me-2"></i>Sin archivo adjunto
                    </div>
                @endif

                {{-- Observaciones --}}
                @if($evidencia->observaciones)
                    <div class="mb-4 p-3 rounded"
                         style="background:#fffbeb;border-left:4px solid #f59e0b">
                        <div class="fw-semibold mb-1" style="font-size:.8rem;color:#b45309;text-transform:uppercase;letter-spacing:.05em">
                            <i class="bi bi-chat-left-text me-1"></i>Observaciones
                        </div>
                        <p class="mb-0" style="font-size:.875rem;color:#374151">
                            {{ $evidencia->observaciones }}
                        </p>
                    </div>
                @endif

                {{-- Acciones --}}
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('evidencias.edit', $evidencia->id) }}"
                       class="btn text-white"
                       style="background:var(--sena-green);border-color:var(--sena-green)">
                        <i class="bi bi-pencil me-1"></i>Editar
                    </a>
                    <a href="{{ route('evidencias.index') }}"
                       class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Volver al listado
                    </a>
                    <form method="POST"
                          action="{{ route('evidencias.destroy', $evidencia->id) }}"
                          class="ms-auto"
                          onsubmit="return confirm('¿Eliminar esta evidencia?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-trash3 me-1"></i>Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
