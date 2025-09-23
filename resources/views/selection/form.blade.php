<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">

    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-body text-foreground flex min-h-screen flex-col items-center justify-center p-6 lg:p-8">
    <div
        class="bg-glass container col-span-2 mx-auto my-8 flex max-w-6xl flex-col items-center justify-center gap-10 rounded-2xl px-4 py-12 shadow-2xl backdrop-blur-xl lg:px-24">
        <img class="w-64 rounded-full"
            src="https://scontent.fmxl1-1.fna.fbcdn.net/v/t39.30808-6/243359537_1586709375004712_6572829814851329979_n.png?_nc_cat=102&ccb=1-7&_nc_sid=6ee11a&_nc_ohc=aJvqvXPaL04Q7kNvwFKOWsD&_nc_oc=AdnvdWMpppVyB9HrWB7c3Iq1B2oBZHjraDN0SLO2L9Y4Tspqd36uQVPeUwrjWvzGTr0&_nc_zt=23&_nc_ht=scontent.fmxl1-1.fna&_nc_gid=Y1Byid_hmiNWs6xL4SZO5Q&oh=00_AfVGG7PTQ2FoVyClmD-fiZJL3bTQNSWvARMkAoeZcUAHqQ&oe=68A3A2C6"
            alt="">

        <div class="flex flex-col items-center gap-1">
            <h2 class="text-body-medium md:text-body-largefont-normal">Casas de Esperanza</h2>
            <h1 class="text-headline-large md:text-display-medium text-center">¡Es momento de elegir tu entrevista!</h1>
        </div>

        @if (session('error'))
            <div class="font-bold text-red-800">
                <p>{{ session('error') }}</p>
            </div>
        @endif


        <p class="mb-4">Hola {{ $applicant->applicant_name ?? 'solicitante' }}, aquí puedes escoger la fecha de tu
            entrevista personal. Solo selecciona la que mejor se acomode a tu horario entre las disponibles.</p>

        <form action="{{ route('group.selection.assign', $applicant) }}" method="POST" class="w-full">
            @csrf

            <fieldset class="grid gap-8 md:grid-cols-2 py-4">
                @foreach ($availableGroups as $group)
                    <x-form-input id="{{ $group->id }}" value="{{ $group->id }}" name="group_id[]" type="radio">
                        <div class="ml-2">
                            ✏️ <span class="font-bold">Nombre:</span> {{ $group->name }}
                            <br>
                            🕘 <span class="font-bold">Fecha:</span> {{ \Carbon\Carbon::parse($group->date)->format('d/m/Y') }}
                            <br>
                            📍 <span class="font-bold">Lugar:</span> {{ $group->location }}
                        </div>
                    </x-form-input>
                @endforeach
            </fieldset>

            @if ($errors->any())
                <div class="font-bold text-red-800">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-button class="text-label-large mx-auto" type="submit">
                <span>Confirmar mi Lugar</span>
                <x-bx-arrow-up-right></x-bx-arrow-up-right>
            </x-button>
        </form>
    </div>
</body>

</html>
