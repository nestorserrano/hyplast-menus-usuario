@extends('adminlte::page')

@section('title', 'Asignar Usuarios a Menú')

@section('content_header')
    <h1>
        <i class="fas fa-users"></i> Asignar Usuarios al Menú
    </h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Menú: <strong>{{ $menu->text }}</strong></h3>
            <div class="card-tools">
                <a href="{{ route('menus.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        <form action="{{ route('menus.update-assignments', $menu->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Seleccione los usuarios que tendrán acceso a este menú.
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="select-all"> Seleccionar Todos
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    @foreach($usuarios as $usuario)
                        <div class="col-md-4 col-sm-6">
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox"
                                       class="custom-control-input user-checkbox"
                                       name="usuarios[]"
                                       value="{{ $usuario->id }}"
                                       id="user-{{ $usuario->id }}"
                                       {{ in_array($usuario->id, $usuariosAsignados) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="user-{{ $usuario->id }}">
                                    {{ $usuario->name }}
                                    @if($usuario->email)
                                        <small class="text-muted">({{ $usuario->email }})</small>
                                    @endif
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($usuarios->isEmpty())
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                No hay usuarios registrados en el sistema.
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar Asignaciones
                </button>
                <a href="{{ route('menus.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Seleccionar/Deseleccionar todos
    $('#select-all').on('change', function() {
        $('.user-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Actualizar el estado del "Seleccionar Todos"
    $('.user-checkbox').on('change', function() {
        const total = $('.user-checkbox').length;
        const checked = $('.user-checkbox:checked').length;
        $('#select-all').prop('checked', total === checked);
    });
});
</script>
@stop
