@extends('layouts.app', ['title' => 'Elegir Entrevista - Casas de Esperanza'])

@section('body')
<div
    class="bg-glass container col-span-2 mx-auto my-8 flex max-w-6xl flex-col items-center justify-center gap-10 rounded-2xl px-4 py-12 shadow-2xl backdrop-blur-xl lg:px-24">
    <img class="w-64 rounded-full"
        src="{{ asset('images/logo.png') }}"
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


    <p class="mb-4">Hola {{ $applicant->applicant_name ?? 'solicitante' }}, elige la fecha que mejor se acomode a tu
        horario.</p>

    <form action="{{ URL::temporarySignedRoute('group.selection.assign', now()->addDays(3), ['applicant' => $applicant]) }}" method="POST" class="w-full">
        @csrf

        @if ($availableGroups->isEmpty())
            <div class="rounded-2xl border-2 border-dashed border-white/25 bg-white/10 p-8 text-center">
                <p class="text-body-medium">Por el momento no hay fechas disponibles.</p>
                <p class="mt-2 text-body-small text-white/70">Te contactaremos en cuanto haya nuevos horarios.</p>
            </div>
        @else
            <div class="mb-2">
                <h2 class="text-label-large font-bold">Elige tu fecha y lugar</h2>
                <p class="mt-1 text-body-small text-white/80">Toca la tarjeta de la opción que prefieras — se marcará en
                    verde. Luego presiona <strong>Confirmar mi Lugar</strong>.</p>
            </div>

            <fieldset class="grid gap-6 py-4 md:grid-cols-2">
                <legend class="sr-only">Elige la fecha de tu entrevista</legend>
                @foreach ($availableGroups as $group)
                    <x-form-input id="{{ $group->id }}" value="{{ $group->id }}" name="group_id[]" type="radio">
                        <div class="min-w-0 text-left">
                            <div class="text-label-large font-bold">
                                ✏️ {{ $group->name }}
                            </div>
                            <div class="mt-1 text-body-small">
                                🕘 {{ \Carbon\Carbon::parse($group->date_time)->translatedFormat('l d M, Y - h:i A') }}
                            </div>
                            <div class="text-body-small">
                                📍 {{ $group->location }}
                            </div>
                            <div class="mt-2 text-label-medium font-bold">
                                🟢 {{ $group->capacity - $group->current_members_count }} lugares libres
                            </div>
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
        @endif
    </form>
</div>
@endsection