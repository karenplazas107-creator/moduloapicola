@extends('evidencias::layouts.master')

@section('title', 'Nueva Evidencia')
@section('page-title', 'Registrar Nueva Evidencia')

@section('breadcrumb')
    <li class="breadcrumb-item active">Nueva Evidencia</li>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-semibold">
                    <i class="bi bi-plus-circle me-2" style="color:var(--sena-green)"></i>
                    Nueva Evidencia
                </h6>
            </div>

            <div class="card-body p-4">
                <form method="POST"
                      action="{{ route('evidencias.store') }}"
                      enctype="multipart/form-data"
                      novalidate>
                    @csrf

                    {{-- Título --}}
                    <div class="mb-3">
                        <label for="titulo" class="form-label fw-semibold" style="font-size:.875rem">
                            Título <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               id="titulo"
                               name="titulo"
                               class="form-control @error('titulo') is-invalid @enderror"
                               value="{{ old('titulo') }}"
                               placeholder="Ej: Evidencia de práctica N.° 1"
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
                                  maxlength="2000"
                                  placeholder="Describe brevemente el contenido de esta evidencia…">{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tipo + Estado en fila --}}
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="tipo" class="form-label fw-semibold" style="font-size:.875rem">
                                Tipo de evidencia <span class="text-danger">*</span>
                            </label>
                            <select id="tipo"
                                    name="tipo"
                                    class="form-select @error('tipo') is-invalid @enderror"
                                    required>
                                <option value="">— Seleccionar tipo —</option>
                                @foreach($tipos as $valor => $etiqueta)
                                    <option value="{{ $valor }}" {{ old('tipo') === $valor ? 'selected' : '' }}>
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
                                        {{ old('estado', 'pendiente') === $valor ? 'selected' : '' }}>
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
                            Archivo adjunto
                            <span class="text-muted fw-normal">(máx. 10 MB)</span>
                        </label>
                        <input type="file"
                               id="archivo"
                               name="archivo"
                               class="form-control @error('archivo') is-invalid @enderror"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.mp4,.avi,.zip">
                        <div class="form-text">
                            Formatos permitidos: PDF, Word, Excel, PowerPoint, imágenes (JPG/PNG), vídeos (MP4) y ZIP.
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
                                  maxlength="2000"
                                  placeholder="Notas adicionales o comentarios del revisor…">{{ old('observaciones') }}</textarea>
                        @error('observaciones')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Acciones --}}
                    <div class="d-flex gap-2">
                        <button type="submit"
                                class="btn text-white px-4"
                                style="background:var(--sena-green);border-color:var(--sena-green)">
                            <i class="bi bi-floppy me-1"></i>Guardar Evidencia
                        </button>
                        <a href="{{ route('evidencias.index') }}"
                           class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
