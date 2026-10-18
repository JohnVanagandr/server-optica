<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $widget->title ?? 'Catálogo Virtual' }}</title>

    <!-- Inyectamos los datos para React -->
    <script>
        window.WIDGET_PROPS = {
            config: @json($widget),
            products: @json($products)
        };
    </script>

    <!-- Cargamos Tailwind y React simultáneamente -->
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/widget.jsx'])
</head>
<body style="background-color: {{ $widget->background_color ?? '#f8fafc' }}; margin: 0; padding: 0; overflow-x: hidden;">
    <div id="widget-react-root"></div>
</body>
</html>