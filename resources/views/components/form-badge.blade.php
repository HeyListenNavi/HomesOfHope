@props([
    'type' => 'required', // required | optional
])

@if($type === 'required')
    <span class="bg-red-500/15 text-red-200 border border-red-300/30 px-3 py-1 rounded-full text-base font-medium ml-1">Obligatorio</span>
@elseif($type === 'optional')
    <span class="bg-white/10 text-white/70 border border-white/20 px-3 py-1 rounded-full text-base font-medium ml-1">Opcional</span>
@endif
