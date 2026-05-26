<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pilih Kurikulum — SIAKAD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f1f5f9; }
        .card-kurmer { border-color: #059669; }
        .card-kurmer:hover { border-color: #059669; box-shadow: 0 4px 24px rgba(5, 150, 105, 0.12); }
        .card-k13 { border-color: #2563eb; }
        .card-k13:hover { border-color: #2563eb; box-shadow: 0 4px 24px rgba(37, 99, 235, 0.12); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-2xl">
        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center text-white font-bold text-xl mx-auto mb-4 shadow-lg">
                SI
            </div>
            <h1 class="text-xl font-bold text-slate-800">Selamat Datang, {{ auth()->user()->name }}</h1>
            <p class="text-sm text-slate-400 mt-1">
                Silakan pilih jenis kurikulum yang akan digunakan.
            </p>
            <p class="text-xs text-slate-300 mt-1">
                Pilihan ini dapat diganti sewaktu-waktu melalui menu Pengaturan.
            </p>
        </div>

        {{-- Cards — hanya tampilkan kurikulum yang diaktifkan admin --}}
        @php $available = \App\Enums\CurriculumType::available(); @endphp
        <div class="grid grid-cols-1 {{ count($available) > 1 ? 'md:grid-cols-2' : '' }} gap-4 mb-6">
            @foreach($available as $cur)
            <form action="{{ route('curriculum.select') }}" method="POST" class="contents">
                @csrf
                <input type="hidden" name="curriculum_type" value="{{ $cur->value }}">
                <button type="submit"
                    class="bg-white rounded-2xl border-2 border-slate-200 p-6 text-left transition-all duration-200 cursor-pointer group hover:shadow-lg"
                    style="border-color: {{ $cur->color() }}; --hover-shadow: {{ $cur->color() }}15;"
                    onmouseover="this.style.borderColor='{{ $cur->color() }}'; this.style.boxShadow='0 4px 24px {{ $cur->color() }}20';"
                    onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='';">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition"
                        style="background: {{ $cur->color() }}15;">
                        <i data-lucide="{{ $cur->icon() }}" class="w-6 h-6" style="color: {{ $cur->color() }};"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800 mb-2">{{ $cur->label() }}</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">{{ $cur->description() }}</p>
                    <div class="mt-4 flex items-center gap-2 text-xs font-semibold" style="color: {{ $cur->color() }};">
                        <span>Gunakan {{ $cur->label() }}</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover:translate-x-1 transition-transform"></i>
                    </div>
                </button>
            </form>
            @endforeach
        </div>

        {{-- Logout --}}
        <div class="text-center">
            <form method="POST" action="{{ url('/backend/logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-xs text-slate-400 hover:text-red-500 transition">
                    ← Keluar / Ganti Akun
                </button>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
