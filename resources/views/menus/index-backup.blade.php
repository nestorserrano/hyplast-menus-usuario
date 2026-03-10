@extends('adminlte::page')

@section('title', 'Gestión de Menús')

@section('content_header')
    <h1>
        <i class="fas fa-bars"></i> Gestión de Menús
    </h1>
@stop

@section('content')
    {{-- Panel de Control --}}
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ \App\Models\Menu::count() }}</h3>
                    <p>Total Menús</p>
                </div>
                <div class="icon">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ \App\Models\Menu::where('parent', 0)->orWhereNull('parent')->count() }}</h3>
                    <p>Menús Principales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ \App\Models\Menu::where('parent', '!=', 0)->whereNotNull('parent')->count() }}</h3>
                    <p>Submenús</p>
                </div>
                <div class="icon">
                    <i class="fas fa-sitemap"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ \App\Models\User::count() }}</h3>
                    <p>Usuarios</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs de Gestión --}}
    <div class="card card-primary card-outline card-outline-tabs">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="custom-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="menus-tab" data-toggle="pill" href="#menus" role="tab">
                        <i class="fas fa-list"></i> Todos los Menús
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="usuarios-tab" data-toggle="pill" href="#usuarios" role="tab">
                        <i class="fas fa-user-cog"></i> Menús por Usuario
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="iconos-tab" data-toggle="pill" href="#iconos" role="tab">
                        <i class="fas fa-icons"></i> Gestión de Iconos
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="custom-tabs-content">
                {{-- Tab 1: Todos los Menús --}}
                <div class="tab-pane fade show active" id="menus" role="tabpanel">
                    <div class="mb-3">
                        <a href="{{ route('menus.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Nuevo Menú
                        </a>
                        <button type="button" class="btn btn-primary" id="btn-crear-padre">
                            <i class="fas fa-folder-plus"></i> Nuevo Menú Padre
                        </button>
                        <button type="button" class="btn btn-info" onclick="$('#menus-table').DataTable().ajax.reload();">
                            <i class="fas fa-sync"></i> Recargar
                        </button>
                    </div>
                    <table id="menus-table" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th>Nombre</th>
                                <th>Icono</th>
                                <th>Ruta</th>
                                <th>Nivel</th>
                                <th>Orden</th>
                                <th>Estado</th>
                                <th>Usuarios</th>
                                <th width="15%">Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                {{-- Tab 2: Menús por Usuario --}}
                <div class="tab-pane fade" id="usuarios" role="tabpanel">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="select-usuario">Seleccionar Usuario:</label>
                                <select id="select-usuario" class="form-control select2" style="width: 100%;">
                                    <option value="">-- Seleccione un usuario --</option>
                                    @foreach(\App\Models\User::orderBy('name')->get() as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label>&nbsp;</label>
                            <div>
                                <button type="button" class="btn btn-primary" id="btn-asignar-menus">
                                    <i class="fas fa-edit"></i> Asignar Menús a Usuario
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="preview-menus-usuario" class="mt-4" style="display: none;">
                        <h5>Vista Previa del Menú del Usuario: <span id="nombre-usuario-preview"></span></h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary">
                                        <h3 class="card-title">Menús Asignados</h3>
                                    </div>
                                    <div class="card-body p-0">
                                        <ul id="lista-menus-usuario" class="nav nav-pills nav-sidebar flex-column">
                                            <!-- Se llenará dinámicamente -->
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-info">
                                        <h3 class="card-title">Estadísticas</h3>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Total de menús asignados:</strong> <span id="total-menus-usuario">0</span></p>
                                        <p><strong>Menús principales:</strong> <span id="total-principales-usuario">0</span></p>
                                        <p><strong>Submenús:</strong> <span id="total-submenus-usuario">0</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab 3: Gestión de Iconos --}}
                <div class="tab-pane fade" id="iconos" role="tabpanel">
                    <div class="mb-3">
                        <button type="button" class="btn btn-success" id="btn-agregar-icono">
                            <i class="fas fa-plus"></i> Agregar Icono Personalizado
                        </button>
                    </div>

                    <div class="row" id="lista-iconos">
                        @php
                            $iconosDefault = [
                                'fas fa-home' => 'Inicio',
                                'fas fa-tachometer-alt' => 'Dashboard',
                                'fas fa-users' => 'Usuarios',
                                'fas fa-user' => 'Usuario',
                                'fas fa-cog' => 'Configuración',
                                'fas fa-boxes' => 'Productos',
                                'fas fa-warehouse' => 'Almacén',
                                'fas fa-industry' => 'Producción',
                                'fas fa-cogs' => 'Máquinas',
                                'fas fa-clipboard-list' => 'Requisiciones',
                                'fas fa-shopping-cart' => 'Ventas',
                                'fas fa-chart-line' => 'Reportes',
                                'fas fa-file-alt' => 'Documentos',
                                'fas fa-tasks' => 'Tareas',
                                'fas fa-calendar' => 'Calendario',
                                'fas fa-truck' => 'Entregas',
                                'fas fa-barcode' => 'Código de Barras',
                                'fas fa-print' => 'Impresión',
                                'fas fa-clipboard-check' => 'Control Calidad',
                                'fas fa-building' => 'Empresas',
                                'fas fa-sitemap' => 'Estructura',
                                'fas fa-list' => 'Listado',
                                'fas fa-wrench' => 'Herramientas',
                                'fas fa-database' => 'Base de Datos',
                                'fas fa-envelope' => 'Mensajes',
                                'fas fa-bell' => 'Notificaciones',
                                'fas fa-user-shield' => 'Admin',
                                'fas fa-table' => 'Tablas',
                                'fas fa-sync' => 'Sincronizar',
                                'fas fa-check-circle' => 'Validar',
                            ];
                        @endphp
                        @foreach($iconosDefault as $clase => $nombre)
                            <div class="col-md-2 text-center mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <i class="{{ $clase }} fa-3x mb-2"></i>
                                        <p class="mb-0 small"><strong>{{ $nombre }}</strong></p>
                                        <p class="mb-0 text-muted small"><code>{{ $clase }}</code></p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para Crear Menú Padre Rápido --}}
    <div class="modal fade" id="modalCrearPadre" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title"><i class="fas fa-folder-plus"></i> Crear Menú Padre</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="form-crear-padre">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre del Menú *</label>
                            <input type="text" name="text" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Icono</label>
                            <select name="icon" class="form-control select2-modal">
                                <option value="">-- Sin icono --</option>
                                @foreach($iconosDefault as $clase => $nombre)
                                    <option value="{{ $clase }}">{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Orden</label>
                            <input type="number" name="order" class="form-control" value="0" min="0">
                        </div>
                        <input type="hidden" name="parent" value="0">
                        <input type="hidden" name="url" value="#">
                        <input type="hidden" name="enabled" value="1">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear Menú Padre</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal para Agregar Icono Personalizado --}}
    <div class="modal fade" id="modalAgregarIcono" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h4 class="modal-title"><i class="fas fa-icons"></i> Agregar Icono Personalizado</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form id="form-agregar-icono">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Clase del Icono *</label>
                            <input type="text" name="clase_icono" class="form-control" placeholder="Ej: fas fa-star" required>
                            <small class="text-muted">Formato FontAwesome: fas fa-nombre-icono</small>
                        </div>
                        <div class="form-group">
                            <label>Nombre/Descripción *</label>
                            <input type="text" name="nombre_icono" class="form-control" placeholder="Ej: Estrella" required>
                        </div>
                        <div class="form-group">
                            <label>Vista Previa</label>
                            <div id="preview-icono-custom" class="text-center p-3 border rounded">
                                <i class="fas fa-question fa-3x"></i>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar Icono</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Inicializar Select2
    $('.select2').select2();

    // DataTable de Menús
    $('#menus-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('menus.data') }}",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'text', name: 'text'},
            {data: 'icono_preview', name: 'icon', orderable: false},
            {data: 'url', name: 'url'},
            {data: 'nivel', name: 'nivel'},
            {data: 'order', name: 'order'},
            {data: 'estado', name: 'enabled'},
            {data: 'usuarios_count', name: 'usuarios_count', orderable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        order: [[5, 'asc']]
    });

    // Crear Menú Padre Rápido
    $('#btn-crear-padre').click(function() {
        $('#modalCrearPadre').modal('show');
        $('.select2-modal').select2({
            dropdownParent: $('#modalCrearPadre')
        });
    });

    $('#form-crear-padre').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: "{{ route('menus.store') }}",
            type: 'POST',
            data: $(this).serialize() + '&_token={{ csrf_token() }}',
            success: function(response) {
                $('#modalCrearPadre').modal('hide');
                Swal.fire('Éxito', 'Menú padre creado correctamente', 'success');
                $('#menus-table').DataTable().ajax.reload();
                $('#form-crear-padre')[0].reset();
            },
            error: function(xhr) {
                let mensaje = 'Error al crear el menú';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }
                Swal.fire('Error', mensaje, 'error');
            }
        });
    });

    // Selector de Usuario - Ver Menús
    $('#select-usuario').change(function() {
        const userId = $(this).val();
        const userName = $(this).find('option:selected').text();

        if (userId) {
            cargarMenusUsuario(userId, userName);
        } else {
            $('#preview-menus-usuario').hide();
        }
    });

    // Asignar Menús a Usuario
    $('#btn-asignar-menus').click(function() {
        const userId = $('#select-usuario').val();
        if (!userId) {
            Swal.fire('Advertencia', 'Por favor seleccione un usuario', 'warning');
            return;
        }
        window.location.href = '/users/' + userId + '/menus';
    });

    // Agregar Icono Personalizado
    $('#btn-agregar-icono').click(function() {
        $('#modalAgregarIcono').modal('show');
    });

    // Preview del icono personalizado
    $('input[name="clase_icono"]').on('input', function() {
        const claseIcono = $(this).val();
        if (claseIcono) {
            $('#preview-icono-custom').html('<i class="' + claseIcono + ' fa-3x text-primary"></i>');
        } else {
            $('#preview-icono-custom').html('<i class="fas fa-question fa-3x"></i>');
        }
    });

    // Guardar icono personalizado (localStorage)
    $('#form-agregar-icono').submit(function(e) {
        e.preventDefault();

        const claseIcono = $('input[name="clase_icono"]').val();
        const nombreIcono = $('input[name="nombre_icono"]').val();

        // Guardar en localStorage
        let iconosCustom = JSON.parse(localStorage.getItem('iconos_personalizados') || '{}');
        iconosCustom[claseIcono] = nombreIcono;
        localStorage.setItem('iconos_personalizados', JSON.stringify(iconosCustom));

        // Agregar a la lista visual
        const nuevoIconoHtml = `
            <div class="col-md-2 text-center mb-3 icono-custom" data-clase="${claseIcono}">
                <div class="card border-success">
                    <div class="card-body">
                        <i class="${claseIcono} fa-3x mb-2 text-success"></i>
                        <p class="mb-0 small"><strong>${nombreIcono}</strong></p>
                        <p class="mb-0 text-muted small"><code>${claseIcono}</code></p>
                        <button type="button" class="btn btn-sm btn-danger mt-2 btn-eliminar-icono">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        $('#lista-iconos').prepend(nuevoIconoHtml);

        $('#modalAgregarIcono').modal('hide');
        $('#form-agregar-icono')[0].reset();
        $('#preview-icono-custom').html('<i class="fas fa-question fa-3x"></i>');

        Swal.fire('Éxito', 'Icono personalizado agregado', 'success');
    });

    // Eliminar icono personalizado
    $(document).on('click', '.btn-eliminar-icono', function() {
        const card = $(this).closest('.icono-custom');
        const claseIcono = card.data('clase');

        Swal.fire({
            title: '¿Eliminar icono?',
            text: "Se eliminará de tu lista de iconos personalizados",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                let iconosCustom = JSON.parse(localStorage.getItem('iconos_personalizados') || '{}');
                delete iconosCustom[claseIcono];
                localStorage.setItem('iconos_personalizados', JSON.stringify(iconosCustom));

                card.fadeOut(300, function() {
                    $(this).remove();
                });

                Swal.fire('Eliminado', 'Icono personalizado eliminado', 'success');
            }
        });
    });

    // Cargar iconos personalizados al iniciar
    cargarIconosPersonalizados();
});

function cargarMenusUsuario(userId, userName) {
    $.ajax({
        url: '/api/menus/usuario/' + userId,
        type: 'GET',
        success: function(response) {
            $('#nombre-usuario-preview').text(userName);

            let html = '';
            let totalMenus = 0;
            let totalPrincipales = 0;
            let totalSubmenus = 0;

            response.menus.forEach(function(menu) {
                totalMenus++;
                totalPrincipales++;

                if (menu.hijos && menu.hijos.length > 0) {
                    html += `
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="${menu.icon}"></i>
                                <p>${menu.text} <i class="fas fa-angle-left right"></i></p>
                            </a>
                            <ul class="nav nav-treeview" style="display: block;">
                    `;

                    menu.hijos.forEach(function(submenu) {
                        totalMenus++;
                        totalSubmenus++;
                        html += `
                            <li class="nav-item">
                                <a href="${submenu.url}" class="nav-link">
                                    <i class="${submenu.icon}"></i>
                                    <p>${submenu.text}</p>
                                </a>
                            </li>
                        `;
                    });

                    html += `
                            </ul>
                        </li>
                    `;
                } else {
                    html += `
                        <li class="nav-item">
                            <a href="${menu.url}" class="nav-link">
                                <i class="${menu.icon}"></i>
                                <p>${menu.text}</p>
                            </a>
                        </li>
                    `;
                }
            });

            $('#lista-menus-usuario').html(html);
            $('#total-menus-usuario').text(totalMenus);
            $('#total-principales-usuario').text(totalPrincipales);
            $('#total-submenus-usuario').text(totalSubmenus);
            $('#preview-menus-usuario').fadeIn();
        },
        error: function(xhr) {
            Swal.fire('Error', 'No se pudieron cargar los menús del usuario', 'error');
        }
    });
}

function cargarIconosPersonalizados() {
    const iconosCustom = JSON.parse(localStorage.getItem('iconos_personalizados') || '{}');

    Object.keys(iconosCustom).forEach(function(clase) {
        const nombre = iconosCustom[clase];
        const iconoHtml = `
            <div class="col-md-2 text-center mb-3 icono-custom" data-clase="${clase}">
                <div class="card border-success">
                    <div class="card-body">
                        <i class="${clase} fa-3x mb-2 text-success"></i>
                        <p class="mb-0 small"><strong>${nombre}</strong></p>
                        <p class="mb-0 text-muted small"><code>${clase}</code></p>
                        <button type="button" class="btn btn-sm btn-danger mt-2 btn-eliminar-icono">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        $('#lista-iconos').prepend(iconoHtml);
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
