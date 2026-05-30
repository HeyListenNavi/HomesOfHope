@extends('layouts.app', ['title' => 'Enlace Inválido - Casas de Esperanza'])

@section('body')
<div
    class="bg-glass container col-span-2 mx-auto my-8 flex max-w-6xl flex-col items-center justify-center gap-10 rounded-2xl px-4 py-12 shadow-2xl backdrop-blur-xl lg:px-24">
    <img class="w-64 rounded-full"
        src="https://scontent.fmxl1-1.fna.fbcdn.net/v/t39.30808-6/243359537_1586709375004712_6572829814851329979_n.png?_nc_cat=102&ccb=1-7&_nc_sid=6ee11a&_nc_ohc=aJvqvXPaL04Q7kNvwFKOWsD&_nc_oc=AdnvdWMpppVyB9HrWB7c3Iq1B2oBZHjraDN0SLO2L9Y4Tspqd36uQVPeUwrjWvzGTr0&_nc_zt=23&_nc_ht=scontent.fmxl1-1.fna&_nc_gid=Y1Byid_hmiNWs6xL4SZO5Q&oh=00_AfVGG7PTQ2FoVyClmD-fiZJL3bTQNSWvARMkAoeZcUAHqQ&oe=68A3A2C6"
        alt="">

    <div class="flex flex-col items-center gap-1">
        <h2 class="text-body-medium md:text-body-largefont-normal">Casas de Esperanza</h2>
        <h1 class="text-headline-large md:text-display-medium text-center">Hubo un problema con tu cita...</h1>
    </div>

    <p>
        @if (session('success'))
            {{ session('success') }}
        @else
            Su solicitud no ha sido procesada correctamente.
        @endif
    </p>

    <x-button class="text-label-large mx-auto" href="/">
        <span>Volver a la página principal</span>
        <x-bx-arrow-up-right></x-bx-arrow-up-right>
    </x-button>
</div>
@endsection