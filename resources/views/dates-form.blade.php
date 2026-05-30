@extends('layouts.app', ['title' => 'Casas de Esperanza'])

@section('body')
    <div
        class="bg-glass container col-span-2 mx-auto flex max-w-6xl flex-col items-center justify-center gap-10 rounded-2xl px-4 py-12 shadow-2xl backdrop-blur-xl lg:px-24">

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
@endsection