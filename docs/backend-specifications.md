# Especificaciones del Proyecto Backend (Laravel)

## 1. Arquitectura y Estándares de Código
*   **Clean Architecture:** Implementación estricta de capas. Los controladores se mantendrán delgados y se encargarán exclusivamente de recibir las peticiones HTTP y retornar respuestas. Toda la lógica de negocio se refactorizará y aislará en clases de servicio (`Service Layer`).
*   **Gestión de Datos:** Uso de `JsonResource` (API Resources) para la transformación de modelos Eloquent a respuestas JSON estandarizadas.
*   **Control de Versiones:** Adopción de la estrategia de ramificación **Git Flow**. Los mensajes de commit deben seguir la sintaxis de **Conventional Commits** (`feat:`, `fix:`, `chore:`, `refactor:`).
*   **Colaboración:** Uso de flujos de trabajo en GitHub con revisiones por pares (peer-audit workflows) antes de integrar cambios a las ramas principales.

## 2. Autenticación y Autorización
*   **Seguridad Base:** Laravel Sanctum para la emisión y validación de tokens de acceso API.
*   **Control de Acceso Granular:** Implementación de permisos específicos utilizando `Spatie/laravel-permission` (ej. `products.store`, `widgets.index`). Se prohíbe la validación directa por roles (`admin`, `client`) en la lógica de negocio; todo se basará en permisos individuales.

## 3. Módulos Principales
*   **Módulo de Cuentas y Sucursales (Tenancy):** Gestión de la relación `Account -> Branch -> User`.
*   **Módulo de Suscripciones (Simulado):** 
    *   Almacenamiento de los tipos de plan (`emprendedor`, `pro`) directamente en la tabla `accounts`.
    *   Endpoints para consultar estado de suscripción y renovaciones.
*   **Módulo de Catálogos (Widgets):**
    *   Control de creación de un (1) único widget por sucursal.
    *   Validación estricta de límites de productos (30 para emprendedor, 60 para profesional) directamente en la lógica del backend antes de la inserción en la base de datos pivote.

## 4. Estructura de Rutas
*   Separación en dominios dentro de `routes/api.php` mediante prefijos y grupos de middleware:
    *   `/api/admin/*`: Dominio exclusivo para la administración del SaaS.
    *   `/api/client/*`: Dominio exclusivo para los inquilinos (ópticas).