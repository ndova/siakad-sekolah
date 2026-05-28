@extends('layouts.backend')

@section('title', 'Data Siswa')
@section('page_title', 'Data Siswa')

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
@if($errors->any())
<div class="mb-4 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm">
    <div class="flex items-center gap-2 mb-1"><i data-lucide="alert-triangle" class="w-4 h-4"></i> <span class="font-medium">Gagal menyimpan:</span></div>
    <ul class="list-disc list-inside text-xs space-y-0.5">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <form method="GET" class="flex gap-2 flex-wrap filter-form">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIS/Nama..." class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200 w-52">
        <select name="class_id" class="px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
            <option value="">Semua Kelas</option>
            @foreach($classes as $kelas)
            <option value="{{ $kelas->id }}" {{ request('class_id')==$kelas->id?'selected':'' }}>{{ $kelas->code }} - {{ $kelas->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium hover:brightness-110"><i data-lucide="search" class="w-4 h-4 inline"></i></button>
    </form>
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium hover:brightness-110 transition">
        <i data-lucide="plus" class="w-4 h-4"></i> Tambah Siswa
    </button>
</div>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden table-responsive">
    <table class="w-full text-sm">
        <thead><tr class="bg-slate-50 text-left">
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">NIS/NISN</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Nama</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Kelas</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Orang Tua/Wali</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase w-20">Aksi</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-50">
        @forelse($students as $student)
        @php
            $primaryWali = $student->parents->firstWhere('pivot.is_primary', true);
            $namaWali = $primaryWali?->nama_lengkap ?? $student->nama_ayah ?? $student->nama_ibu ?? '-';
        @endphp
        <tr class="hover:bg-slate-50/50 transition">
            <td class="px-5 py-3.5"><span class="font-mono text-sm">{{ $student->nis ?? '-' }}</span><br><span class="text-xs text-slate-400">{{ $student->nisn ?? '' }}</span></td>
            <td class="px-5 py-3.5"><span class="font-medium text-slate-800">{{ $student->nama_lengkap }}</span></td>
            <td class="px-5 py-3.5"><span class="px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100">{{ $student->class?->code ?? '-' }}</span></td>
            <td class="px-5 py-3.5"><span class="text-sm text-slate-700">{{ $namaWali }}</span></td>
            <td class="px-5 py-3.5"><span class="px-2 py-1 rounded-full text-xs font-medium {{ $student->status=='aktif' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500' }}">{{ $student->status }}</span></td>
            <td class="px-5 py-3.5">
                <div class="flex gap-1">
                    <button data-view-id="{{ $student->id }}" class="p-1.5 rounded-lg hover:bg-blue-50 text-slate-400 hover:text-blue-600 js-view-btn" title="Lihat Detail"><i data-lucide="eye" class="w-4 h-4"></i></button>
                    <button data-edit-id="{{ $student->id }}" data-edit-nis="{{ $student->nis }}" data-edit-nisn="{{ $student->nisn }}" data-edit-nama="{{ $student->nama_lengkap }}" data-edit-class="{{ $student->class_id }}" data-edit-status="{{ $student->status }}" class="p-1.5 rounded-lg hover:bg-accent-50 text-slate-400 hover:text-accent js-edit-btn" title="Edit"><i data-lucide="pencil" class="w-4 h-4"></i></button>
                    <form method="POST" action="{{ route('master.students.delete', $student->id) }}" onsubmit="event.preventDefault(); showConfirm('Hapus siswa ini?', 'Hapus Siswa', 'Ya, Hapus', () => this.submit());" class="inline">
                        @csrf<button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400">Belum ada data siswa.</td></tr>
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
    {{ $students->links() }}
</div>

{{-- MODAL --}}
<div class="modal-overlay" id="studentModal">
    <div class="modal-box">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800" id="modalTitle">Tambah Siswa</h3>
            <button onclick="closeModal()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="studentForm" method="POST" action="{{ route('master.students.store') }}" class="p-5 space-y-4">
            @csrf<input type="hidden" name="_method" id="sFormMethod" value="POST">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">NIS</label>
                    <input type="text" name="nis" id="sNis" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">NISN</label>
                    <input type="text" name="nisn" id="sNisn" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="sNama" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Kelas</label>
                    <select name="class_id" id="sClass" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                        <option value="">Pilih Kelas</option>
                        @foreach($classes as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->code }} - {{ $kelas->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Status</label>
                    <select name="status" id="sStatus" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                        <option value="aktif">Aktif</option>
                        <option value="lulus">Lulus</option>
                        <option value="keluar">Keluar</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium hover:brightness-110">Simpan</button>
                <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 hover:bg-slate-50">Batal</button>
            </div>
        </form>
    </div>
</div>

{{-- DETAIL MODAL --}}
<div class="modal-overlay" id="detailModal">
    <div class="modal-box" style="max-width:640px;">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">Detail Siswa</h3>
            <button onclick="closeDetailModal()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <div class="p-5" id="detailContent">
            <div class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-accent"></div>
                <span class="ml-3 text-sm text-slate-500">Memuat data...</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openModal() {
    document.getElementById('modalTitle').textContent='Tambah Siswa';
    document.getElementById('studentForm').action='{{ route("master.students.store") }}';
    document.getElementById('sFormMethod').value='POST';
    document.getElementById('sNis').value=''; document.getElementById('sNisn').value='';
    document.getElementById('sNama').value=''; document.getElementById('sClass').value='';
    document.getElementById('sStatus').value='aktif';
    document.getElementById('studentModal').classList.add('show');
}
function editStudent(id,nis,nisn,nama,classId,status){
    document.getElementById('modalTitle').textContent='Edit Siswa';
    document.getElementById('studentForm').action='{{ url("/backend/master/students") }}/'+id;
    document.getElementById('sFormMethod').value='PUT';
    document.getElementById('sNis').value=nis; document.getElementById('sNisn').value=nisn;
    document.getElementById('sNama').value=nama; document.getElementById('sClass').value=classId;
    document.getElementById('sStatus').value=status;
    document.getElementById('studentModal').classList.add('show');
}
function closeModal(){ document.getElementById('studentModal').classList.remove('show'); }
// Detail modal
function openDetailModal(studentId) {
    var modal = document.getElementById('detailModal');
    var content = document.getElementById('detailContent');
    modal.classList.add('show');
    content.innerHTML = '<div class="flex items-center justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-accent"></div><span class="ml-3 text-sm text-slate-500">Memuat data...</span></div>';
    fetch('{{ url("/backend/master/students") }}/' + studentId, {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (!res.success) { content.innerHTML = '<p class="text-red-500 text-center py-4">Gagal memuat data.</p>'; return; }
        var d = res.data;
        var jkLabel = d.jk === 'L' ? 'Laki-laki' : (d.jk === 'P' ? 'Perempuan' : d.jk);
        var statusClass = d.status === 'aktif' ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-500';
        var photoHtml = d.photo ? '<img src="'+d.photo+'" alt="Foto" class="w-20 h-20 rounded-full object-cover border-2 border-slate-200">' : '<div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center text-slate-400"><i data-lucide="user" class="w-10 h-10"></i></div>';
        // Bangun list wali
        var waliHtml = '';
        if (d.wali_list && d.wali_list.length > 0) {
            waliHtml = '<div class="col-span-2 pt-2 border-t border-slate-100 mt-1"><span class="text-xs text-slate-400 block uppercase tracking-wide font-semibold mb-2">Orang Tua / Wali</span></div>';
            d.wali_list.forEach(function(w) {
                waliHtml += '<div class="col-span-2 flex items-center justify-between py-1.5 px-3 bg-slate-50 rounded-lg"><div><span class="font-medium text-slate-700 text-sm">'+w.nama+'</span><span class="ml-2 text-xs text-slate-400">('+w.hubungan+')</span>'+ (w.is_primary ? ' <span class="ml-1 px-1.5 py-0.5 rounded text-xs bg-blue-50 text-blue-600">Utama</span>' : '') +'</div><span class="text-xs text-slate-500">'+(w.phone||'-')+'</span></div>';
            });
        } else if (d.nama_wali && d.nama_wali !== '-') {
            waliHtml = '<div class="col-span-2 pt-2 border-t border-slate-100 mt-1"><span class="text-xs text-slate-400 block uppercase tracking-wide font-semibold mb-2">Orang Tua / Wali</span></div>' +
                '<div class="col-span-2"><span class="font-medium text-slate-700 text-sm">'+d.nama_wali+'</span></div>';
        }
        content.innerHTML =
            '<div class="flex items-start gap-4 mb-5">' +
                photoHtml +
                '<div>' +
                    '<h4 class="text-lg font-semibold text-slate-800">'+d.nama_lengkap+'</h4>' +
                    '<span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-medium '+statusClass+'">'+d.status+'</span>' +
                '</div>' +
            '</div>' +
            '<div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">' +
                '<div><span class="text-xs text-slate-400 block">NIS</span><span class="font-medium text-slate-700">'+(d.nis||'-')+'</span></div>' +
                '<div><span class="text-xs text-slate-400 block">NISN</span><span class="font-medium text-slate-700">'+(d.nisn||'-')+'</span></div>' +
                (d.nik ? '<div><span class="text-xs text-slate-400 block">NIK</span><span class="font-medium text-slate-700">'+d.nik+'</span></div>' : '') +
                (d.kode_dapodik ? '<div><span class="text-xs text-slate-400 block">Kode Dapodik</span><span class="font-medium text-slate-700">'+d.kode_dapodik+'</span></div>' : '') +
                '<div><span class="text-xs text-slate-400 block">Jenis Kelamin</span><span class="font-medium text-slate-700">'+jkLabel+'</span></div>' +
                '<div><span class="text-xs text-slate-400 block">Tempat, Tgl Lahir</span><span class="font-medium text-slate-700">'+(d.tempat_lahir||'-')+', '+(d.tanggal_lahir||'-')+'</span></div>' +
                '<div><span class="text-xs text-slate-400 block">Agama</span><span class="font-medium text-slate-700">'+(d.agama||'-')+'</span></div>' +
                '<div><span class="text-xs text-slate-400 block">No. HP</span><span class="font-medium text-slate-700">'+(d.phone||'-')+'</span></div>' +
                '<div><span class="text-xs text-slate-400 block">Kelas</span><span class="font-medium text-slate-700">'+(d.kelas||'-')+' ('+(d.tingkat||'')+')</span></div>' +
                '<div><span class="text-xs text-slate-400 block">Wali Kelas</span><span class="font-medium text-slate-700">'+(d.wali_kelas||'-')+'</span></div>' +
                '<div><span class="text-xs text-slate-400 block">Nama Ayah</span><span class="font-medium text-slate-700">'+(d.nama_ayah||'-')+'</span></div>' +
                '<div><span class="text-xs text-slate-400 block">Nama Ibu</span><span class="font-medium text-slate-700">'+(d.nama_ibu||'-')+'</span></div>' +
                '<div><span class="text-xs text-slate-400 block">Tanggal Masuk</span><span class="font-medium text-slate-700">'+(d.tanggal_masuk||'-')+'</span></div>' +
                '<div class="col-span-2"><span class="text-xs text-slate-400 block">Alamat</span><span class="font-medium text-slate-700">'+(d.alamat||'-')+'</span></div>' +
                waliHtml +
                '<div class="col-span-2 pt-2 border-t border-slate-100 mt-1"><span class="text-xs text-slate-400 block uppercase tracking-wide font-semibold mb-1">Info Akun</span></div>' +
                '<div><span class="text-xs text-slate-400 block">Email</span><span class="font-medium text-slate-700">'+(d.email||'-')+'</span></div>' +
                '<div><span class="text-xs text-slate-400 block">No. HP Akun</span><span class="font-medium text-slate-700">'+(d.phone_akun||'-')+'</span></div>' +
            '</div>';
        try { if (typeof lucide !== 'undefined') lucide.createIcons(); } catch(e) {}
    })
    .catch(function() {
        content.innerHTML = '<p class="text-red-500 text-center py-4">Gagal memuat data.</p>';
    });
}
function closeDetailModal(){ document.getElementById('detailModal').classList.remove('show'); }
// Event delegation
document.addEventListener('DOMContentLoaded',function(){
    document.querySelector('table').addEventListener('click',function(e){
        var editBtn = e.target.closest('.js-edit-btn');
        if (editBtn) {
            editStudent(editBtn.dataset.editId, editBtn.dataset.editNis, editBtn.dataset.editNisn, editBtn.dataset.editNama, editBtn.dataset.editClass, editBtn.dataset.editStatus);
            return;
        }
        var viewBtn = e.target.closest('.js-view-btn');
        if (viewBtn) {
            openDetailModal(viewBtn.dataset.viewId);
            return;
        }
    });
    var mod = document.getElementById('studentModal');
    if (mod) mod.addEventListener('click',function(e){if(e.target===this)closeModal();});
    var dmod = document.getElementById('detailModal');
    if (dmod) dmod.addEventListener('click',function(e){if(e.target===this)closeDetailModal();});
    try { if (typeof lucide !== 'undefined') lucide.createIcons(); } catch(e) {}
});
</script>
@endpush
