@extends('layouts.app', ['title' => 'Enlace Inválido - Casas de Esperanza'])

@section('body')
<div
    class="bg-glass container col-span-2 mx-auto my-8 flex max-w-6xl flex-col items-center justify-center gap-10 rounded-2xl px-4 py-12 shadow-2xl backdrop-blur-xl lg:px-24">
    <img class="w-64 rounded-full"
        src="{{ asset('images/logo.png') }}"
        alt="Logo">

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