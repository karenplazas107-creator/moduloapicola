@extends('evidencias::layouts.master')

@section('title', 'Editar Evidencia')
@section('page-title', 'Editar Evidencia')

@section('breadcrumb')
    <li class="breadcrumb-item active">Editar #{{ $evidencia->id }}</li>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-pencil-square me-2" style="color:var(--sena-green)"></i>
                    Editando: <span class="text-muted fw-normal">{{ Str::limit($evidencia->titulo, 50) }}</span>
                </h6>
                <span class="badge bg-{{ $evidencia->estado_badge }} bg-opacity-15 text-{{ $evidencia->estado_badge }}"
                      style="font-size:.78rem">
                    {{ $evidencia->estado_label }}
                </span>
            </div>

            <div class="card-body p-4">
                <form method="POST"
                      action="{{ route('evidencias.update', $evidencia->id) }}"
                      enctype="multipart/form-data"
                      novalidate>
                    @csrf
                    @method('PUT')

                    {{-- Título --}}
                    <div class="mb-3">
                        <label for="titulo" class="form-label fw-semibold" style="font-size:.875rem">
                            Título <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="titulo"
                               name="titulo"
                               class="form-control @error('titulo') is-invalid @enderror"
                               value="{{ old('titulo', $evidencia->titulo) }}"
                               maxlength="255"
                               required>
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Descripción --}}
                    <div class="mb-3">
                        <label for="descripcion" class="form-label fw-semibold" style="font-size:.875rem">
                            Descripción
                        </label>
                        <textarea id="descripcion"
                                  name="descripcion"
                                  class="form-control @error('descripcion') is-invalid @enderror"
                                  rows="3"
                                  maxlength="2000">{{ old('descripcion', $evidencia->descripcion) }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tipo + Estado --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="tipo" class="form-label fw-semibold" style="font-size:.875rem">
                                Tipo de evidencia <span class="text-danger">*</span>
                            </label>
                            <select id="tipo"
                                    name="tipo"
                                    class="form-select @error('tipo') is-invalid @enderror"
                                    required>
                                @foreach($tipos as $valor => $etiqueta)
                                    <option value="{{ $valor }}"
                                        {{ old('tipo', $evidencia->tipo) === $valor ? 'selected' : '' }}>
                                        {{ $etiqueta }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="estado" class="form-label fw-semibold" style="font-size:.875rem">
                                Estado <span class="text-danger">*</span>
                            </label>
                            <select id="estado"
                                    name="estado"
                                    class="form-select @error('estado') is-invalid @enderror"
                                    required>
                                @foreach($estados as $valor => $etiqueta)
                                    <option value="{{ $valor }}"
                                        {{ old('estado', $evidencia->estado) === $valor ? 'selected' : '' }}>
                                        {{ $etiqueta }}
                                    </option>
                                @endforeach
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Archivo --}}
                    <div class="mb-3">
                        <label for="archivo" class="form-label fw-semibold" style="font-size:.875rem">
                            Reemplazar archivo
                            <span class="text-muted fw-normal">(opcional, máx. 10 MB)</span>
                        </label>
                        @if($evidencia->archivo)
                            <div class="mb-2 p-2 rounded d-flex align-items-center gap-2"
                                 style="background:#f0fdf4;border:1px dashed var(--sena-green);font-size:.8rem">
                                <i class="bi bi-file-earmark-check text-success"></i>
                                <span>Archivo actual:</span>
                                <a href="{{ Storage::url($evidencia->archivo) }}"
                                   target="_blank"
                                   class="text-decoration-none"
                                   style="color:var(--sena-green)">
                                    {{ basename($evidencia->archivo) }}
                                </a>
                            </div>
                        @endif
                        <input type="file"
                               id="archivo"
                               name="archivo"
                               class="form-control @error('archivo') is-invalid @enderror"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.avi,.zip">
                        <div class="form-text">
                            Sube un nuevo archivo para reemplazar el actual. Si no seleccionas ninguno, el archivo actual se conserva.
                        </div>
                        @error('archivo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Observaciones --}}
                    <div class="mb-4">
                        <label for="observaciones" class="form-label fw-semibold" style="font-size:.875rem">
                            Observaciones
                        </label>
                        <textarea id="observaciones"
                                  name="observaciones"
                                  class="form-control @error('observaciones') is-invalid @enderror"
                                  rows="2"
                                  maxlength="2000">{{ old('observaciones', $evidencia->observaciones) }}</textarea>
                        @error('observaciones')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Metadatos --}}
                    <div class="mb-4 p-3 rounded" style="background:#f8fafc;font-size:.8rem;color:#64748b">
                        <i class="bi bi-clock me-1"></i>
                        Creada: {{ $evidencia->created_at->format('d/m/Y H:i') }}
                        &nbsp;·&nbsp;
                        <i class="bi bi-pencil me-1"></i>
                        Última actualización: {{ $evidencia->updated_at->format('d/m/Y H:i') }}
                    </div>

                    {{-- Acciones --}}
                    <div class="d-flex gap-2">
                        <button type="submit"
                                class="btn text-white px-4"
                                style="background:var(--sena-green);border-color:var(--sena-green)">
                            <i class="bi bi-floppy me-1"></i>Actualizar Evidencia
                        </button>
                        <a href="{{ route('evidencias.show', $evidencia->id) }}"
                           class="btn btn-outline-secondary">
                            <i class="bi bi-eye me-1"></i>Ver detalle
                        </a>
                        <a href="{{ route('evidencias.index') }}"
                           class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
