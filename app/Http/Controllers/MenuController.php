<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\User;
use App\Helpers\ButtonHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use RealRashid\SweetAlert\Facades\Alert;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('menus.home');
    }

    /**
     * DataTables data for menus
     */
    public function menuData(Request $request)
    {
        $menus = Menu::with('padre')->select('menus.*')->get();

        return DataTables::of($menus)
            ->addIndexColumn()
            ->addColumn('text', function ($menu) {
                return $menu->text;
            })
            ->addColumn('url', function ($menu) {
                return $menu->url ?? '<span class="text-muted">N/A</span>';
            })
            ->addColumn('order', function ($menu) {
                return $menu->order;
            })
            ->addColumn('nivel', function ($menu) {
                if ($menu->padre) {
                    return '<span class="badge badge-info">' . ($menu->padre->text ?? '') . ' > ' . $menu->text . '</span>';
                }
                return '<span class="badge badge-primary">Menú Principal</span>';
            })
            ->addColumn('icono_preview', function ($menu) {
                if ($menu->icon) {
                    return '<i class="' . $menu->icon . ' fa-2x"></i>';
                }
                return '<span class="text-muted">Sin icono</span>';
            })
            ->addColumn('estado', function ($menu) {
                if ($menu->enabled) {
                    return '<span class="badge badge-success">Activo</span>';
                }
                return '<span class="badge badge-danger">Inactivo</span>';
            })
            ->addColumn('usuarios_count', function ($menu) {
                $count = $menu->usuarios()->count();
                return '<span class="badge badge-secondary">' . $count . ' usuarios</span>';
            })
            ->addColumn('action', function ($menu) {
                $editBtn = ButtonHelper::edit(route('menus.edit', $menu->id));
                $usersBtn = ButtonHelper::custom(route('menus.assign', $menu->id), 'btn-info', 'fa-users', 'Asignar Usuarios');
                $deleteBtn = ButtonHelper::delete(null, $menu->id, null, 'deleteMenu');

                return '<div class="btn-group">' . $editBtn . $usersBtn . $deleteBtn . '</div>';
            })
            ->rawColumns(['nivel', 'icono_preview', 'estado', 'usuarios_count', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $menusParent = Menu::where(function($q) {
            $q->where('parent', 0)->orWhereNull('parent');
        })->where('enabled', true)->orderBy('order')->get();
        $iconos = $this->getIconosList();

        return view('menus.create', compact('menusParent', 'iconos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'parent' => 'nullable|integer',
            'order' => 'required|integer|min:0',
            'enabled' => 'boolean',
        ]);

        try {
            $data = [
                'text' => $request->text,
                'icon' => $request->icon,
                'url' => $request->url,
                'parent' => $request->parent ?? 0,
                'order' => $request->order,
                'enabled' => $request->enabled ?? true,
                'active' => 1,
            ];

            $menu = Menu::create($data);

            // Asignar automáticamente el menú nuevo a nestorserrano
            $nestorserrano = User::where('name', 'nestorserrano')
                ->orWhere('email', 'nestorserrano@hyplast.com')
                ->first();

            if ($nestorserrano) {
                $menu->usuarios()->attach($nestorserrano->id);
            }

            Alert::success('Éxito', 'Menú creado correctamente y asignado a nestorserrano');
            return redirect()->route('menus.index');
        } catch (\Exception $e) {
            Alert::error('Error', 'No se pudo crear el menú: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Menu $menu)
    {
        $menusParent = Menu::where(function($q) {
            $q->where('parent', 0)->orWhereNull('parent');
        })
            ->where('id', '!=', $menu->id)
            ->where('enabled', true)
            ->orderBy('order')
            ->get();

        $iconos = $this->getIconosList();

        return view('menus.edit', compact('menu', 'menusParent', 'iconos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'text' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'parent' => 'nullable|integer',
            'order' => 'required|integer|min:0',
            'enabled' => 'boolean',
        ]);

        // Evitar que un menú sea su propio padre
        if ($request->parent == $menu->id) {
            Alert::error('Error', 'Un menú no puede ser su propio padre');
            return back()->withInput();
        }

        try {
            $data = [
                'text' => $request->text,
                'icon' => $request->icon,
                'url' => $request->url,
                'parent' => $request->parent ?? 0,
                'order' => $request->order,
                'enabled' => $request->enabled ?? true,
            ];

            $menu->update($data);

            Alert::success('Éxito', 'Menú actualizado correctamente');
            return redirect()->route('menus.index');
        } catch (\Exception $e) {
            Alert::error('Error', 'No se pudo actualizar el menú: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu)
    {
        try {
            // Verificar si tiene submenús
            if ($menu->hijos()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un menú que tiene submenús'
                ]);
            }

            $menu->delete();

            return response()->json([
                'success' => true,
                'message' => 'Menú eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el menú: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show form to assign users to menu
     */
    public function assign(Menu $menu)
    {
        $usuarios = User::orderBy('name')->get();
        $usuariosAsignados = $menu->usuarios()->pluck('users.id')->toArray();

        return view('menus.assign', compact('menu', 'usuarios', 'usuariosAsignados'));
    }

    /**
     * Update user assignments for menu
     */
    public function updateAssignments(Request $request, Menu $menu)
    {
        $request->validate([
            'usuarios' => 'nullable|array',
            'usuarios.*' => 'exists:users,id',
        ]);

        try {
            $menu->usuarios()->sync($request->usuarios ?? []);

            Alert::success('Éxito', 'Usuarios asignados correctamente al menú');
            return redirect()->route('menus.index');
        } catch (\Exception $e) {
            Alert::error('Error', 'No se pudieron asignar los usuarios: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Show user's menu assignments
     */
    public function userMenus(User $user)
    {
        $todosMenus = Menu::with('padre')
            ->where('enabled', true)
            ->orderBy('order')
            ->get()
            ->groupBy(function($menu) {
                return ($menu->parent && $menu->parent != 0) ? 'submenus' : 'principales';
            });

        $menusAsignados = $user->menus()->pluck('menus.id')->toArray();

        return view('menus.user-menus', compact('user', 'todosMenus', 'menusAsignados'));
    }

    /**
     * Update user's menu assignments
     */
    public function updateUserMenus(Request $request, User $user)
    {
        $request->validate([
            'menus' => 'nullable|array',
            'menus.*' => 'exists:menus,id',
        ]);

        try {
            $user->menus()->sync($request->menus ?? []);

            Alert::success('Éxito', 'Menús actualizados correctamente para el usuario');
            return redirect()->route('users.index');
        } catch (\Exception $e) {
            Alert::error('Error', 'No se pudieron actualizar los menús: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Get list of FontAwesome icons
     */
    private function getIconosList()
    {
        return [
            'fas fa-home' => 'Inicio',
            'fas fa-tachometer-alt' => 'Dashboard',
            'fas fa-users' => 'Usuarios',
            'fas fa-user' => 'Usuario',
            'fas fa-cog' => 'Configuración',
            'fas fa-boxes' => 'Productos',
            'fas fa-box' => 'Producto',
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
            'fas fa-th' => 'Módulos',
            'fas fa-list' => 'Listado',
            'fas fa-edit' => 'Editar',
            'fas fa-plus' => 'Agregar',
            'fas fa-minus' => 'Eliminar',
            'fas fa-check' => 'Aprobar',
            'fas fa-times' => 'Rechazar',
            'fas fa-bell' => 'Notificaciones',
            'fas fa-envelope' => 'Mensajes',
            'fas fa-wrench' => 'Herramientas',
            'fas fa-database' => 'Base de Datos',
            'fas fa-server' => 'Servidor',
            'fas fa-network-wired' => 'Red',
        ];
    }

    /**
     * API: Obtener menús de un usuario específico
     */
    public function getMenusUsuario($userId)
    {
        try {
            $menus = Menu::getMenusParaUsuario($userId);

            return response()->json([
                'success' => true,
                'menus' => $menus
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los menús: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Obtener todos los menús habilitados y los asignados a un usuario
     */
    public function getTodosYAsignados($userId)
    {
        try {
            // Obtener TODOS los menús habilitados como catálogo maestro
            $todosMenus = Menu::where('enabled', true)
                ->orderBy('parent')
                ->orderBy('order')
                ->get(['id', 'text', 'icon', 'parent']);

            // Obtener los menús que ya tiene asignados el usuario seleccionado
            $menusAsignados = DB::table('menu_user')
                ->where('user_id', $userId)
                ->pluck('menu_id')
                ->toArray();

            return response()->json([
                'success' => true,
                'todos' => $todosMenus,
                'asignados' => $menusAsignados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Asignar menús a un usuario
     */
    public function asignarMenusUsuario(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'menus' => 'nullable|array',
                'menus.*' => 'exists:menus,id'
            ]);

            $user = User::findOrFail($request->user_id);
            $user->menus()->sync($request->menus ?? []);

            return response()->json([
                'success' => true,
                'message' => 'Menús asignados correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar menús: ' . $e->getMessage()
            ], 500);
        }
    }
}
