@extends('layouts.backend')

@section('title', 'Backup & Restore')
@section('page_title', 'Backup & Restore Database')

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-accent-50 border border-accent-100 text-accent text-sm flex items-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm flex items-center gap-2">
    <i data-lucide="alert-triangle" class="w-4 h-4"></i> {{ session('error') }}
</div>
@endif
@if($errors->any())
<div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm">
    <div class="flex items-center gap-2 mb-1"><i data-lucide="alert-triangle" class="w-4 h-4"></i> <span class="font-medium">Gagal:</span></div>
    <ul class="list-disc list-inside text-xs space-y-0.5">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

{{-- Info Database --}}
<div class="bg-white rounded-2xl border border-slate-100 p-5 mb-5">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
            <i data-lucide="database" class="w-5 h-5 text-blue-600"></i>
        </div>
        <div>
            <h3 class="font-semibold text-slate-800 text-sm">Informasi Database</h3>
            <p class="text-xs text-slate-500">Driver: <span class="font-mono font-medium">{{ $dbDriver }}</span></p>
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        <form action="{{ route('system.backup.create') }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl btn-accent text-white text-sm font-medium hover:brightness-110 transition">
                <i data-lucide="hard-drive-download" class="w-4 h-4"></i> Buat Backup Sekarang
            </button>
        </form>
    </div>
</div>

{{-- Restore dari Upload (selalu muncul) --}}
<div class="bg-white rounded-2xl border border-slate-100 p-5 mb-5">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
            <i data-lucide="upload" class="w-5 h-5 text-amber-600"></i>
        </div>
        <div>
            <h3 class="font-semibold text-slate-800 text-sm">Restore dari File Backup</h3>
            <p class="text-xs text-slate-500">Upload file .sql atau .sqlite untuk merestore database.</p>
        </div>
    </div>

    <form action="{{ route('system.backup.restore-upload') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
        @csrf
        <div>
            <input type="file" name="backup_file" accept=".sql,.sqlite,.gz"
                class="w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
        </div>
        <div>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 text-white text-sm font-medium hover:brightness-110 transition">
                <i data-lucide="upload" class="w-4 h-4 inline"></i> Restore Sekarang
            </button>
        </div>
    </form>
</div>

{{-- Auto-Backup Settings --}}
<div class="bg-white rounded-2xl border border-slate-100 p-5 mb-5">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-accent-50 flex items-center justify-center">
            <i data-lucide="clock" class="w-5 h-5 text-accent"></i>
        </div>
        <div>
            <h3 class="font-semibold text-slate-800 text-sm">Auto-Backup Terjadwal</h3>
            <p class="text-xs text-slate-500">Backup otomatis harian, mingguan, dan bulanan via cron.</p>
        </div>
    </div>

    <form action="{{ route('system.backup.settings') }}" method="POST" class="space-y-4">
        @csrf
        <div class="flex flex-wrap items-center gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="backup_auto_enabled" value="0">
                <input type="checkbox" name="backup_auto_enabled" value="1"
                    class="w-5 h-5 rounded border-slate-300 focus:ring-accent-200 peer"
                    {{ $autoBackupEnabled ? 'checked' : '' }}
                    onchange="this.previousElementSibling.value = this.checked ? '1' : '0'">
                <span class="text-sm font-medium text-slate-700">Aktifkan Auto-Backup</span>
            </label>

            <div class="flex items-center gap-2">
                <label class="text-sm text-slate-600">Retensi:</label>
                <input type="number" name="backup_retention_days" value="{{ $retentionDays }}"
                    min="1" max="365"
                    class="w-20 px-3 py-2 rounded-lg border border-slate-200 text-sm text-center focus:outline-none focus:ring-accent-200">
                <span class="text-sm text-slate-500">hari</span>
            </div>
        </div>

        <div class="bg-slate-50 rounded-xl p-4 text-xs text-slate-600 space-y-1.5">
            <p class="font-medium text-slate-700">Jadwal Auto-Backup (setelah diaktifkan):</p>
            <div class="flex items-center gap-2"><span class="w-16 font-medium text-slate-500">Harian</span> <span class="font-mono">Jam 02:00</span> <span class="text-slate-400">— retensi {{ $retentionDays }} hari</span></div>
            <div class="flex items-center gap-2"><span class="w-16 font-medium text-slate-500">Mingguan</span> <span class="font-mono">Senin 03:00</span> <span class="text-slate-400">— retensi {{ $retentionDays * 4 }} hari</span></div>
            <div class="flex items-center gap-2"><span class="w-16 font-medium text-slate-500">Bulanan</span> <span class="font-mono">Tgl 1, 04:00</span> <span class="text-slate-400">— retensi {{ $retentionDays * 12 }} hari</span></div>
            <p class="mt-2 text-slate-400">Backup otomatis disimpan dengan label <code class="bg-slate-200 px-1 rounded">daily</code>, <code class="bg-slate-200 px-1 rounded">weekly</code>, <code class="bg-slate-200 px-1 rounded">monthly</code>. Backup manual tidak terpengaruh retensi.</p>
            <p class="text-slate-400">Untuk mengaktifkan cron: <code class="bg-slate-200 px-1 rounded">php artisan schedule:run</code> atau tambahkan ke crontab server.</p>
        </div>

        <div>
            <button type="submit" class="px-5 py-2.5 rounded-xl btn-accent text-white text-sm font-medium hover:brightness-110 transition">
                Simpan Pengaturan Auto-Backup
            </button>
        </div>
    </form>
</div>

{{-- Daftar Backup --}}
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="p-5 border-b border-slate-100">
        <h3 class="font-semibold text-slate-800 text-sm">Daftar File Backup</h3>
    </div>

    @if(empty($backups))
    <div class="p-12 text-center text-slate-400">
        <i data-lucide="archive" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
        <p class="text-sm">Belum ada file backup.</p>
        <p class="text-xs mt-1">Klik "Buat Backup Sekarang" untuk membuat backup database.</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-left">
                    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Nama File</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Ukuran</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Tanggal</th>
                    <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase w-40">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($backups as $backup)
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <i data-lucide="{{ $backup['ext'] === 'sql' ? 'file-code' : 'file' }}" class="w-4 h-4 text-slate-400"></i>
                            <span class="font-mono text-sm text-slate-700">{{ $backup['filename'] }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-slate-600">{{ $backup['size'] }}</td>
                    <td class="px-5 py-3.5 text-sm text-slate-500">{{ $backup['date'] }}</td>
                    <td class="px-5 py-3.5">
                        <div class="flex gap-1">
                            <a href="{{ route('system.backup.download', $backup['filename']) }}"
                                class="p-1.5 rounded-lg hover:bg-blue-50 text-slate-400 hover:text-blue-600"
                                title="Download">
                                <i data-lucide="download" class="w-4 h-4"></i>
                            </a>
                            <button
                                onclick="confirmRestore('{{ $backup['filename'] }}')"
                                class="p-1.5 rounded-lg hover:bg-amber-50 text-slate-400 hover:text-amber-600"
                                title="Restore">
                                <i data-lucide="upload" class="w-4 h-4"></i>
                            </button>
                            <button
                                onclick="confirmDelete('{{ $backup['filename'] }}')"
                                class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600"
                                title="Hapus">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- Form Restore (hidden) --}}
<form id="restoreForm" method="POST" action="{{ route('system.backup.restore') }}" class="hidden">
    @csrf
    <input type="hidden" name="backup_file" id="restoreFilename">
</form>

{{-- Form Delete (hidden) --}}
<form id="deleteForm" method="POST" action="{{ route('system.backup.delete') }}" class="hidden">
    @csrf
    <input type="hidden" name="filename" id="deleteFilename">
</form>
@endsection

@push('scripts')
<script>
function confirmRestore(filename) {
    showConfirm(
        'Restore database dari file ' + filename + '? Semua data saat ini akan ditimpa. Tindakan ini tidak bisa dibatalkan.',
        'Restore Database',
        'Ya, Restore',
        function() {
            document.getElementById('restoreFilename').value = filename;
            document.getElementById('restoreForm').submit();
        }
    );
}

function confirmDelete(filename) {
    showConfirm(
        'Hapus file backup ' + filename + '?',
        'Hapus Backup',
        'Ya, Hapus',
        function() {
            document.getElementById('deleteFilename').value = filename;
            document.getElementById('deleteForm').submit();
        }
    );
}
</script>
@endpush
