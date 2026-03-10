<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $table = 'menus';

    protected $fillable = [
        'text',        // nombre del menú
        'icon',        // icono FontAwesome
        'url',         // ruta
        'parent_id',   // ID del menú padre
        'parent',      // ID del menú padre (compatibilidad)
        'order',       // orden
        'enabled',     // activo
        'active',      // patrón de activación
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'order' => 'integer',
        'parent_id' => 'integer',
    ];

    /**
     * Menú padre (para submenús)
     */
    public function padre()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    /**
     * Submenús hijos
     */
    public function hijos()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    /**
     * Submenús hijos activos
     */
    public function hijosActivos()
    {
        return $this->hasMany(Menu::class, 'parent_id')->where('enabled', true)->orderBy('order');
    }

    /**
     * Usuarios que tienen acceso a este menú
     */
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'menu_user')->withTimestamps();
    }

    /**
     * Verificar si es un menú raíz (sin padre)
     */
    public function esRaiz()
    {
        return $this->parent_id == 0 || is_null($this->parent_id);
    }

    /**
     * Verificar si tiene submenús
     */
    public function tieneHijos()
    {
        return $this->hijos()->count() > 0;
    }

    /**
     * Obtener la URL con placeholders reemplazados
     */
    public function getUrlAttribute($value)
    {
        if (!$value) {
            return $value;
        }

        // Reemplazar :username con el nombre del usuario actual
        if (strpos($value, ':username') !== false && auth()->check()) {
            $value = str_replace(':username', auth()->user()->name, $value);
        }

        // Reemplazar :user_id con el ID del usuario actual
        if (strpos($value, ':user_id') !== false && auth()->check()) {
            $value = str_replace(':user_id', auth()->id(), $value);
        }

        return $value;
    }

    /**
     * Obtener la ruta completa del menú (breadcrumb)
     */
    public function getRutaCompletaAttribute()
    {
        $ruta = collect([$this->text]);
        $padre = $this->padre;

        while ($padre && $padre->parent > 0) {
            $ruta->prepend($padre->text);
            $padre = $padre->padre;
        }

        return $ruta->implode(' > ');
    }

    /**
     * Scope: Solo menús raíz (sin padre)
     */
    public function scopeRaices($query)
    {
        return $query->where(function($q) {
            $q->where('parent', 0)->orWhereNull('parent');
        });
    }

    /**
     * Scope: Solo menús activos
     */
    public function scopeActivos($query)
    {
        return $query->where('enabled', true);
    }

    /**
     * Obtener menús jerárquicos para un usuario
     */
    public static function getMenusParaUsuario($userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return collect([]);
        }

        // Usuario nestorserrano siempre tiene acceso a todos los menús
        if (strtolower($user->name) === 'nestorserrano' ||
            strtolower($user->email) === 'nestorserrano@hyplast.com' ||
            strtolower($user->email) === 'nestorserrano2004@gmail.com') {
            $menusRaiz = static::where(function($q) {
                    $q->where('parent_id', 0)->orWhereNull('parent_id');
                })
                ->where('enabled', true)
                ->orderBy('order')
                ->get();

            // Cargar todos los hijos recursivamente
            $menusRaiz->each(function ($menu) {
                $menu->setRelation('hijos', static::cargarTodosHijosRecursivo($menu->id));
            });

            return $menusRaiz;
        }

        // Obtener IDs de menús del usuario
        $menuIds = $user->menus()->pluck('menus.id')->toArray();

        if (empty($menuIds)) {
            return collect([]);
        }

        // Obtener menús raíz del usuario
        $menusRaiz = static::whereIn('id', $menuIds)
            ->where(function($q) {
                $q->where('parent_id', 0)->orWhereNull('parent_id');
            })
            ->where('enabled', true)
            ->orderBy('order')
            ->get();

        // Cargar hijos recursivamente
        $menusRaiz->each(function ($menu) use ($menuIds) {
            $menu->setRelation('hijos', static::cargarHijosRecursivo($menu->id, $menuIds));
        });

        return $menusRaiz;
    }

    /**
     * Cargar hijos recursivamente solo si el usuario tiene acceso
     */
    private static function cargarHijosRecursivo($parentId, $menuIds)
    {
        $hijos = static::whereIn('id', $menuIds)
            ->where('parent_id', $parentId)
            ->where('enabled', true)
            ->orderBy('order')
            ->get();

        $hijos->each(function ($hijo) use ($menuIds) {
            $hijo->setRelation('hijos', static::cargarHijosRecursivo($hijo->id, $menuIds));
        });

        return $hijos;
    }

    /**
     * Cargar todos los hijos recursivamente (sin filtro de usuario)
     * Usado para usuarios con acceso total
     */
    private static function cargarTodosHijosRecursivo($parentId)
    {
        $hijos = static::where('parent_id', $parentId)
            ->where('enabled', true)
            ->orderBy('order')
            ->get();

        $hijos->each(function ($hijo) {
            $hijo->setRelation('hijos', static::cargarTodosHijosRecursivo($hijo->id));
        });

        return $hijos;
    }
}
