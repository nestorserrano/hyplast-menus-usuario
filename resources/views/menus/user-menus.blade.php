@extends('adminlte::page')

@section('title', 'Menús del Usuario')

@section('content_header')
    <h1>
        <i class="fas fa-user-cog"></i> Gestionar Menús del Usuario
    </h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Usuario: <strong>{{ $user->name }}</strong></h3>
            <div class="card-tools">
                <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver a Usuarios
                </a>
            </div>
        </div>
        <form action="{{ route('menus.update-user-menus', $user->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Seleccione los menús que verá este usuario en el sistema.
                        </div>
                    </div>
                </div>

                <!-- Menús Principales -->
                @if(isset($todosMenus['principales']) && $todosMenus['principales']->count() > 0)
                    <div class="row">
                        <div class="col-md-12">
                            <h4><i class="fas fa-list"></i> Menús Principales</h4>
                            <hr>
                        </div>
                    </div>
                    <div class="row">
                        @foreach($todosMenus['principales'] as $menu)
                            <div class="col-md-4 col-sm-6">
                                <div class="card {{ in_array($menu->id, $menusAsignados) ? 'card-primary' : 'card-outline card-primary' }}">
                                    <div class="card-header">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox"
                                                   class="custom-control-input menu-checkbox"
                                                   name="menus[]"
                                                   value="{{ $menu->id }}"
                                                   id="menu-{{ $menu->id }}"
                                                   {{ in_array($menu->id, $menusAsignados) ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="menu-{{ $menu->id }}">
                                                @if($menu->icono)
                                                    <i class="{{ $menu->icono }}"></i>
                                                @endif
                                                <strong>{{ $menu->nombre }}</strong>
                                            </label>
                                        </div>
                                    </div>
                                    @if($menu->ruta)
                                        <div class="card-body p-2">
                                            <small class="text-muted">
                                                <i class="fas fa-link"></i> {{ $menu->ruta }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Submenús -->
                @if(isset($todosMenus['submenus']) && $todosMenus['submenus']->count() > 0)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h4><i class="fas fa-sitemap"></i> Submenús</h4>
                            <hr>
                        </div>
                    </div>
                    <div class="row">
                        @foreach($todosMenus['submenus'] as $submenu)
                            <div class="col-md-3 col-sm-6">
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox"
                                           class="custom-control-input menu-checkbox"
                                           name="menus[]"
                                           value="{{ $submenu->id }}"
                                           id="menu-{{ $submenu->id }}"
                                           {{ in_array($submenu->id, $menusAsignados) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="menu-{{ $submenu->id }}">
                                        @if($submenu->icono)
                                            <i class="{{ $submenu->icono }}"></i>
                                        @endif
                                        {{ $submenu->nombre }}
                                        @if($submenu->padre)
                                            <br><small class="text-muted">└─ {{ $submenu->padre->nombre }}</small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if(!isset($todosMenus['principales']) && !isset($todosMenus['submenus']))
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                No hay menús disponibles para asignar.
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar Menús
                </button>
                <button type="button" class="btn btn-info" id="select-all-menus">
                    <i class="fas fa-check-double"></i> Seleccionar Todos
                </button>
                <button type="button" class="btn btn-warning" id="deselect-all-menus">
                    <i class="fas fa-times"></i> Deseleccionar Todos
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Seleccionar todos
    $('#select-all-menus').on('click', function() {
        $('.menu-checkbox').prop('checked', true);
        updateCardStyles();
    });

    // Deseleccionar todos
    $('#deselect-all-menus').on('click', function() {
        $('.menu-checkbox').prop('checked', false);
        updateCardStyles();
    });

    // Actualizar estilos de las cards al cambiar checkbox
    $('.menu-checkbox').on('change', function() {
        updateCardStyles();
    });

    function updateCardStyles() {
        $('.menu-checkbox').each(function() {
            const card = $(this).closest('.card');
            if ($(this).prop('checked')) {
                card.removeClass('card-outline').addClass('card-primary');
            } else {
                card.removeClass('card-primary').addClass('card-outline card-primary');
            }
        });
    }
});
</script>
@stop
