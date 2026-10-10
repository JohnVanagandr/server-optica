<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo Virtual - {{ $branch->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Usamos la configuración JSON para inyectar el color primario de la marca del cliente */
        :root {
            --primary-color: {{ $settings['primary_color'] ?? '#000000' }};
        }
        .bg-primary { background-color: var(--primary-color); }
        .text-primary { color: var(--primary-color); }
        .border-primary { border-color: var(--primary-color); }
    </style>
</head>
<body class="bg-gray-50 font-sans p-4">

    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            {{ $settings['title'] ?? 'Nuestro Catálogo' }}
        </h1>
        <p class="text-gray-500 mt-2">Respaldado por {{ $branch->account->name }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($productos as $producto)
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition-shadow">
                <div class="h-48 bg-gray-200 flex items-center justify-center relative">
                    <span class="text-gray-400">Imagen 3D</span>
                    <span class="absolute top-2 right-2 bg-white text-xs px-2 py-1 rounded-full text-gray-600 font-medium">
                        {{ $producto->category->name }}
                    </span>
                </div>
                
                <div class="p-5">
                    <h3 class="text-lg font-semibold text-gray-800">{{ $producto->name }}</h3>
                    <p class="text-sm text-gray-500 mb-4">{{ $producto->brand }}</p>
                    
                    <div class="flex justify-between items-center mt-4">
                        @if(isset($settings['show_prices']) && $settings['show_prices'])
                            <span class="text-xl font-bold text-gray-900">${{ $producto->base_price }}</span>
                        @else
                            <span class="text-sm text-gray-500">Consultar precio</span>
                        @endif
                        
                        <button class="bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:opacity-90 transition-opacity flex items-center gap-2">
                            <span>Probar en AR</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>