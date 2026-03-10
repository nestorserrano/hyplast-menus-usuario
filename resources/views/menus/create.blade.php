@extends('adminlte::page')

@section('title', 'Crear Menú')

@section('plugins.Select2', true)

@section('content_header')
    <h1>
        <i class="fas fa-plus"></i> Crear Nuevo Menú
    </h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Formulario de Creación</h3>
            <div class="card-tools">
                <a href="{{ route('menus.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <form action="{{ route('menus.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="text">Nombre del Menú <span class="text-danger">*</span></label>
                            <input type="text" name="text" id="text" class="form-control @error('text') is-invalid @enderror"
                                   value="{{ old('text') }}" required>
                            @error('text')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="url">Ruta (URL o Route Name)</label>
                            <input type="text" name="url" id="url" class="form-control @error('url') is-invalid @enderror"
                                   value="{{ old('url') }}" placeholder="Ej: /dashboard o route:dashboard">
                            @error('url')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">Dejar vacío si es un menú padre con submenús</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="icon">Icono FontAwesome</label>
                            <select name="icon" id="icon" class="form-control select2 @error('icon') is-invalid @enderror">
                                <option value="">-- Sin icono --</option>
                                @foreach($iconos as $clase => $nombre)
                                    <option value="{{ $clase }}" {{ old('icon') == $clase ? 'selected' : '' }}>
                                        {{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('icon')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <div id="icono-preview" class="mt-2"></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="parent">Menú Padre (opcional)</label>
                            <select name="parent" id="parent" class="form-control select2 @error('parent') is-invalid @enderror">
                                <option value="0">-- Menú Principal --</option>
                                @foreach($menusParent as $menuParent)
                                    <option value="{{ $menuParent->id }}" {{ old('parent') == $menuParent->id ? 'selected' : '' }}>
                                        {{ $menuParent->text }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="text-muted">Seleccione si este es un submenú</small>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="order">Orden <span class="text-danger">*</span></label>
                            <input type="number" name="order" id="order" class="form-control @error('order') is-invalid @enderror"
                                   value="{{ old('order', 0) }}" min="0" required>
                            @error('order')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="enabled">Estado</label>
                            <select name="enabled" id="enabled" class="form-control @error('enabled') is-invalid @enderror">
                                <option value="1" {{ old('enabled', 1) == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('enabled') == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('enabled')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar Menú
                </button>
                <a href="{{ route('menus.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
@stop

@section('css')
@stop

@section('js')
    <script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4'
        });

        // Preview del icono
        $('#icon').on('change', function() {
            const iconClass = $(this).val();
            if (iconClass) {
                $('#icono-preview').html('<i class="' + iconClass + ' fa-3x text-primary"></i>');
            } else {
                $('#icono-preview').html('');
            }
        });

        // Mostrar icono inicial si hay uno seleccionado
        if ($('#icon').val()) {
            $('#icon').trigger('change');
        }
    });
    </script>
@stop
