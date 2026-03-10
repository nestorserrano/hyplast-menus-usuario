@extends('adminlte::page')

@section('title', 'Gestión de Menús')

@section('plugins.Datatables', true)
@section('plugins.Sweetalert2', true)

@section('content_header')
    <h1>
        <i class="fas fa-bars"></i> Gestión de Menús
    </h1>
@stop

@section('content')
    {{-- Filtro Principal por Usuario --}}
    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtrar Menús por Usuario</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filtro-usuario">Seleccionar Usuario:</label>
                        <select id="filtro-usuario" class="form-control" style="width: 100%;">
                            <option value="">-- Seleccione un usuario --</option>
                            @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div>
                            <button type="button" class="btn btn-success" id="btn-asignar-menus-usuario">
                                <i class="fas fa-check-double"></i> Asignar Menús al Usuario Seleccionado
                            </button>
                            <button type="button" class="btn btn-info" id="btn-ver-preview">
                                <i class="fas fa-eye"></i> Ver Vista Previa del Menú
                            </button>
                            <a href="{{ route('menus.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Crear Nuevo Menú
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Vista previa del menú del usuario --}}
            <div id="preview-menu-usuario" style="display: none;">
                <hr>
                <h5>Vista Previa del Menú: <span id="nombre-usuario-seleccionado" class="badge badge-primary"></span></h5>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card bg-light">
                            <div class="card-body">
                                <ul id="lista-preview-menu" class="nav nav-pills nav-sidebar flex-column">
                                    <!-- Se llenará con JavaScript -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de Todos los Menús --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Menús del Sistema</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-tool" onclick="$('#menus-table').DataTable().ajax.reload();">
                    <i class="fas fa-sync"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="menus-table" class="table table-bordered table-striped table-hover table-sm">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th>Nombre</th>
                        <th>Icono</th>
                        <th>URL</th>
                        <th>Nivel</th>
                        <th>Orden</th>
                        <th>Estado</th>
                        <th>Usuarios</th>
                        <th width="15%">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    {{-- Incluir modal de asignación --}}
    @include('modals.modal-asignar-menus')
@stop

@section('css')
    <style>
        .menu-checkbox-item {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .menu-checkbox-item:last-child {
            border-bottom: none;
        }
        .menu-principal {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .submenu-item {
            padding-left: 40px;
            background-color: #fff;
        }
        .submenu-item label {
            font-weight: normal;
        }
    </style>
@stop

@section('js')
    @include('scripts.datatables.datatables-menu')
@stop
