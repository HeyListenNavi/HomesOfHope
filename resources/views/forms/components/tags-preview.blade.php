<div class="flex flex-wrap gap-2">
    @foreach($tags as $tag)
        @php
            $color = $tag->color ?? '#61b346';
        @endphp
        <div class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium" 
             style="background-color: {{ $color }}12; 
                    color: {{ $color }}; 
                    border: 1px solid {{ $color }}4F;">
            {{ $tag->name }}
        </div>
    @endforeach
</div>
