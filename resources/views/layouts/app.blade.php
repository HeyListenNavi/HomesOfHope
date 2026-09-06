<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-screen">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Homes of Hope' }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- Tailwind CSS (via Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        window.googleMapsApiKey = @js(config('services.google.maps_key', env('GOOGLE_MAPS_API_KEY')));
        
        (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b[c]||(b[c]={});var d=b[c].maps||(b[c].maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.googleapis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
            key: window.googleMapsApiKey,
            v: "weekly",
            language: "es",
            region: "MX",
        });
    </script>
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-body bg-fixed bg-cover min-h-screen font-sans antialiased text-white selection:bg-highlight selection:text-white">
    
    <main class="min-h-screen flex flex-col items-center justify-center">
        @yield('body')
        {{ $slot ?? '' }}
    </main>

    @livewireScripts
</body>
</html>
