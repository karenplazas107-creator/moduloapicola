@extends('sisgedi::layouts.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-4 p-4">
            <h4 class="mb-4">Nuevo Documento SISGEDI</h4>
            <form action="{{ route('sisgedi.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Código del Documento</label>
                    <input type="text" name="codigo" class="form-control" placeholder="Ej: SGD-F-001" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre del Documento</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('sisgedi.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-warning text-white">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection