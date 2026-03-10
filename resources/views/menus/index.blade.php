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
@stop

{{-- Modal para Asignar Menús a Usuario --}}
@section('modals')
<div class="modal fade" id="modalAsignarMenus" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title">
                    <i class="fas fa-check-double"></i> Asignar Menús a Usuario:
                    <span id="modal-usuario-nombre"></span>
                </h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Seleccione los menús que este usuario podrá ver. Los menús disponibles son los que tiene <strong>nestorserrano</strong> (catálogo maestro).
                    Los menús marcados son los que ya tiene el usuario.
                </div>

                <form id="form-asignar-menus">
                    <input type="hidden" id="modal-usuario-id" name="user_id">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-sm btn-primary mb-2" id="btn-seleccionar-todos">
                                    <i class="fas fa-check-square"></i> Seleccionar Todos
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary mb-2" id="btn-deseleccionar-todos">
                                    <i class="fas fa-square"></i> Deseleccionar Todos
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="lista-menus-asignar">
                        <!-- Se llenará dinámicamente con JavaScript -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btn-guardar-asignaciones">
                    <i class="fas fa-save"></i> Guardar Asignaciones
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
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
<script>
let usuarioSeleccionadoId = null;
let todosLosMenus = [];

// Función para cargar DataTables dinámicamente
function loadDataTables(callback) {
    if (typeof $.fn.DataTable !== 'undefined') {
        console.log('DataTables ya está cargado');
        callback();
        return;
    }

    console.log('Cargando DataTables...');

    // Cargar CSS
    $('<link>')
        .appendTo('head')
        .attr({
            type: 'text/css',
            rel: 'stylesheet',
            href: 'https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css'
        });

    // Cargar JS principal
    $.getScript('https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js', function() {
        console.log('jquery.dataTables.min.js cargado');

        // Cargar JS Bootstrap
        $.getScript('https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js', function() {
            console.log('dataTables.bootstrap4.min.js cargado');
            callback();
        });
    });
}

$(document).ready(function() {
    console.log('DOM ready, jQuery:', typeof $);

    // Cargar DataTables y luego inicializar
    loadDataTables(function() {
        console.log('Iniciando DataTable...');

        // DataTable de Menús
        const table = $('#menus-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('menus.data') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'text', name: 'text'},
                {data: 'icono_preview', name: 'icon', orderable: false},
                {data: 'url', name: 'url'},
                {data: 'nivel', name: 'nivel', orderable: false},
                {data: 'order', name: 'order'},
                {data: 'estado', name: 'enabled'},
                {data: 'usuarios_count', name: 'usuarios_count', orderable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            order: [[5, 'asc']],
            pageLength: 25
        });

        console.log('DataTable inicializado correctamente');
    });

    // Cambio en filtro de usuario
    $('#filtro-usuario').on('change', function() {
        const userId = $(this).val();
        usuarioSeleccionadoId = userId;
        console.log('Usuario seleccionado:', userId);
        $('#preview-menu-usuario').hide();
    });

    // Ver preview del menú
    $('#btn-ver-preview').on('click', function() {
        console.log('Click en ver preview');
        if (!usuarioSeleccionadoId) {
            Swal.fire('Advertencia', 'Seleccione un usuario primero', 'warning');
            return;
        }

        const userName = $('#filtro-usuario option:selected').text();
        cargarPreviewMenu(usuarioSeleccionadoId, userName);
    });

    // Abrir modal de asignación
    $('#btn-asignar-menus-usuario').on('click', function() {
        console.log('Click en asignar menus, usuario ID:', usuarioSeleccionadoId);
        if (!usuarioSeleccionadoId) {
            Swal.fire('Advertencia', 'Seleccione un usuario primero', 'warning');
            return;
        }

        const userName = $('#filtro-usuario option:selected').text();
        abrirModalAsignacion(usuarioSeleccionadoId, userName);
    });

    // Seleccionar todos los checkboxes
    $('#btn-seleccionar-todos').on('click', function() {
        $('#lista-menus-asignar input[type="checkbox"]').prop('checked', true);
    });

    // Deseleccionar todos los checkboxes
    $('#btn-deseleccionar-todos').on('click', function() {
        $('#lista-menus-asignar input[type="checkbox"]').prop('checked', false);
    });

    // Guardar asignaciones
    $('#btn-guardar-asignaciones').on('click', function() {
        guardarAsignaciones();
    });
});

function cargarPreviewMenu(userId, userName) {
    $.ajax({
        url: '/api/menus/usuario/' + userId,
        type: 'GET',
        beforeSend: function() {
            $('#lista-preview-menu').html('<li class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</li>');
            $('#preview-menu-usuario').show();
        },
        success: function(response) {
            $('#nombre-usuario-seleccionado').text(userName);

            if (!response.success || !response.menus || response.menus.length === 0) {
                $('#lista-preview-menu').html('<li class="text-muted"><i class="fas fa-info-circle"></i> Este usuario no tiene menús asignados</li>');
                return;
            }

            let html = '';
            response.menus.forEach(function(menu) {
                if (menu.hijos && menu.hijos.length > 0) {
                    html += `
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="${menu.icon || 'fas fa-circle'}"></i>
                                <p>${menu.text} <i class="fas fa-angle-left right"></i></p>
                            </a>
                            <ul class="nav nav-treeview" style="display: block;">
                    `;

                    menu.hijos.forEach(function(submenu) {
                        html += `
                            <li class="nav-item">
                                <a href="${submenu.url || '#'}" class="nav-link">
                                    <i class="${submenu.icon || 'far fa-circle'}"></i>
                                    <p>${submenu.text}</p>
                                </a>
                            </li>
                        `;
                    });

                    html += `</ul></li>`;
                } else {
                    html += `
                        <li class="nav-item">
                            <a href="${menu.url || '#'}" class="nav-link">
                                <i class="${menu.icon || 'fas fa-circle'}"></i>
                                <p>${menu.text}</p>
                            </a>
                        </li>
                    `;
                }
            });

            $('#lista-preview-menu').html(html);
        },
        error: function() {
            Swal.fire('Error', 'No se pudo cargar la vista previa', 'error');
            $('#preview-menu-usuario').hide();
        }
    });
}

function abrirModalAsignacion(userId, userName) {
    $('#modal-usuario-id').val(userId);
    $('#modal-usuario-nombre').text(userName);

    // Cargar todos los menús y los asignados al usuario
    $.ajax({
        url: '/api/menus/todos-y-asignados/' + userId,
        type: 'GET',
        beforeSend: function() {
            $('#lista-menus-asignar').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando menús...</div>');
        },
        success: function(response) {
            todosLosMenus = response.todos;
            const menusAsignados = response.asignados;

            let html = '';

            // Agrupar por menús principales
            const principales = todosLosMenus.filter(m => !m.parent || m.parent == 0);

            principales.forEach(function(menuPrincipal) {
                const isChecked = menusAsignados.includes(menuPrincipal.id) ? 'checked' : '';

                html += `
                    <div class="menu-checkbox-item menu-principal">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input menu-check"
                                   id="menu-${menuPrincipal.id}"
                                   name="menus[]"
                                   value="${menuPrincipal.id}"
                                   ${isChecked}>
                            <label class="custom-control-label" for="menu-${menuPrincipal.id}">
                                <i class="${menuPrincipal.icon || 'fas fa-folder'}"></i>
                                <strong>${menuPrincipal.text}</strong>
                            </label>
                        </div>
                    </div>
                `;

                // Submenús
                const submenus = todosLosMenus.filter(m => m.parent == menuPrincipal.id);
                submenus.forEach(function(submenu) {
                    const isSubChecked = menusAsignados.includes(submenu.id) ? 'checked' : '';

                    html += `
                        <div class="menu-checkbox-item submenu-item">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input menu-check"
                                       id="menu-${submenu.id}"
                                       name="menus[]"
                                       value="${submenu.id}"
                                       ${isSubChecked}>
                                <label class="custom-control-label" for="menu-${submenu.id}">
                                    <i class="${submenu.icon || 'far fa-circle'}"></i>
                                    ${submenu.text}
                                </label>
                            </div>
                        </div>
                    `;
                });
            });

            $('#lista-menus-asignar').html(html);
            $('#modalAsignarMenus').modal('show');
        },
        error: function() {
            Swal.fire('Error', 'No se pudieron cargar los menús', 'error');
        }
    });
}

function guardarAsignaciones() {
    const userId = $('#modal-usuario-id').val();
    const menusSeleccionados = [];

    $('#lista-menus-asignar input[type="checkbox"]:checked').each(function() {
        menusSeleccionados.push($(this).val());
    });

    $.ajax({
        url: '/api/menus/asignar-usuario',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            user_id: userId,
            menus: menusSeleccionados
        },
        beforeSend: function() {
            $('#btn-guardar-asignaciones').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        },
        success: function(response) {
            $('#modalAsignarMenus').modal('hide');
            Swal.fire('Éxito', 'Menús asignados correctamente', 'success');
            $('#menus-table').DataTable().ajax.reload();
            $('#preview-menu-usuario').hide();
        },
        error: function(xhr) {
            Swal.fire('Error', 'No se pudieron guardar las asignaciones', 'error');
        },
        complete: function() {
            $('#btn-guardar-asignaciones').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Asignaciones');
        }
    });
}

function deleteMenu(id) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede revertir",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/menus/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Eliminado', response.message, 'success');
                        $('#menus-table').DataTable().ajax.reload();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Error al eliminar el menú', 'error');
                }
            });
        }
    });
}
</script>
@stop
