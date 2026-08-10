@props([
    'icon' => '',
    'iconColor' => 'highlight',
    'title' => '',
    'subtitle' => '',
])

<div class="text-center flex flex-col items-center gap-4">
    @if($icon)
        <div class="w-24 h-24 bg-white/10 border-2 border-{{ $iconColor }} rounded-full flex items-center justify-center">
            <i class='bx {{ $icon }} bx-lg text-{{ $iconColor }}'></i>
        </div>
    @endif
    <h1 class="text-4xl md:text-5xl font-bold text-white leading-tight">{{ $title }}</h1>
    @if($subtitle)
        <p class="text-2xl text-white/80">{{ $subtitle }}</p>
    @endif
</div>
