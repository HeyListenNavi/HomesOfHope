@props([
    'message' => '',
])

@if ($message)
    <span class="text-red-300 font-bold text-xl flex items-center gap-1.5 mt-2">
        <i class='bx bxs-error-circle shrink-0'></i> {{ $message }}
    </span>
@endif
