<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $portalTitle }} — Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: {{ $primaryColor }};
            --primary-light: {{ $primaryLightColor }};
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        .btn-primary {
            background: var(--primary);
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background: var(--primary-light);
            filter: brightness(1.1);
        }
        .ring-primary:focus {
            --tw-ring-color: color-mix(in srgb, var(--primary) 30%, transparent);
            border-color: color-mix(in srgb, var(--primary) 60%, transparent);
        }

        .bg-brand-gradient {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        }

        .login-left {
            background: linear-gradient(180deg, color-mix(in srgb, var(--primary) 95%, white) 0%, var(--primary) 100%);
        }

        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 30px white inset !important;
            -webkit-text-fill-color: #1e293b !important;
        }

        @media (max-width: 768px) {
            .login-left { display: none; }
        }
    </style>
</head>
<body class="min-h-screen flex">

    {{-- LEFT SIDE — Brand & Illustration --}}
    <div class="login-left w-full md:w-1/2 min-h-screen flex flex-col justify-between p-8 md:p-12 lg:p-16 text-white relative overflow-hidden">
        {{-- Decorative circles --}}
        <div class="absolute -top-20 -right-20 w-64 h-64 rounded-full bg-white/5"></div>
        <div class="absolute -bottom-10 -left-10 w-48 h-48 rounded-full bg-white/5"></div>

        {{-- Brand --}}
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-8">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" class="h-12 w-auto object-contain rounded-lg">
                @else
                    <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center text-white font-bold text-xl backdrop-blur-sm">
                        {{ strtoupper(substr($schoolName, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <h2 class="text-lg font-bold tracking-tight">{{ $schoolName }}</h2>
                    @if($schoolNpsn)
                        <p class="text-xs text-white/60">NPSN: {{ $schoolNpsn }}</p>
                    @endif
                </div>
            </div>

            <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold leading-tight mb-4">
                {{ $welcomeText }}
            </h1>
            @if($tagline)
                <p class="text-base md:text-lg text-white/70 max-w-md">
                    {{ $tagline }}
                </p>
            @endif
        </div>

        {{-- Illustration --}}
        <div class="relative z-10 flex-1 flex items-center justify-center py-8">
            @if($landingImageUrl)
                <img src="{{ $landingImageUrl }}" alt="Illustration" class="max-w-full max-h-80 object-contain">
            @else
                {{-- Default SVG Illustration --}}
                <div class="text-center">
                    <div class="w-72 h-72 mx-auto flex items-center justify-center">
                        <svg viewBox="0 0 400 300" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                            {{-- Laptop --}}
                            <rect x="80" y="120" width="240" height="160" rx="12" fill="white" fill-opacity="0.15" stroke="white" stroke-opacity="0.3" stroke-width="2"/>
                            <rect x="100" y="135" width="200" height="120" rx="4" fill="white" fill-opacity="0.1"/>
                            {{-- Screen content --}}
                            <line x1="120" y1="155" x2="280" y2="155" stroke="white" stroke-opacity="0.3" stroke-width="2"/>
                            <line x1="120" y1="170" x2="250" y2="170" stroke="white" stroke-opacity="0.2" stroke-width="2"/>
                            <line x1="120" y1="185" x2="220" y2="185" stroke="white" stroke-opacity="0.2" stroke-width="2"/>
                            <rect x="130" y="200" width="60" height="20" rx="4" fill="white" fill-opacity="0.2"/>
                            <rect x="200" y="200" width="60" height="20" rx="4" fill="white" fill-opacity="0.15"/>
                            {{-- Keyboard base --}}
                            <path d="M60 280 L160 300 L240 300 L340 280" stroke="white" stroke-opacity="0.3" stroke-width="2" fill="none"/>
                            <rect x="160" y="280" width="80" height="8" rx="4" fill="white" fill-opacity="0.15"/>
                            {{-- Person left --}}
                            <circle cx="140" cy="100" r="18" fill="white" fill-opacity="0.15" stroke="white" stroke-opacity="0.3" stroke-width="2"/>
                            <path d="M115 145 Q140 120 165 145" stroke="white" stroke-opacity="0.3" stroke-width="2" fill="white" fill-opacity="0.08"/>
                            {{-- Person right --}}
                            <circle cx="260" cy="95" r="18" fill="white" fill-opacity="0.15" stroke="white" stroke-opacity="0.3" stroke-width="2"/>
                            <path d="M235 140 Q260 115 285 140" stroke="white" stroke-opacity="0.3" stroke-width="2" fill="white" fill-opacity="0.08"/>
                            {{-- Person center (taller) --}}
                            <circle cx="200" cy="85" r="20" fill="white" fill-opacity="0.2" stroke="white" stroke-opacity="0.4" stroke-width="2"/>
                            <path d="M170 135 Q200 105 230 135" stroke="white" stroke-opacity="0.4" stroke-width="2" fill="white" fill-opacity="0.1"/>
                        </svg>
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div class="relative z-10 text-xs text-white/50">
            {{ $footerText }}
        </div>
    </div>

    {{-- RIGHT SIDE — Login Form --}}
    <div class="w-full md:w-1/2 min-h-screen flex items-center justify-center p-6 md:p-12 bg-white">
        <div class="w-full max-w-md">
            {{-- Mobile brand (only visible on small screens) --}}
            <div class="md:hidden text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-brand-gradient flex items-center justify-center text-white font-bold text-xl mx-auto mb-3 shadow-lg">
                    {{ strtoupper(substr($schoolName, 0, 2)) }}
                </div>
                <h2 class="text-lg font-bold text-slate-800">{{ $portalTitle }}</h2>
            </div>

            <div class="mb-6">
                <h2 class="text-2xl font-bold text-slate-800">{{ $formTitle }}</h2>
                <p class="text-sm text-slate-400 mt-1">{{ $formSubtitle }}</p>
            </div>

            {{ $slot }}

            @if(isset($footerLink))
                <p class="text-center text-xs text-slate-400 mt-6">
                    {!! $footerLink !!}
                </p>
            @endif
        </div>
    </div>

</body>
</html>
