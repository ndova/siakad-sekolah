@extends('layouts.backend')

@section('title', 'Sinkronisasi Fingerprint')
@section('page_title', 'Sinkronisasi Fingerprint')

@section('content')
<div class="max-w-6xl mx-auto">

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-600 text-sm font-medium flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
    </div>
    @endif

    {{-- TAB NAVIGATION --}}
    <div class="flex gap-1 mb-6 bg-white rounded-xl p-1 border border-slate-200 shadow-sm" x-data="{ tab: 'devices' }">
        <button type="button" @click="tab = 'devices'"
            :class="tab === 'devices' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
            class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200 text-center">
            📟 Perangkat
        </button>
        <button type="button" @click="tab = 'upload'"
            :class="tab === 'upload' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
            class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200 text-center">
            📤 Upload Log
        </button>
        <button type="button" @click="tab = 'mappings'"
            :class="tab === 'mappings' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
            class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200 text-center">
            🔗 PIN Mapping
        </button>
        <button type="button" @click="tab = 'logs'"
            :class="tab === 'logs' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
            class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200 text-center">
            📋 Log Terbaru
        </button>
    </div>

    {{-- TAB 1: PERANGKAT --}}
    <div x-show="tab === 'devices'" x-transition>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Daftar Perangkat</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                @forelse($devices as $device)
                <div class="border rounded-xl p-4 {{ $device->is_active ? 'border-slate-200' : 'border-slate-100 bg-slate-50' }}">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h4 class="font-semibold text-slate-800">{{ $device->name }}</h4>
                            <p class="text-xs text-slate-400">SN: {{ $device->serial_number }}</p>
                        </div>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $device->status === 'online' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">
                            {{ $device->status }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-400 space-y-1">
                        @if($device->ip_address)
                        <p>IP: {{ $device->ip_address }}:{{ $device->port }}</p>
                        @endif
                        @if($device->model)
                        <p>Model: {{ $device->model }}</p>
                        @endif
                        @if($device->last_sync_at)
                        <p>Sinkron terakhir: {{ $device->last_sync_at->diffForHumans() }}</p>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400 col-span-2 py-4">Belum ada perangkat terdaftar.</p>
                @endforelse
            </div>

            <h4 class="text-sm font-semibold text-slate-600 mb-3">➕ Tambah Perangkat</h4>
            <form action="{{ route('fingerprint.device.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @csrf
                <input type="text" name="name" placeholder="Nama perangkat*" required
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                <input type="text" name="serial_number" placeholder="Serial Number*" required
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                <input type="text" name="ip_address" placeholder="IP Address"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                <input type="text" name="model" placeholder="Model (opsional)"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                <input type="text" name="location" placeholder="Lokasi"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                <button type="submit"
                    class="px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700">
                    Daftarkan
                </button>
            </form>
        </div>
    </div>

    {{-- TAB 2: UPLOAD LOG --}}
    <div x-show="tab === 'upload'" x-transition>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Upload File Log Fingerprint</h3>

            <form action="{{ route('fingerprint.log.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Perangkat</label>
                    <select name="device_id" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                        @foreach($devices as $d)
                        <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->serial_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">File Log (CSV/TXT)</label>
                    <input type="file" name="file" accept=".csv,.txt,.log" required
                        class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700">
                    <p class="text-xs text-slate-400 mt-1">Format: PIN, Tanggal, Jam, VerifyMode, IOMode (CSV)</p>
                </div>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700">
                    Upload & Simpan Log
                </button>
            </form>

            {{-- Proses Log --}}
            <div class="mt-6 border-t pt-6">
                <h4 class="text-sm font-semibold text-slate-600 mb-3">⚙️ Proses Log → Absensi</h4>
                <form action="{{ route('fingerprint.log.process') }}" method="POST" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">Tanggal</label>
                        <input type="date" name="tanggal" value="{{ now()->format('Y-m-d') }}" required
                            class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                    </div>
                    <button type="submit"
                        class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-medium hover:bg-emerald-700">
                        Proses Jadi Absensi
                    </button>
                </form>
                <p class="text-xs text-slate-400 mt-2">Log fingerprint yang belum diproses akan dikonversi menjadi data absensi siswa & pegawai.</p>
            </div>
        </div>
    </div>

    {{-- TAB 3: PIN MAPPING --}}
    <div x-show="tab === 'mappings'" x-transition>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Daftar PIN → Siswa/Pegawai</h3>

            <form action="{{ route('fingerprint.pin.store') }}" method="POST" class="flex flex-wrap items-end gap-3 mb-5 p-4 bg-slate-50 rounded-xl">
                @csrf
                <div>
                    <label class="block text-xs text-slate-500 mb-1">PIN (dari mesin)</label>
                    <input type="text" name="pin" placeholder="PIN" required
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm w-32">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Tipe</label>
                    <select name="entity_type" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm">
                        <option value="staff">Pegawai</option>
                        <option value="student">Siswa</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">ID Pegawai/Siswa</label>
                    <input type="text" name="entity_id" placeholder="UUID" required
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm w-80">
                </div>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700">
                    Tambah Mapping
                </button>
            </form>

            @if($mappings->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b">
                            <th class="text-left py-2 px-4 text-xs font-semibold text-slate-500 uppercase">PIN</th>
                            <th class="text-left py-2 px-4 text-xs font-semibold text-slate-500 uppercase">Tipe</th>
                            <th class="text-left py-2 px-4 text-xs font-semibold text-slate-500 uppercase">ID Entity</th>
                            <th class="text-center py-2 px-4 text-xs font-semibold text-slate-500 uppercase w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($mappings as $m)
                        <tr>
                            <td class="py-2.5 px-4 font-mono text-sm font-bold text-slate-700">{{ $m->pin }}</td>
                            <td class="py-2.5 px-4">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                    {{ $m->entity_type === 'student' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600' }}">
                                    {{ $m->entity_type }}
                                </span>
                            </td>
                            <td class="py-2.5 px-4 text-xs text-slate-500 font-mono">{{ $m->entity_id }}</td>
                            <td class="py-2.5 px-4 text-center">
                                <form action="{{ route('fingerprint.pin.delete', $m->id) }}" method="POST" onsubmit="return confirm('Hapus mapping ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-400 hover:text-red-600 text-xs">🗑️</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-slate-400 py-4">Belum ada mapping PIN.</p>
            @endif
        </div>
    </div>

    {{-- TAB 4: LOG TERBARU --}}
    <div x-show="tab === 'logs'" x-transition>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b bg-slate-50">
                <h3 class="font-semibold text-slate-800">50 Log Fingerprint Terbaru</h3>
            </div>
            @if($recentLogs->isEmpty())
            <p class="text-sm text-slate-400 text-center py-12">Belum ada log.</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b">
                            <th class="text-left py-2 px-4 text-xs font-semibold text-slate-500 uppercase">PIN</th>
                            <th class="text-left py-2 px-4 text-xs font-semibold text-slate-500 uppercase">Waktu</th>
                            <th class="text-center py-2 px-4 text-xs font-semibold text-slate-500 uppercase">Mode</th>
                            <th class="text-center py-2 px-4 text-xs font-semibold text-slate-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($recentLogs as $log)
                        <tr class="{{ $log->is_processed ? '' : 'bg-amber-50/30' }}">
                            <td class="py-2 px-4 font-mono font-medium text-slate-700">{{ $log->pin }}</td>
                            <td class="py-2 px-4 text-xs text-slate-500">{{ $log->scan_time?->format('d/m/Y H:i:s') }}</td>
                            <td class="py-2 px-4 text-center">
                                <span class="text-xs">{{ $log->verify_mode }}/{{ $log->io_mode }}</span>
                            </td>
                            <td class="py-2 px-4 text-center">
                                @if($log->is_processed)
                                <span class="text-emerald-500 text-xs">✓ Diproses</span>
                                @else
                                <span class="text-amber-500 text-xs">⚠ Belum</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

</div>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>lucide.createIcons();</script>
@endsection
