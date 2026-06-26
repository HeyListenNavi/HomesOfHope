@extends('layouts.app', ['title' => 'Invitación - Casas de Esperanza'])

@section('body')
<div
    class="bg-glass container col-span-2 mx-auto my-8 flex max-w-6xl flex-col items-center justify-center gap-10 rounded-2xl px-4 py-12 shadow-2xl backdrop-blur-xl lg:px-24">
    <img class="w-64 rounded-full"
        src="{{ asset('images/logo.png') }}"
        alt="Logo">

    <h1 class="text-headline-large md:text-display-medium text-center font-bold">Invitación de Entrevista</h1>

    <div class="w-full max-w-4xl space-y-6">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="flex flex-col gap-2 bg-zinc-50 p-4 rounded-xl shadow-inner border border-zinc-100">
                <p class="text-label-small text-zinc-400 uppercase tracking-widest font-black">Aplicante</p>
                <p class="text-body-medium font-bold text-zinc-800">{{ $applicant->applicant_name }}</p>
            </div>

            <div class="flex flex-col gap-2 bg-zinc-50 p-4 rounded-xl shadow-inner border border-zinc-100">
                <p class="text-label-small text-zinc-400 uppercase tracking-widest font-black">Fecha y Hora</p>
                <p class="text-body-medium font-bold text-zinc-800">{{ $applicant->group->date_time->translatedFormat('l d M, Y') }} a las {{ $applicant->group->date_time->translatedFormat('h:i A') }}</p>
            </div>

            <div class="col-span-2 flex flex-col gap-2 span-2 bg-zinc-50 p-4 rounded-xl shadow-inner border border-zinc-100">
                <p class="text-label-small text-zinc-400 uppercase tracking-widest font-black">Ubicación</p>
                <p class="text-body-medium font-bold text-zinc-800">{{ $applicant->group->location }}</p>
            </div>

            @if($applicant->group->location_link)
            <x-button class="col-span-2 w-full text-label-large" href="{{ $applicant->group->location_link }}" target="_blank">
                <i class="bx bxs-map text-xl mr-3"></i>
                <span>Abrir ubicación en Google Maps</span>
            </x-button>
            @endif

        </div>

        @if($applicant->group->message)
        <div class="bg-zinc-50 p-6 rounded-xl shadow-inner border border-zinc-100">
            <p class="text-label-small text-zinc-400 mb-2 uppercase tracking-widest font-black">Información Importante</p>
            <div class="text-zinc-700 leading-relaxed">
                {!! nl2br(e($applicant->group->message)) !!}
            </div>
        </div>
        @endif
    </div>

    @if($applicant->attendance?->attendance_code && $qrCode)
    <div class="flex flex-col items-center bg-zinc-50 p-8 rounded-2xl shadow-inner border border-zinc-100">
        <p class="text-label-small text-zinc-400 mb-4 uppercase tracking-widest font-black">Tu Código de Asistencia</p>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-zinc-200 mb-4">
            <img src="{{ $qrCode }}" alt="QR Code" class="w-40 h-40">
        </div>
        <span class="text-3xl font-mono font-black tracking-[0.3em] text-zinc-900">{{ $applicant->attendance->attendance_code }}</span>
        <p class="text-sm text-zinc-500 mt-4 text-center max-w-xs">⚠️ Guarda este Código para entrar a tu Entrevista</p>
    </div>
    @endif

    <p class="text-lg text-zinc-100 text-center">Presente este documento (digital o en su teléfono) al llegar. <br> Generado el {{ now()->format('d/m/Y H:i') }} • ID: {{ $applicant->id }}</p>

    <div class="flex flex-col sm:flex-row gap-4 w-full justify-center">
        <x-button class="text-label-large" href="{!! URL::temporarySignedRoute('invitation.show', now()->addDays(3), ['applicant' => $applicant, 'pdf' => 1]) !!}">
            <span>Descargar Invitación en PDF</span>
            <i class="bx bxs-download ml-2 w-5 !text-2xl"></i>
        </x-button>

        <x-button.outline class="text-label-large" href="{{ $whatsAppUrl }}">
            <span>Contactar por WhatsApp</span>
            <x-bx-arrow-up-right></x-bx-arrow-up-right>
        </x-button.outline>
    </div>
</div>
@endsection