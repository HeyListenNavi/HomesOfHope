@extends('layouts.app', ['title' => $group->name])

@section('body')
<div class="min-h-screen p-4 md:p-10 flex flex-col">
    <div class="max-w-[1600px] w-full mx-auto mb-10 flex flex-col md:flex-row justify-between items-center bg-white p-6 rounded-2xl shadow-lg border border-zinc-200 gap-6">
        <div class="flex items-center gap-6">
            <img src="{{ asset('images/logo.png') }}" class="w-16 h-16 rounded-full border-2 border-highlight p-1 bg-[#f4f4f4]" alt="Logo">
            <div>
                <h1 class="text-headline-medium font-bold text-zinc-900 leading-tight">{{ $group->name }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="px-2 py-0.5 bg-highlight/10 text-highlight text-[10px] font-black uppercase tracking-widest rounded-md border border-highlight/20">Kiosco Oficial</span>
                    <p class="text-label-small text-zinc-500 uppercase tracking-widest">Asistencia de Grupo</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            @if($group->attendance_closed_at)
                <x-button 
                    wire:click="reOpenAttendance"
                    wire:confirm="¿Estás seguro de re-abrir el registro?"
                    class="bg-amber-500 hover:bg-amber-600 text-white"
                >
                    <i class='bx bx-refresh text-xl mr-2'></i>
                    Re-abrir Registro
                </x-button>
            @else
                <x-button 
                    wire:click="closeAttendance"
                    wire:confirm="¿Estás seguro de cerrar el registro definitivamente?"
                    class="bg-red-600 hover:bg-red-700 text-white"
                >
                    <i class='bx bxs-lock-alt text-xl mr-2'></i>
                    Finalizar y Cerrar
                </x-button>
            @endif
            
            <x-button.outline 
                href="/admin/groups" 
                class="!p-3 !bg-zinc-100 hover:!bg-zinc-200 !border-zinc-200 !rounded-xl !text-zinc-600"
            >
                <i class='bx bx-x text-2xl'></i>
            </x-button.outline>
        </div>
    </div>

    <div class="max-w-[1600px] w-full mx-auto space-y-10 flex-1">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-stretch">
            <div class="lg:col-span-4 flex flex-col gap-8">
                <div class="bg-white rounded-3xl border border-zinc-200 shadow-lg overflow-hidden flex flex-col flex-1">
                    <div class="p-8 border-b border-zinc-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-highlight rounded-xl">
                                <i class='bx bx-qr-scan text-2xl text-white'></i>
                            </div>
                            <span class="text-xl font-bold tracking-tight text-zinc-900">Escaneo</span>
                        </div>
                        <div class="flex items-center gap-2 px-3 py-1 bg-zinc-50 rounded-full border border-zinc-100">
                            <span class="w-2 h-2 rounded-full bg-highlight"></span>
                            <span class="text-[10px] font-bold text-zinc-500 uppercase">Live</span>
                        </div>
                    </div>
                    
                    <div class="p-10 flex-1 flex flex-col justify-center">
                        <div class="relative">
                            <input 
                                type="text" 
                                id="scan-input"
                                wire:model.live="scanCode"
                                wire:keydown.enter="processCode"
                                autofocus
                                autocomplete="off"
                                placeholder="{{ $group->attendance_closed_at ? 'CERRADO' : 'CÓDIGO' }}"
                                @disabled($group->attendance_closed_at !== null)
                                class="w-full text-center font-mono text-4xl p-8 rounded-2xl border-2 border-zinc-100 bg-zinc-50 text-highlight focus:border-highlight focus:ring-4 focus:ring-highlight/10 outline-none placeholder:text-zinc-300"
                            >
                        </div>
                        
                        <div class="mt-10 text-center space-y-4">
                            <p class="text-body-small text-zinc-500 px-6">
                                El lector registrará automáticamente al aplicante al detectar el código.
                            </p>
                        </div>
                    </div>

                    <div class="bg-zinc-50 p-8 border-t border-zinc-100 flex justify-between items-center">
                        <div>
                            <p class="text-[10px] font-black text-zinc-400 uppercase tracking-widest mb-1">Progreso</p>
                            <div class="flex items-baseline gap-1">
                                <span class="text-3xl font-black text-zinc-900">
                                    {{ collect($groupMembers)->where('attendance.status', \App\Enums\AttendanceStatus::Present)->count() }}
                                </span>
                                <span class="text-xl font-bold text-zinc-400">/ {{ count($groupMembers) }}</span>
                            </div>
                        </div>
                        <div class="w-24 h-24 relative">
                            @php 
                                $percent = count($groupMembers) > 0 ? (collect($groupMembers)->where('attendance.status', \App\Enums\AttendanceStatus::Present)->count() / count($groupMembers)) * 100 : 0;
                            @endphp
                            <svg class="w-full h-full -rotate-90">
                                <circle cx="48" cy="48" r="36" stroke="currentColor" stroke-width="8" fill="transparent" class="text-zinc-200" />
                                <circle cx="48" cy="48" r="36" stroke="currentColor" stroke-width="8" fill="transparent" class="text-highlight" stroke-dasharray="226.2" stroke-dashoffset="{{ 226.2 - (226.2 * $percent / 100) }}" />
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-[10px] font-black text-zinc-900">{{ round($percent) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8 min-h-[550px] flex flex-col">
                @if(!$scanResult)
                    <div class="flex-1 bg-white rounded-[2rem] border border-zinc-200 flex flex-col items-center justify-center p-12 text-center shadow-lg">
                        <div class="w-48 h-48 bg-zinc-50 rounded-full flex items-center justify-center mb-12 border border-zinc-100 relative">
                            <i class='bx bx-scan !text-7xl text-zinc-300'></i>
                        </div>
                        <h2 class="text-display-medium text-zinc-900 tracking-tight mb-4">Listo para validar</h2>
                        <p class="text-body-medium text-zinc-500 max-w-sm">
                            Escanea un código QR para iniciar el proceso de verificación.
                        </p>
                    </div>
                @else
                    <div class="flex-1 rounded-[2rem] border shadow-xl flex flex-col overflow-hidden
                        @if($scanResult === 'success') bg-highlight/5 border-highlight/20
                        @elseif($scanResult === 'warning') bg-amber-500/5 border-amber-500/20
                        @elseif($scanResult === 'danger') bg-red-600/5 border-red-600/20
                        @endif
                    ">
                        <div class="p-12 flex flex-col items-center justify-center text-center border-b border-zinc-100 flex-1 relative bg-white">
                            
                            <div class="mb-12">
                                @if($scanResult === 'success')
                                    <div class="bg-highlight p-10 rounded-full shadow-lg">
                                        <i class='bx bxs-check-circle !text-[48px] text-white'></i>
                                    </div>
                                @elseif($scanResult === 'warning')
                                    <div class="bg-amber-500 p-10 rounded-full shadow-lg">
                                        <i class='bx bxs-error-circle !text-[48px] text-white'></i>
                                    </div>
                                @elseif($scanResult === 'danger')
                                    <div class="bg-red-600 p-10 rounded-full shadow-lg">
                                        <i class='bx bxs-x-circle !text-[48px] text-white'></i>
                                    </div>
                                @endif
                            </div>

                            <h1 class="text-display-large text-zinc-900 tracking-tighter mb-6">
                                @if($scanResult === 'success' && $lastScannedApplicant)
                                    {{ explode(' ', $lastScannedApplicant->applicant_name)[0] }}
                                @elseif($scanResult === 'warning')
                                    Atención
                                @elseif($scanResult === 'danger')
                                    Error
                                @endif
                            </h1>
                            
                            <p class="text-headline-medium font-bold px-12
                                @if($scanResult === 'success') text-highlight
                                @elseif($scanResult === 'warning') text-amber-500
                                @elseif($scanResult === 'danger') text-red-500
                                @endif
                            ">
                                {{ $scanMessage }}
                            </p>
                        </div>

                        @if($lastScannedApplicant && $scanResult !== 'danger')
                            <div class="bg-zinc-50 px-12 py-10 flex items-center justify-between border-t border-zinc-100">
                                <div class="flex items-center gap-8">
                                    <div class="px-5 py-4 rounded-2xl bg-white border border-zinc-200">
                                        @if($lastScannedApplicant->gender === 'man')
                                            <i class='bx bxs-user-circle !text-3xl text-blue-500'></i>
                                        @else
                                            <i class='bx bxs-user-circle !text-3xl text-pink-500'></i>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-label-small text-zinc-400 uppercase tracking-[0.2em] mb-2">aplicante Validado</p>
                                        <p class="text-headline-medium text-zinc-900 leading-none">{{ $lastScannedApplicant->applicant_name }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-label-small text-zinc-400 uppercase tracking-[0.2em] mb-2">Identificador</p>
                                    <p class="text-2xl font-mono font-bold text-highlight bg-highlight/5 px-5 py-2 rounded-xl border border-highlight/20 inline-block">
                                        {{ strtoupper($lastScannedApplicant->curp) }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-[2rem] border border-zinc-200 shadow-lg overflow-hidden" x-data="{ openId: null }">
            <div class="p-10 border-b border-zinc-100 flex flex-col md:flex-row items-center justify-between gap-6 bg-zinc-50/30">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-white rounded-xl border border-zinc-200">
                        <i class='bx bx-group text-4xl text-zinc-400'></i>
                    </div>
                    <div>
                        <span class="text-headline-medium font-bold text-zinc-900 tracking-tight">Registro General</span>
                        <p class="text-label-small text-zinc-500 uppercase tracking-widest mt-1">Miembros del Grupo</p>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <div class="flex items-center gap-3 px-6 py-3 bg-highlight/5 rounded-xl border border-highlight/10">
                        <span class="w-3 h-3 rounded-full bg-highlight"></span>
                        <span class="text-xs font-black text-highlight uppercase tracking-widest">Presentes</span>
                    </div>
                    <div class="flex items-center gap-3 px-6 py-3 bg-zinc-100 rounded-xl border border-zinc-200">
                        <span class="w-3 h-3 rounded-full bg-zinc-300"></span>
                        <span class="text-xs font-black text-zinc-500 uppercase tracking-widest">Pendientes</span>
                    </div>
                </div>
            </div>

            <div class="divide-y divide-zinc-100">
                <div class="grid grid-cols-12 bg-zinc-50 p-6 text-[10px] font-black text-zinc-400 uppercase tracking-[0.2em]">
                    <div class="col-span-5 md:col-span-6">Aplicante</div>
                    <div class="col-span-4 md:col-span-3 text-center">Estado</div>
                    <div class="col-span-3 md:col-span-3 text-right pr-10">Ficha</div>
                </div>

                @forelse($groupMembers as $member)
                    <div :class="openId === {{ $member->id }} ? 'bg-zinc-50' : ''">
                        <div class="grid grid-cols-12 p-6 items-center hover:bg-zinc-50/50 cursor-pointer" @click="openId = (openId === {{ $member->id }} ? null : {{ $member->id }})">
                            <div class="col-span-5 md:col-span-6 flex items-center gap-5">
                                <div class="w-14 h-14 rounded-xl flex items-center justify-center border border-zinc-100 shadow-sm
                                    @if($member->attendance?->status === \App\Enums\AttendanceStatus::Present) bg-highlight text-white
                                    @elseif($member->attendance?->status === \App\Enums\AttendanceStatus::Absent) bg-red-600 text-white
                                    @else bg-zinc-100 text-zinc-400 @endif
                                ">
                                    @if($member->attendance?->status === \App\Enums\AttendanceStatus::Present) <i class='bx bxs-check-circle text-2xl'></i>
                                    @elseif($member->attendance?->status === \App\Enums\AttendanceStatus::Absent) <i class='bx bxs-x-circle text-2xl'></i>
                                    @else <i class='bx bxs-user text-2xl'></i> @endif
                                </div>
                                <div>
                                    <p class="text-body-small font-bold text-zinc-900 group-hover:text-highlight">{{ $member->applicant_name }}</p>
                                    <p class="text-xs font-mono text-zinc-400 uppercase mt-0.5 tracking-tighter">{{ strtoupper($member->curp) }}</p>
                                </div>
                            </div>

                            <div class="col-span-4 md:col-span-3 flex justify-center">
                                @php $s = $member->attendance?->status; @endphp
                                <span class="px-5 py-2 rounded-lg text-[10px] font-black uppercase tracking-[0.15em] border
                                    @if($s === \App\Enums\AttendanceStatus::Present) bg-highlight/5 text-highlight border-highlight/20
                                    @elseif($s === \App\Enums\AttendanceStatus::Absent) bg-red-600/5 text-red-600 border-red-600/20
                                    @else bg-zinc-50 text-zinc-400 border-zinc-200 @endif
                                ">
                                    {{ $member->attendance?->status->getLabel() ?? 'Pendiente' }}
                                </span>
                            </div>

                            <div class="col-span-3 md:col-span-3 flex justify-end pr-6">
                                <div class="p-3 rounded-lg border border-zinc-100">
                                    <i class='bx bx-chevron-down text-xl text-zinc-400 transition-transform' x-bind:class="openId === {{ $member->id }} ? 'rotate-180 text-highlight' : ''"></i>
                                </div>
                            </div>
                        </div>

                        <div x-show="openId === {{ $member->id }}" x-collapse x-cloak class="px-8 pb-10">
                            <div class="bg-zinc-50 rounded-2xl border border-zinc-200 p-10 grid grid-cols-1 lg:grid-cols-2 gap-12 shadow-inner">
                                <div class="space-y-8">
                                    <div class="flex items-center gap-3 border-b border-zinc-200 pb-4">
                                        <i class='bx bx-id-card text-xl text-highlight'></i>
                                        <h5 class="text-xs font-black text-zinc-900 uppercase tracking-[0.2em]">Datos de Contacto</h5>
                                    </div>
                                    <div class="grid grid-cols-2 gap-8">
                                        <div class="bg-white p-5 rounded-xl border border-zinc-200">
                                            <p class="text-[10px] font-bold text-zinc-400 uppercase mb-2 tracking-widest">WhatsApp</p>
                                            <a href="https://wa.me/{{ $member->chat_id }}" target="_blank" class="text-lg font-black text-highlight flex items-center gap-2 hover:underline">
                                                <i class='bx bxl-whatsapp text-2xl'></i>
                                                {{ str_starts_with($member->chat_id, '521') ? substr($member->chat_id, 3) : $member->chat_id }}
                                            </a>
                                        </div>
                                        <div class="bg-white p-5 rounded-xl border border-zinc-200">
                                            <p class="text-[10px] font-bold text-zinc-400 uppercase mb-2 tracking-widest">Escaneado</p>
                                            <p class="text-sm font-bold text-zinc-900 flex items-center gap-2">
                                                <i class='bx bx-time text-xl text-zinc-400'></i>
                                                {{ $member->attendance?->scanned_at ? $member->attendance->scanned_at->format('d/m/Y - h:i A') : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-8">
                                    <div class="flex items-center gap-3 border-b border-zinc-200 pb-4">
                                        <i class='bx bx-comment-detail text-xl text-highlight'></i>
                                        <h5 class="text-xs font-black text-zinc-900 uppercase tracking-[0.2em]">Perfil de Entrevista</h5>
                                    </div>
                                    <div class="space-y-4 max-h-72 overflow-y-auto pr-4 custom-scrollbar">
                                        @forelse($member->responses as $response)
                                            <div class="p-5 bg-white rounded-xl border border-zinc-200">
                                                <p class="text-[10px] font-bold text-highlight/70 mb-2 uppercase leading-tight tracking-wider">{{ $response->question_text_snapshot }}</p>
                                                <p class="text-sm font-bold text-zinc-900 leading-relaxed">{{ $response->user_response }}</p>
                                            </div>
                                        @empty
                                            <div class="text-center py-10 bg-white rounded-2xl border border-dashed border-zinc-200">
                                                <p class="text-sm text-zinc-400 italic">No hay respuestas en el sistema.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-32 text-center bg-zinc-50 rounded-2xl border border-dashed border-zinc-200">
                        <i class='bx bx-group text-6xl text-zinc-200 mb-6'></i>
                        <p class="text-zinc-400 font-bold text-xl uppercase tracking-widest">Grupo sin aplicantes</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', () => {
            document.getElementById('scan-input')?.focus();
        });
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(0,0,0,0.05); border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(97, 179, 70, 0.3); border-radius: 10px; border: 2px solid transparent; background-clip: content-box; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(97, 179, 70, 0.5); border-radius: 10px; border: 2px solid transparent; background-clip: content-box; }
    </style>
</div>
@endsection
