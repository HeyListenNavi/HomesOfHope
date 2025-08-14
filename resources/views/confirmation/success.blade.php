<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación Exitosa</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-lg text-center">
        <h1 class="text-2xl font-bold mb-4 text-green-600">¡Confirmación Exitosa!</h1>
        <p class="mb-6 text-gray-700">
            @if(session('success'))
                {{ session('success') }}
            @else
                Su solicitud ha sido procesada correctamente.
            @endif
        </p>
        <a href="/" class="text-blue-500 hover:underline">Volver a la página principal</a>
    </div>
</body>
</html>