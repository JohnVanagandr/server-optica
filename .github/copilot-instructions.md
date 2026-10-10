# Reglas de Desarrollo para Laravel Backend (SaaS)

## 1. Arquitectura y Patrones
- Eres un desarrollador experto en PHP 12+ y Laravel.
- Los Controladores deben mantenerse delgados. La lógica de negocio compleja debe ir en clases `Service` o `Action`.
- Para las respuestas de la API, SIEMPRE utiliza `JsonResource` (API Resources) de Laravel. Nunca devuelvas los modelos Eloquent directamente desde el controlador.
- Sigue el estándar RESTful para el nombramiento de rutas y métodos (index, show, store, update, destroy).

## 2. Autenticación y Permisos
- El sistema utiliza Laravel Sanctum para la autenticación de la API.
- El sistema de roles y permisos se maneja exclusivamente con el paquete `Spatie/laravel-permission`.
- Protege los endpoints utilizando middlewares de Spatie (ej. `middleware('permission:products.index')`) en lugar de validaciones manuales por rol dentro de la lógica.

## 3. Estándares de Código (Clean Code)
- Declara estrictamente los tipos de datos en los parámetros de las funciones y en los valores de retorno (Tipado fuerte).
- Utiliza las convenciones de nomenclatura de Laravel: `camelCase` para variables y métodos, `PascalCase` para clases y modelos, `snake_case` para columnas de base de datos.
- Agrupa las rutas de la API utilizando prefijos y middlewares para mantener `routes/api.php` limpio.

## 4. Respuestas API y Manejo de Errores
- Utiliza un formato de respuesta estándar para la API (ej. `{ "status": "success", "message": "...", "data": {...} }`).
- Maneja las excepciones correctamente usando bloques `try/catch` en los controladores, o delega el manejo global al archivo `bootstrap/app.php` o `Handler.php`.

## 5. Comunicación
- Todas las respuestas, explicaciones y comentarios de código que generes deben estar en **Español**.