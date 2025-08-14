<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Grupo</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-lg">
        <h1 class="text-2xl font-bold mb-6 text-center">Confirmar su Lugar</h1>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <p class="mb-4">Hola {{ $applicant->name ?? 'solicitante' }}, su lugar ha sido aprobado.</p>
        <p class="mb-4">Se le ha asignado al <strong>{{ $currentGroup->name }}</strong>, con fecha programada para el <strong>{{ \Carbon\Carbon::parse($currentGroup->date)->format('d/m/Y') }}</strong>.</p>

        <form action="{{ route('confirmation.confirm', $applicant) }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <label for="new_group_id" class="block text-gray-700 font-bold mb-2">
                    Si lo desea, puede elegir otra fecha disponible:
                </label>
                <select name="new_group_id" id="new_group_id" class="block w-full mt-1 p-2 border border-gray-300 rounded">
                    <option value="{{ $currentGroup->id }}">Mantener mi grupo actual ({{ \Carbon\Carbon::parse($currentGroup->date)->format('d/m/Y') }})</option>
                    @foreach ($availableGroups as $group)
                        <option value="{{ $group->id }}">
                            {{ $group->name }} ({{ \Carbon\Carbon::parse($group->date)->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="text-center">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Confirmar mi lugar
                </button>
            </div>
        </form>
    </div>
</body>
</html>