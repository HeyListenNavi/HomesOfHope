@extends('layouts.app', ['title' => 'Registro Exitoso - Casas de Esperanza'])

@section('body')
<div
    class="bg-glass container col-span-2 mx-auto my-8 flex max-w-6xl flex-col items-center justify-center gap-10 rounded-2xl px-4 py-12 shadow-2xl backdrop-blur-xl lg:px-24">
    <img class="w-64 rounded-full"
        src="{{ asset('images/logo.png') }}"
        alt="Logo">

    <div class="flex flex-col items-center gap-1">
        <h2 class="text-body-medium md:text-body-largefont-normal">Casas de Esperanza</h2>
        <h1 class="text-headline-large md:text-display-medium text-center">¡Registro completado con éxito! </h1>
    </div>

    @if($applicant->attendance?->attendance_code && $qrCode)
        <div class="flex flex-col items-center bg-zinc-50 p-8 rounded-2xl shadow-inner border border-zinc-100">
            <p class="text-label-small text-zinc-400 mb-4 uppercase tracking-widest font-black">Tu Código de Asistencia</p>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-zinc-200 mb-4">
                <img src="{{ $qrCode }}" alt="QR Code" class="w-40 h-40">
            </div>
            <span class="text-3xl font-mono font-black tracking-[0.3em] text-zinc-900">{{ $applicant->attendance->attendance_code }}</span>
            <p class="text-xs text-zinc-500 mt-4 text-center max-w-xs">⚠️ Guarda este código o toma una captura de pantalla para entrar a tu entrevista.</p>
        </div>
    @endif

    <div class="max-w-2xl text-center">
        @if ($applicant->group && $applicant->group->message)
        <p class="text-md leading-8">
            {!! nl2br(e($applicant->group->message)) !!}
        </p>
        @endif
    </div>

    <div class="flex flex-col sm:flex-row gap-4 w-full justify-center">
        <x-button class="text-label-large" href="{!! URL::temporarySignedRoute('invitation.show', now()->addDays(3), ['applicant' => $applicant, 'pdf' => 1]) !!}">
            <span>Descargar Invitación</span>
            <i class="bx bxs-download ml-2 w-5 !text-2xl"></i>
        </x-button>

        <x-button.outline class="text-label-large" href="{{ $whatsAppUrl }}">
            <span>Volver a WhatsApp</span>
            <x-bx-arrow-up-right></x-bx-arrow-up-right>
        </x-button.outline>
    </div>
</div>
@endsection