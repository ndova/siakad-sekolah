@extends('layouts.backend')

@section('title', 'Manajemen User')
@section('page_title', 'Manajemen User')

@push('styles')
<style>
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 50; display: none; align-items: center; justify-content: center; padding: 20px; }
    .modal-overlay.show { display: flex; }
    .modal-box { background: white; border-radius: 16px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
    /* Searchable Select */
    .searchable-select { position: relative; }
    .searchable-select__input-wrap { position: relative; }
    .searchable-select__input-wrap input { padding-right: 2rem; }
    .searchable-select__arrow { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; transition: transform 0.2s; }
    .searchable-select.open .searchable-select__arrow { transform: translateY(-50%) rotate(180deg); }
    .searchable-select__dropdown { position: absolute; z-index: 60; top: 100%; left: 0; right: 0; margin-top: 4px; background: white; border: 1px solid #e2e8f0; border-radius: 12px; max-height: 200px; overflow-y: auto; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
    .searchable-select__dropdown.hidden { display: none; }
    .searchable-select__option { padding: 8px 12px; cursor: pointer; font-size: 0.875rem; color: #334155; transition: background 0.15s; }
    .searchable-select__option:hover,
    .searchable-select__option.active { background: #f1f5f9; }
    .searchable-select__option.selected { background: #eff6ff; color: #2563eb; font-weight: 500; }
    .searchable-select__option.selected:hover { background: #eff6ff; cursor: default; }
    .searchable-select__option.no-result { color: #94a3b8; cursor: default; text-align: center; }
    .searchable-select__option.no-result:hover { background: transparent; }
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
                        <button data-edit-id="{{ $user->id }}" data-edit-name="{{ $user->name }}" data-edit-email="{{ $user->email }}" data-edit-role="{{ $user->role }}" data-edit-guardian-nama="{{ $user->guardian?->nama_lengkap }}" data-edit-guardian-jk="{{ $user->guardian?->jk }}" data-edit-guardian-hubungan="{{ $user->guardian?->hubungan }}" data-edit-guardian-pekerjaan="{{ $user->guardian?->pekerjaan }}" data-edit-guardian-phone="{{ $user->guardian?->phone }}" data-edit-guardian-alamat="{{ $user->guardian?->alamat }}" data-edit-guardian-student-id="{{ $user->guardian?->students?->first()?->id }}" data-edit-guardian-student-name="{{ $user->guardian?->students?->first()?->nama_lengkap }}" class="p-1.5 rounded-lg hover:bg-accent-50 text-slate-400 hover:text-accent transition js-edit-btn" title="Edit">
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
                <div class="mt-3">
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Kelas Mengajar <span class="text-slate-400 font-normal">(pilih kelas yang diajar)</span></label>
                    <div class="max-h-32 overflow-y-auto border border-slate-200 rounded-xl p-2 space-y-1">
                        @foreach($classes as $cls)
                        <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-slate-50 cursor-pointer text-sm">
                            <input type="checkbox" name="class_ids[]" value="{{ $cls->id }}" class="rounded accent-accent">
                            <span>{{ $cls->code }} — {{ $cls->name }} ({{ $cls->tingkat }})</span>
                        </label>
                        @endforeach
                    </div>
                    @if($classes->isEmpty())
                    <p class="text-xs text-slate-400 mt-1">Belum ada kelas aktif.</p>
                    @endif
                </div>
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
            {{-- Fields khusus Orang Tua / Wali --}}
            <div id="guardianSection" style="display:none;">
                <input type="hidden" name="guardian_nama" id="guardianNama" value="">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Jenis Kelamin <span class="text-red-400">*</span></label>
                        <select name="guardian_jk" id="guardianJk" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                            <option value="">Pilih</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Hubungan <span class="text-red-400">*</span></label>
                        <select name="guardian_hubungan" id="guardianHubungan" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                            <option value="">Pilih</option>
                            <option value="Ayah">Ayah</option>
                            <option value="Ibu">Ibu</option>
                            <option value="Wali">Wali</option>
                            <option value="Kakek">Kakek</option>
                            <option value="Nenek">Nenek</option>
                            <option value="Paman">Paman</option>
                            <option value="Bibi">Bibi</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Pekerjaan</label>
                        <input type="text" name="guardian_pekerjaan" id="guardianPekerjaan" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">No. Telepon</label>
                        <input type="text" name="guardian_phone" id="guardianPhone" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Alamat</label>
                    <textarea name="guardian_alamat" id="guardianAlamat" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Anak (Siswa)</label>
                    <div class="searchable-select" id="studentSearchSelect">
                        <div class="searchable-select__input-wrap">
                            <input type="text" id="studentSearchInput" placeholder="Cari nama siswa..." autocomplete="off" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                            <i data-lucide="chevron-down" class="searchable-select__arrow w-4 h-4 text-slate-400"></i>
                        </div>
                        <div class="searchable-select__dropdown hidden" id="studentSearchDropdown">
                            <div class="searchable-select__option" data-value="" data-text="-- Tidak memilih --">-- Tidak memilih --</div>
                            @foreach($students as $s)
                            <div class="searchable-select__option" data-value="{{ $s->id }}" data-text="{{ strtolower($s->nama_lengkap) }} {{ strtolower($s->nis) }}">{{ $s->nama_lengkap }} <span class="text-slate-400 text-xs">({{ $s->nis }})</span></div>
                            @endforeach
                        </div>
                        <input type="hidden" name="student_id" id="guardianStudentId" value="">
                    </div>
                    @if($students->isEmpty())
                    <p class="text-xs text-slate-400 mt-1">Belum ada data siswa. Link ke siswa bisa dilakukan nanti.</p>
                    @endif
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
    document.getElementById('guardianSection').style.display = (role === 'orang_tua') ? 'block' : 'none';
    // Auto-fill guardian_nama dari field Nama Lengkap utama
    if (role === 'orang_tua') {
        document.getElementById('guardianNama').value = document.getElementById('inputName').value;
    }
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
    document.getElementById('guardianSection').style.display = 'none';
    // Clear student fields
    var s = document.getElementById('studentSection');
    s.querySelectorAll('input[type=text], input[type=date], textarea, select').forEach(el => el.value = '');
    s.querySelector('input[type=file]') && (s.querySelector('input[type=file]').value = '');
    // Clear guardian fields
    var g = document.getElementById('guardianSection');
    g.querySelectorAll('input[type=text], textarea').forEach(el => el.value = '');
    g.querySelectorAll('select').forEach(el => el.value = '');
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
    document.getElementById('guardianSection').style.display = (role === 'orang_tua') ? 'block' : 'none';
    document.getElementById('userModal').classList.add('show');
}
function closeModal() { document.getElementById('userModal').classList.remove('show'); }

// ─── Searchable Select ──────────────────────────────────────────────
function initSearchableSelect(containerId) {
    var container = document.getElementById(containerId);
    if (!container) return;
    var input = container.querySelector('.searchable-select__input-wrap input');
    var dropdown = container.querySelector('.searchable-select__dropdown');
    var hidden = container.querySelector('input[type=hidden]');
    var options = dropdown.querySelectorAll('.searchable-select__option[data-value]');
    var activeIdx = -1;

    function filterOptions(query, hideSelected) {
        var q = query.toLowerCase().trim();
        var hasResult = false;
        var selectedValue = hidden.value;
        options.forEach(function(opt) {
            if (!opt.dataset.value) return; // skip placeholder
            var isSelected = opt.classList.contains('selected');
            var match = q === '' || opt.dataset.text.indexOf(q) !== -1;
            // Hide jika sudah selected dan masih di opsi yang sama
            if (hideSelected && isSelected && opt.dataset.value !== selectedValue) {
                opt.style.display = 'none';
            } else {
                opt.style.display = match ? '' : 'none';
                if (match) hasResult = true;
            }
        });
        // Tampilkan/tidak placeholder
        var placeholder = dropdown.querySelector('.searchable-select__option[data-value=""]');
        if (placeholder) placeholder.style.display = (q === '') ? '' : 'none';
        // No result
        var noRes = dropdown.querySelector('.no-result');
        if (!hasResult && q !== '') {
            if (!noRes) {
                noRes = document.createElement('div');
                noRes.className = 'searchable-select__option no-result';
                noRes.textContent = 'Tidak ditemukan';
                dropdown.appendChild(noRes);
            }
            noRes.style.display = '';
        } else if (noRes) {
            noRes.style.display = 'none';
        }
        activeIdx = -1;
    }

    function openDropdown() {
        dropdown.classList.remove('hidden');
        container.classList.add('open');
        filterOptions(input.value, true);
    }

    function closeDropdown() {
        dropdown.classList.add('hidden');
        container.classList.remove('open');
    }

    function selectOption(opt) {
        if (opt.classList.contains('selected')) {
            closeDropdown();
            return;
        }
        input.value = opt.dataset.value ? opt.textContent.replace(/\s*\(.*\)\s*$/, '').trim() : '';
        hidden.value = opt.dataset.value;
        options.forEach(function(o) { o.classList.remove('selected'); });
        if (opt.dataset.value) opt.classList.add('selected');
        closeDropdown();
        // Refresh icon
        try { if (typeof lucide !== 'undefined') lucide.createIcons(); } catch(e) {}
    }

    function setActive(idx) {
        var visible = Array.from(options).filter(function(o) { return o.style.display !== 'none' && o.dataset.value; });
        if (visible.length === 0) return;
        activeIdx = ((idx % visible.length) + visible.length) % visible.length;
        options.forEach(function(o) { o.classList.remove('active'); });
        visible[activeIdx].classList.add('active');
        visible[activeIdx].scrollIntoView({ block: 'nearest' });
    }

    input.addEventListener('focus', openDropdown);
    input.addEventListener('input', function() {
        openDropdown();
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowDown') { e.preventDefault(); setActive(activeIdx + 1); return; }
        if (e.key === 'ArrowUp') { e.preventDefault(); setActive(activeIdx - 1); return; }
        if (e.key === 'Enter') {
            e.preventDefault();
            var visible = Array.from(options).filter(function(o) { return o.style.display !== 'none' && o.dataset.value; });
            if (activeIdx >= 0 && activeIdx < visible.length) {
                selectOption(visible[activeIdx]);
            } else if (visible.length === 1) {
                selectOption(visible[0]);
            }
            return;
        }
        if (e.key === 'Escape') { closeDropdown(); input.blur(); }
    });

    dropdown.addEventListener('click', function(e) {
        var opt = e.target.closest('.searchable-select__option');
        if (!opt || opt.dataset.value === undefined) return;
        if (opt.classList.contains('no-result')) return;
        selectOption(opt);
    });

    // Close on click outside
    document.addEventListener('click', function(e) {
        if (!container.contains(e.target)) closeDropdown();
    });

    // Expose setValue for edit mode
    container._setValue = function(value, text) {
        hidden.value = value || '';
        input.value = text || '';
        options.forEach(function(o) {
            o.classList.remove('selected');
            if (o.dataset.value === value) o.classList.add('selected');
        });
    };

    // Expose reset for new modal
    container._reset = function() {
        hidden.value = '';
        input.value = '';
        options.forEach(function(o) { o.classList.remove('selected'); });
    };
}

// Event delegation: tangkap klik dari container tabel
document.addEventListener('DOMContentLoaded', function() {
    // Init searchable select
    initSearchableSelect('studentSearchSelect');

    document.querySelector('table').addEventListener('click', function(e) {
        var btn = e.target.closest('.js-edit-btn');
        if (!btn) return;
        editUser(btn.dataset.editId, btn.dataset.editName, btn.dataset.editEmail, btn.dataset.editRole);
        // Populate guardian fields from data attributes
        var role = btn.dataset.editRole;
        if (role === 'orang_tua') {
            document.getElementById('guardianNama').value = btn.dataset.editGuardianNama || '';
            document.getElementById('guardianJk').value = btn.dataset.editGuardianJk || '';
            document.getElementById('guardianHubungan').value = btn.dataset.editGuardianHubungan || '';
            document.getElementById('guardianPekerjaan').value = btn.dataset.editGuardianPekerjaan || '';
            document.getElementById('guardianPhone').value = btn.dataset.editGuardianPhone || '';
            document.getElementById('guardianAlamat').value = btn.dataset.editGuardianAlamat || '';
            // Set searchable select value
            var ss = document.getElementById('studentSearchSelect');
            if (ss && ss._setValue) {
                ss._setValue(btn.dataset.editGuardianStudentId || '', btn.dataset.editGuardianStudentName || '');
            }
        }
    });
    // Reset searchable select on modal open (via openModal override)
    var origOpenModal = openModal;
    openModal = function() {
        origOpenModal();
        var ss = document.getElementById('studentSearchSelect');
        if (ss && ss._reset) ss._reset();
    };

    // Modal click-outside-to-close
    var modal = document.getElementById('userModal');
    if (modal) modal.addEventListener('click', function(e) { if (e.target === this) closeModal(); });
    // Lucide icons (safe)
    try { if (typeof lucide !== 'undefined') lucide.createIcons(); } catch(e) {}
});
</script>
@endpush
