# Hyplast Menús por Usuario - Sistema de Navegación Dinámica

## Descripción
Sistema que genera menús de navegación dinámicos basados en roles y permisos del usuario, con soporte multi-nivel y configuración personalizada.

## Características Principales
- 🎯 Menús dinámicos por rol
- 📊 Estructura multinivel (3 niveles)
- 🔒 Filtrado por permisos
- 🏢 Soporte multi-empresa
- ⚡ Cache de menús
- 🎨 Iconos personalizables
- 📱 Responsive design

## Modelos Principales
- **Menu**: Menús del sistema
- **UserConjuntoPivot**: Acceso por conjunto/empresa

## Estructura de Menús
```
Nivel 1: Módulo principal
  └─ Nivel 2: Submódulo
      └─ Nivel 3: Funcionalidad específica
```

## API Endpoints
```
GET    /api/menus/user             # Menús del usuario actual
GET    /api/menus/structure        # Estructura completa
POST   /api/menus                  # Crear menú
PUT    /api/menus/{id}             # Actualizar menú
```

## Configuración de Menús
```php
[
    'label' => 'CRM',
    'slug' => 'crm',
    'icon' => 'fa-users',
    'order' => 1,
    'permission' => 'view-crm',
    'parent_id' => null
]
```

## Requisitos
- PHP >= 8.1
- Laravel >= 10.x

## Instalación
```bash
composer install
php artisan migrate
php artisan db:seed --class=MenuSeeder
```

## Autor y Propietario
**Néstor Serrano**  
Desarrollador Full Stack  
GitHub: [@nestorserrano](https://github.com/nestorserrano)

## Licencia
Propietario - © 2026 Néstor Serrano. Todos los derechos reservados.
