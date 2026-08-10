@props([
    'message' => '',
])

@if($message)
    <span class="text-red-300 font-bold text-xl block mt-2">⚠ {{ $message }}</span>
@endif
