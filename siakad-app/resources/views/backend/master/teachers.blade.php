@extends('layouts.backend')

@section('title', 'Data Guru')
@section('page_title', 'Data Guru')

@push('styles')
<style>
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 50; display: none; align-items: center; justify-content: center; padding: 20px; }
.modal-overlay.show { display: flex; }
.modal-box { background: white; border-radius: 16px; width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-accent-50 border border-accent-100 text-accent text-sm flex items-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm flex items-center gap-2">
    <i data-lucide="alert-circle" class="w-4 h-4"></i> {{ session('error') }}
</div>
@endif
@if($errors->any())
<div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm">
    <div class="flex items-center gap-2 mb-1"><i data-lucide="alert-triangle" class="w-4 h-4"></i> <span class="font-medium">Gagal menyimpan:</span></div>
    <ul class="list-disc list-inside text-xs space-y-0.5">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <form method="GET" class="flex gap-2 filter-form">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari guru..." class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200 w-56">
        <select name="role" class="px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
            <option value="">Semua Role</option>
            <option value="guru" {{ request('role')=='guru'?'selected':'' }}>Guru</option>
            <option value="walikelas" {{ request('role')=='walikelas'?'selected':'' }}>Wali Kelas</option>
            <option value="kepsek" {{ request('role')=='kepsek'?'selected':'' }}>Kepala Sekolah</option>
            <option value="bendahara" {{ request('role')=='bendahara'?'selected':'' }}>Bendahara</option>
            <option value="tata_usaha" {{ request('role')=='tata_usaha'?'selected':'' }}>Tata Usaha</option>
            <option value="bk" {{ request('role')=='bk'?'selected':'' }}>BK</option>
            <option value="perpustakaan" {{ request('role')=='perpustakaan'?'selected':'' }}>Perpustakaan</option>
        </select>
        <button type="submit" class="px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium hover:brightness-110"><i data-lucide="search" class="w-4 h-4 inline"></i></button>
    </form>
</div>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden table-responsive">
    <table class="w-full text-sm">
        <thead><tr class="bg-slate-50 text-left">
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">NIP</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Nama</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Email</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Role</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Wali Kelas</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase w-20">Aksi</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-50">
        @forelse($teachers as $teacher)
        <tr class="hover:bg-slate-50/50 transition">
            <td class="px-5 py-3.5 font-mono text-sm">{{ $teacher->nip ?? '-' }}</td>
            <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-accent-100 flex items-center justify-center text-accent font-bold text-xs">{{ strtoupper(substr($teacher->name,0,1)) }}</div>
                    <span class="font-medium text-slate-800">{{ $teacher->name }}</span>
                </div>
            </td>
            <td class="px-5 py-3.5 text-slate-500">{{ $teacher->email }}</td>
            <td class="px-5 py-3.5">
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-accent-50 text-accent">
                    {{ str_replace('_',' ',$teacher->role) }}
                </span>
            </td>
            <td class="px-5 py-3.5">
                @if($teacher->homeroomClass)
                <span class="px-2 py-1 rounded-full text-xs font-medium bg-accent-50 text-accent">{{ $teacher->homeroomClass->code }}</span>
                @else <span class="text-slate-300">-</span> @endif
            </td>
            <td class="px-5 py-3.5">
                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $teacher->is_active?'bg-emerald-50 text-emerald-600':'bg-red-50 text-red-600' }}">{{ $teacher->is_active?'Aktif':'Nonaktif' }}</span>
            </td>
            <td class="px-5 py-3.5">
                <div class="flex gap-1">
                    <button data-edit-id="{{ $teacher->id }}" class="p-1.5 rounded-lg hover:bg-accent-50 text-slate-400 hover:text-accent js-edit-btn" title="Edit">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                    </button>
                    <form method="POST" action="{{ route('master.teachers.delete', $teacher->id) }}" onsubmit="event.preventDefault(); showConfirm('Hapus data guru ini?', 'Hapus Guru', 'Ya, Hapus', () => this.submit());" class="inline">
                        @csrf
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="px-5 py-12 text-center text-slate-400">Belum ada data guru.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <span>Tampilkan</span>
        <select onchange="changePerPage(this.value)"
            class="form-select text-sm w-20 py-1.5 rounded-lg border-slate-200">
            <option value="10"  {{ $perPage == 10 ? 'selected' : '' }}>10</option>
            <option value="20"  {{ $perPage == 20 ? 'selected' : '' }}>20</option>
            <option value="50"  {{ $perPage == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
        </select>
        <span>data</span>
    </div>
    {{ $teachers->links() }}
</div>

{{-- EDIT MODAL --}}
<div class="modal-overlay" id="teacherModal">
    <div class="modal-box">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800" id="modalTitle">Edit Guru</h3>
            <button onclick="closeModal()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="teacherForm" method="POST" action="" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="_method" value="PUT">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" id="tName" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Email</label>
                <input type="email" name="email" id="tEmail" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Password <span class="text-slate-400 font-normal">(kosongkan jika tidak diubah)</span></label>
                    <input type="password" name="password" id="tPass" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">NIP</label>
                    <input type="text" name="nip" id="tNip" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">No. Telepon</label>
                    <input type="text" name="phone" id="tPhone" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Role</label>
                    <select name="role" id="tRole" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                        <option value="guru">Guru</option>
                        <option value="walikelas">Wali Kelas</option>
                        <option value="kepsek">Kepala Sekolah</option>
                        <option value="bendahara">Bendahara</option>
                        <option value="tata_usaha">Tata Usaha</option>
                        <option value="bk">BK</option>
                        <option value="perpustakaan">Perpustakaan</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Mata Pelajaran <span class="text-slate-400 font-normal">(bisa pilih lebih dari satu)</span></label>
                <div class="max-h-40 overflow-y-auto border border-slate-200 rounded-xl p-2 space-y-1" id="tSubjectList">
                    @foreach(\App\Models\Subject::where('school_id', auth()->user()->school_id)->where('is_active', true)->orderBy('code')->get() as $subj)
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer text-sm">
                        <input type="checkbox" name="subject_ids[]" value="{{ $subj->id }}" class="rounded accent-accent t-subj-cb">
                        <span>{{ $subj->code }} — {{ $subj->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Kelas Mengajar <span class="text-slate-400 font-normal">(pilih kelas yang diajar)</span></label>
                <div class="max-h-32 overflow-y-auto border border-slate-200 rounded-xl p-2 space-y-1" id="tClassList">
                    @foreach($classes as $cls)
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer text-sm">
                        <input type="checkbox" name="class_ids[]" value="{{ $cls->id }}" class="rounded accent-accent t-cls-cb">
                        <span>{{ $cls->code }} — {{ $cls->name }} ({{ $cls->tingkat }})</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium hover:brightness-110">Simpan</button>
                <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 hover:bg-slate-50">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEditModal(id) {
    var modal = document.getElementById('teacherModal');
    var content = document.getElementById('tSubjectList');
    document.getElementById('teacherForm').action = '{{ url("/backend/master/teachers") }}/' + id;
    document.getElementById('modalTitle').textContent = 'Edit Guru';
    // Reset all checkboxes
    document.querySelectorAll('.t-subj-cb, .t-cls-cb').forEach(function(cb) { cb.checked = false; });
    modal.classList.add('show');
    // Fetch JSON
    fetch('{{ url("/backend/master/teachers") }}/' + id, {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (!res.success) return;
        var d = res.data;
        document.getElementById('tName').value = d.name || '';
        document.getElementById('tEmail').value = d.email || '';
        document.getElementById('tPass').value = '';
        document.getElementById('tNip').value = d.nip || '';
        document.getElementById('tPhone').value = d.phone || '';
        document.getElementById('tRole').value = d.role || 'guru';
        // Check subject checkboxes
        if (d.subject_ids) {
            d.subject_ids.forEach(function(sid) {
                document.querySelectorAll('.t-subj-cb').forEach(function(cb) {
                    if (cb.value == sid) cb.checked = true;
                });
            });
        }
        // Check class checkboxes
        if (d.class_ids) {
            d.class_ids.forEach(function(cid) {
                document.querySelectorAll('.t-cls-cb').forEach(function(cb) {
                    if (cb.value == cid) cb.checked = true;
                });
            });
        }
    })
    .catch(function() {
        document.getElementById('teacherModal').classList.remove('show');
        alert('Gagal memuat data guru.');
    });
}
function closeModal(){ document.getElementById('teacherModal').classList.remove('show'); }

document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('table').addEventListener('click', function(e) {
        var btn = e.target.closest('.js-edit-btn');
        if (btn) { openEditModal(btn.dataset.editId); }
    });
    var modal = document.getElementById('teacherModal');
    if (modal) modal.addEventListener('click', function(e) { if (e.target === this) closeModal(); });
    try { if (typeof lucide !== 'undefined') lucide.createIcons(); } catch(e) {}
});
</script>
@endpush
