@extends('layouts.backend')

@section('title', 'Manajemen User')
@section('page_title', 'Manajemen User')

@push('styles')
<style>
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 50; display: none; align-items: center; justify-content: center; padding: 20px; }
    .modal-overlay.show { display: flex; }
    .modal-box { background: white; border-radius: 16px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
</style>
@endpush

@section('content')
{{-- FLASH MESSAGE --}}
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

{{-- Top Bar --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <form method="GET" class="flex gap-2 flex-wrap filter-form">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari user..." class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200 focus:border-accent-300 w-56">
        <select name="role" class="px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
            <option value="">Semua Role</option>
            <option value="admin" {{ request('role')=='admin'?'selected':'' }}>Admin</option>
            <option value="guru" {{ request('role')=='guru'?'selected':'' }}>Guru</option>
            <option value="walikelas" {{ request('role')=='walikelas'?'selected':'' }}>Wali Kelas</option>
            <option value="bendahara" {{ request('role')=='bendahara'?'selected':'' }}>Bendahara</option>
            <option value="kepsek" {{ request('role')=='kepsek'?'selected':'' }}>Kepala Sekolah</option>
            <option value="siswa" {{ request('role')=='siswa'?'selected':'' }}>Siswa</option>
            <option value="orang_tua" {{ request('role')=='orang_tua'?'selected':'' }}>Orang Tua</option>
            <option value="tata_usaha" {{ request('role')=='tata_usaha'?'selected':'' }}>Tata Usaha</option>
            <option value="bk" {{ request('role')=='bk'?'selected':'' }}>BK</option>
            <option value="perpustakaan" {{ request('role')=='perpustakaan'?'selected':'' }}>Perpustakaan</option>
        </select>
        <select name="status" class="px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
            <option value="">Semua Status</option>
            <option value="aktif" {{ request('status')=='aktif'?'selected':'' }}>Aktif</option>
            <option value="nonaktif" {{ request('status')=='nonaktif'?'selected':'' }}>Nonaktif</option>
        </select>
        <button type="submit" class="px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium hover:brightness-110 transition">
            <i data-lucide="search" class="w-4 h-4 inline"></i>
        </button>
    </form>
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium hover:brightness-110 transition">
        <i data-lucide="plus" class="w-4 h-4"></i> Tambah User
    </button>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden table-responsive">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50 text-left">
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Nama</th>
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Email</th>
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Role</th>
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
                <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase w-20">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($users as $user)
            <tr class="hover:bg-slate-50/50 transition">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-accent-100 flex items-center justify-center text-accent font-bold text-xs">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                        <span class="font-medium text-slate-800">{{ $user->name }}</span>
                    </div>
                </td>
                <td class="px-5 py-3.5 text-slate-500">{{ $user->email }}</td>
                <td class="px-5 py-3.5"><span class="px-2.5 py-1 rounded-full text-xs font-medium bg-accent-50 text-accent">{{ str_replace('_',' ',$user->role) }}</span></td>
                <td class="px-5 py-3.5">
                    <form method="POST" action="{{ route('master.users.toggle-status', $user->id) }}" class="inline">
                        @csrf
                        <select name="is_active" onchange="this.form.submit()" class="px-2.5 py-1 rounded-full text-xs font-medium border-0 cursor-pointer {{ $user->is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                            <option value="1" {{ $user->is_active ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </form>
                </td>
                <td class="px-5 py-3.5">
                    <div class="flex gap-1">
                        <button data-edit-id="{{ $user->id }}" data-edit-name="{{ $user->name }}" data-edit-email="{{ $user->email }}" data-edit-role="{{ $user->role }}" class="p-1.5 rounded-lg hover:bg-accent-50 text-slate-400 hover:text-accent transition js-edit-btn" title="Edit">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </button>
                        <form method="POST" action="{{ route('master.users.delete', $user->id) }}" onsubmit="event.preventDefault(); showConfirm('Hapus permanen user ini?', 'Hapus User', 'Ya, Hapus', () => this.submit());" class="inline">
                            @csrf
                            <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600 transition" title="Hapus">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-12 text-center text-slate-400">Belum ada data user.</td></tr>
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
    {{ $users->links() }}
</div>

{{-- ADD/EDIT MODAL --}}
<div class="modal-overlay" id="userModal">
    <div class="modal-box">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800" id="modalTitle">Tambah User</h3>
            <button onclick="closeModal()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="userForm" method="POST" action="{{ route('master.users.store') }}" enctype="multipart/form-data" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Nama Lengkap</label>
                <input type="text" name="name" id="inputName" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Email</label>
                <input type="email" name="email" id="inputEmail" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Password <span id="passHint" class="text-slate-400 font-normal">(min. 8 karakter)</span></label>
                <input type="password" name="password" id="inputPass" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Role</label>
                <select name="role" id="inputRole" required onchange="toggleRoleFields()" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                    <option value="">Pilih Role</option>
                    <option value="admin">Admin</option>
                    <option value="guru">Guru</option>
                    <option value="walikelas">Wali Kelas</option>
                    <option value="bendahara">Bendahara</option>
                    <option value="kepsek">Kepala Sekolah</option>
                    <option value="siswa">Siswa</option>
                    <option value="orang_tua">Orang Tua</option>
                    <option value="tata_usaha">Tata Usaha</option>
                    <option value="bk">BK</option>
                    <option value="perpustakaan">Perpustakaan</option>
                </select>
            </div>
            {{-- Fields khusus Guru --}}
            <div id="subjectSection" style="display:none;">
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Mata Pelajaran yang Diampu <span class="text-slate-400 font-normal">(bisa pilih lebih dari satu)</span></label>
                <div class="max-h-40 overflow-y-auto border border-slate-200 rounded-xl p-2 space-y-1">
                    @foreach($subjects as $subj)
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer text-sm">
                        <input type="checkbox" name="subject_ids[]" value="{{ $subj->id }}" class="rounded accent-accent">
                        <span>{{ $subj->code }} — {{ $subj->name }}</span>
                    </label>
                    @endforeach
                </div>
                @if($subjects->isEmpty())
                <p class="text-xs text-slate-400 mt-1">Belum ada mata pelajaran. Tambahkan di menu Mapel terlebih dahulu.</p>
                @endif
            </div>
            {{-- Fields khusus Siswa --}}
            <div id="studentSection" style="display:none;">
                <div class="grid grid-cols-2 gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">NIS</label>
                        <input type="text" name="nis" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">NISN</label>
                        <input type="text" name="nisn" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Jenis Kelamin</label>
                        <select name="jk" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                            <option value="">Pilih</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Kelas</label>
                        <select name="class_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                            <option value="">Pilih Kelas</option>
                            @foreach(\App\Models\SchoolClass::where('school_id', auth()->user()->school_id)->where('is_active', true)->orderBy('code')->get() as $cls)
                            <option value="{{ $cls->id }}">{{ $cls->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Agama</label>
                        <select name="agama" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                            <option value="">Pilih</option>
                            <option value="Islam">Islam</option>
                            <option value="Kristen">Kristen</option>
                            <option value="Katolik">Katolik</option>
                            <option value="Hindu">Hindu</option>
                            <option value="Buddha">Buddha</option>
                            <option value="Konghucu">Konghucu</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">No. Telepon</label>
                        <input type="text" name="phone" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Alamat</label>
                    <textarea name="alamat" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Foto</label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/jpg" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium hover:brightness-110 transition">Simpan</button>
                <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 hover:bg-slate-50 transition">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleRoleFields() {
    var role = document.getElementById('inputRole').value;
    document.getElementById('subjectSection').style.display = (role === 'guru') ? 'block' : 'none';
    document.getElementById('studentSection').style.display = (role === 'siswa') ? 'block' : 'none';
}
function openModal() {
    document.getElementById('modalTitle').textContent = 'Tambah User';
    document.getElementById('userForm').action = '{{ route("master.users.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('inputName').value = '';
    document.getElementById('inputEmail').value = '';
    document.getElementById('inputPass').value = '';
    document.getElementById('inputPass').required = true;
    document.getElementById('inputRole').value = '';
    document.getElementById('subjectSection').style.display = 'none';
    document.getElementById('studentSection').style.display = 'none';
    // Clear student fields
    var s = document.getElementById('studentSection');
    s.querySelectorAll('input[type=text], input[type=date], textarea, select').forEach(el => el.value = '');
    s.querySelector('input[type=file]') && (s.querySelector('input[type=file]').value = '');
    // Uncheck all subject checkboxes
    document.querySelectorAll('#subjectSection input[type=checkbox]').forEach(cb => cb.checked = false);
    document.getElementById('userModal').classList.add('show');
}
function editUser(id, name, email, role) {
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('userForm').action = '{{ url("/backend/master/users") }}/' + id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('inputName').value = name;
    document.getElementById('inputEmail').value = email;
    document.getElementById('inputPass').value = '';
    document.getElementById('inputPass').required = false;
    document.getElementById('inputRole').value = role;
    document.getElementById('subjectSection').style.display = (role === 'guru') ? 'block' : 'none';
    document.getElementById('studentSection').style.display = 'none'; // Edit user tidak tampilkan field siswa
    document.getElementById('userModal').classList.add('show');
}
function closeModal() { document.getElementById('userModal').classList.remove('show'); }

// Event delegation: tangkap klik dari container tabel
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('table').addEventListener('click', function(e) {
        var btn = e.target.closest('.js-edit-btn');
        if (!btn) return;
        editUser(btn.dataset.editId, btn.dataset.editName, btn.dataset.editEmail, btn.dataset.editRole);
    });
    // Modal click-outside-to-close
    var modal = document.getElementById('userModal');
    if (modal) modal.addEventListener('click', function(e) { if (e.target === this) closeModal(); });
    // Lucide icons (safe)
    try { if (typeof lucide !== 'undefined') lucide.createIcons(); } catch(e) {}
});
</script>
@endpush
