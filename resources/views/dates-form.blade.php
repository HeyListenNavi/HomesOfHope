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

<body class="bg-body text-foreground flex min-h-screen flex-col items-center p-6 justify-center lg:p-8">
    <div
        class="bg-glass container col-span-2 mx-auto my-8 flex max-w-6xl flex-col items-center justify-center gap-10 rounded-2xl px-4 py-12 shadow-2xl backdrop-blur-xl lg:px-24">

        <img class="w-64 rounded-full"
            src="{{ asset('images/logo.png') }}"
            alt="">

        <div class="flex flex-col items-center gap-1">
            <h1 class="text-headline-large md:text-display-medium text-center">¡Casas de Esperanza!</h1>
        </div>

        @if (session('error'))
            <div class="text-red-800 font-bold">
                <p>{{ session('error') }}</p>
            </div>
        @endif
    </div>
</body>

</html>
