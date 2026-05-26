@extends('layouts.backend')
@section('title', 'Data Pegawai — SIAKAD')
@section('content')
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-wrap justify-between items-center gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Data Pegawai</h2>
            <p class="text-sm text-slate-500 mt-0.5">Kelola data guru, kepala sekolah, bendahara, BK, TU, dan seluruh staf</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('staff.attendance.grid') }}" class="btn-secondary text-sm px-4 py-2 rounded-lg flex items-center gap-1.5">
                📋 Grid Absensi
            </a>
            <a href="{{ route('staff.attendance.recap') }}" class="btn-secondary text-sm px-4 py-2 rounded-lg flex items-center gap-1.5">
                📊 Rekap Absensi
            </a>
            <button onclick="openModal('addStaffModal')" class="btn-primary text-sm px-4 py-2 rounded-lg flex items-center gap-1.5">
                ➕ Tambah Pegawai
            </button>
        </div>
    </div>

    {{-- Summary Cards per Jabatan --}}
    @if(!empty($totalByJabatan))
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-6">
        @foreach($totalByJabatan as $j => $count)
        <a href="?jabatan={{ $j }}" class="bg-white rounded-xl border border-slate-100 px-4 py-3 hover:shadow-md transition-shadow {{ request('jabatan') === $j ? 'ring-2 ring-indigo-300' : '' }}">
            <span class="text-xs text-slate-400 uppercase tracking-wider">{{ \App\Models\Staff::jabatanLabel($j) }}</span>
            <div class="text-2xl font-bold text-slate-700 mt-1">{{ $count }}</div>
        </a>
        @endforeach
    </div>
    @endif

    {{-- Users tanpa staff profile --}}
    @if($usersWithoutStaff->isNotEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
        <div class="flex items-start gap-3">
            <span class="text-xl">⚠️</span>
            <div class="flex-1">
                <h4 class="font-semibold text-amber-800 text-sm">{{ $usersWithoutStaff->count() }} User Belum Memiliki Profil Staff</h4>
                <p class="text-xs text-amber-600 mt-0.5">User berikut memiliki role internal tapi belum dibuatkan profil pegawai. Klik buat otomatis.</p>
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach($usersWithoutStaff as $u)
                    <form method="POST" action="{{ route('staff.store') }}" class="inline">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $u->id }}">
                        <input type="hidden" name="nama_lengkap" value="{{ $u->name }}">
                        <input type="hidden" name="nip" value="{{ $u->nip ?? '' }}">
                        <input type="hidden" name="jabatan" value="{{ $u->role }}">
                        <button class="bg-amber-100 hover:bg-amber-200 text-amber-800 text-xs px-3 py-1.5 rounded-lg transition-colors">
                            Buat Profil: {{ $u->name }} ({{ \App\Enums\Role::tryFrom($u->role)?->label() ?? $u->role }})
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Search & Filter --}}
    <div class="bg-white rounded-xl border border-slate-100 px-4 py-3 mb-4">
        <form class="flex flex-wrap gap-3 items-center">
            <input name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIP..." class="form-input text-sm w-56">
            <select name="jabatan" class="form-select text-sm">
                <option value="">Semua Jabatan</option>
                @foreach($jabatanList as $j => $label)
                <option value="{{ $j }}" {{ request('jabatan') === $j ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="status_aktif" class="form-select text-sm">
                <option value="">Semua Status</option>
                <option value="1" {{ request('status_aktif') === '1' ? 'selected' : '' }}>Aktif</option>
                <option value="0" {{ request('status_aktif') === '0' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            <button class="btn-primary text-sm px-4 py-1.5 rounded-lg">Filter</button>
            @if(request()->anyFilled(['search','jabatan','status_aktif']))
            <a href="{{ route('staff.index') }}" class="text-sm text-slate-400 hover:text-slate-600">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-slate-100 overflow-hidden table-responsive">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Pegawai</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase w-16">JK</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">NIP / NUPTK</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Jabatan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Gol</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase w-16">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                @forelse($staffList as $s)
                <tr class="hover:bg-slate-50/50 {{ !$s->is_active ? 'opacity-50' : '' }}">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-xs">
                                {{ strtoupper(substr($s->nama_lengkap, 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-medium text-slate-800">{{ $s->nama_lengkap }}</div>
                                <div class="text-xs text-slate-400">{{ $s->user?->email ?? '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center text-xs">{{ $s->jk ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs text-slate-600">
                        <div>{{ $s->nip ?? '-' }}</div>
                        @if($s->nuptk)<div class="text-slate-400">{{ $s->nuptk }}</div>@endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ 
                            $s->jabatan === 'guru' ? 'bg-blue-50 text-blue-700' :
                            ($s->jabatan === 'kepsek' ? 'bg-purple-50 text-purple-700' :
                            ($s->jabatan === 'bendahara' ? 'bg-emerald-50 text-emerald-700' :
                            ($s->jabatan === 'bk' ? 'bg-amber-50 text-amber-700' :
                            ($s->jabatan === 'walikelas' ? 'bg-cyan-50 text-cyan-700' :
                            ($s->jabatan === 'admin' ? 'bg-rose-50 text-rose-700' :
                            'bg-slate-50 text-slate-600')))))
                        }}">
                            {{ \App\Models\Staff::jabatanLabel($s->jabatan) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-500">{{ $s->golongan ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex w-2 h-2 rounded-full {{ $s->is_active ? 'bg-emerald-500' : 'bg-red-400' }}"></span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex gap-1 justify-center">
                            <button onclick="editStaff({{ Js::from($s) }})" class="text-xs text-indigo-600 hover:text-indigo-800 px-1.5 py-0.5">Edit</button>
                            <form action="{{ route('staff.toggle', $s) }}" method="POST" class="inline">
                                @csrf
                                <button class="text-xs {{ $s->is_active ? 'text-amber-600' : 'text-emerald-600' }} hover:underline px-1.5 py-0.5">
                                    {{ $s->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            <form action="{{ route('staff.destroy', $s) }}" method="POST" class="inline" onsubmit="event.preventDefault(); showConfirm('Hapus staff ini?', 'Hapus Staff', 'Ya, Hapus', () => this.submit());">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-500 hover:text-red-700 px-1.5 py-0.5">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-slate-400">Belum ada data pegawai.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-50 flex flex-wrap items-center justify-between gap-3">
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
            {{ $staffList->links() }}
        </div>
    </div>
</div>

{{-- Modal: Add Staff --}}
<div id="addStaffModal" class="modal-overlay hidden" onclick="closeModal('addStaffModal')">
    <div class="modal-content max-w-lg" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-slate-800">Tambah Pegawai</h3>
            <button onclick="closeModal('addStaffModal')" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
        </div>
        <form method="POST" action="{{ route('staff.store') }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nama Lengkap *</label>
                    <input name="nama_lengkap" required class="form-input text-sm w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Link User</label>
                    <select name="user_id" class="form-select text-sm w-full">
                        <option value="">-- Tanpa user login --</option>
                        @foreach($usersWithoutStaff as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">NIP</label>
                    <input name="nip" class="form-input text-sm w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">NUPTK</label>
                    <input name="nuptk" class="form-input text-sm w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Jabatan *</label>
                    <select name="jabatan" required class="form-select text-sm w-full">
                        @foreach($jabatanList as $j => $label)
                        <option value="{{ $j }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Golongan</label>
                    <input name="golongan" placeholder="III/a" class="form-input text-sm w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">JK</label>
                    <select name="jk" class="form-select text-sm w-full">
                        <option value="">--</option><option value="L">Laki-laki</option><option value="P">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Pendidikan</label>
                    <input name="pendidikan_terakhir" class="form-input text-sm w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Tempat Lahir</label>
                    <input name="tempat_lahir" class="form-input text-sm w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-input text-sm w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Agama</label>
                    <select name="agama" class="form-select text-sm w-full">
                        <option value="">--</option>
                        <option>Islam</option><option>Kristen</option><option>Katolik</option>
                        <option>Hindu</option><option>Buddha</option><option>Konghucu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Phone</label>
                    <input name="phone" class="form-input text-sm w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" class="form-input text-sm w-full">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Alamat</label>
                    <textarea name="alamat" rows="2" class="form-input text-sm w-full"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('addStaffModal')" class="btn-secondary text-sm px-4 py-2 rounded-lg">Batal</button>
                <button class="btn-primary text-sm px-4 py-2 rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Edit Staff --}}
<div id="editStaffModal" class="modal-overlay hidden" onclick="closeModal('editStaffModal')">
    <div class="modal-content max-w-lg" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-slate-800">Edit Pegawai</h3>
            <button onclick="closeModal('editStaffModal')" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
        </div>
        <form id="editStaffForm" method="POST" class="space-y-3">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-3" id="editStaffFields">
                <!-- filled by JS -->
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('editStaffModal')" class="btn-secondary text-sm px-4 py-2 rounded-lg">Batal</button>
                <button class="btn-primary text-sm px-4 py-2 rounded-lg">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.body.style.overflow = '';
}

function editStaff(staff) {
    const form = document.getElementById('editStaffForm');
    form.action = '/backend/staff/' + staff.id;
    const fields = document.getElementById('editStaffFields');
    const jabatanOptions = {!! Js::from($jabatanList) !!};
    let jOpts = '';
    for (const [k, v] of Object.entries(jabatanOptions)) {
        jOpts += `<option value="${k}" ${staff.jabatan === k ? 'selected' : ''}>${v}</option>`;
    }

    fields.innerHTML = `
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Nama Lengkap *</label>
            <input name="nama_lengkap" value="${escapeHtml(staff.nama_lengkap)}" required class="form-input text-sm w-full">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">NIP</label>
            <input name="nip" value="${escapeHtml(staff.nip || '')}" class="form-input text-sm w-full">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">NUPTK</label>
            <input name="nuptk" value="${escapeHtml(staff.nuptk || '')}" class="form-input text-sm w-full">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Jabatan *</label>
            <select name="jabatan" required class="form-select text-sm w-full">${jOpts}</select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Golongan</label>
            <input name="golongan" value="${escapeHtml(staff.golongan || '')}" class="form-input text-sm w-full">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Pendidikan</label>
            <input name="pendidikan_terakhir" value="${escapeHtml(staff.pendidikan_terakhir || '')}" class="form-input text-sm w-full">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">JK</label>
            <select name="jk" class="form-select text-sm w-full">
                <option value="">--</option>
                <option value="L" ${staff.jk === 'L' ? 'selected' : ''}>Laki-laki</option>
                <option value="P" ${staff.jk === 'P' ? 'selected' : ''}>Perempuan</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Tempat Lahir</label>
            <input name="tempat_lahir" value="${escapeHtml(staff.tempat_lahir || '')}" class="form-input text-sm w-full">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" value="${staff.tanggal_lahir || ''}" class="form-input text-sm w-full">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Agama</label>
            <select name="agama" class="form-select text-sm w-full">
                <option value="">--</option>
                ${['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'].map(a => `<option ${staff.agama===a?'selected':''}>${a}</option>`).join('')}
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Phone</label>
            <input name="phone" value="${escapeHtml(staff.phone || '')}" class="form-input text-sm w-full">
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Masuk</label>
            <input type="date" name="tanggal_masuk" value="${staff.tanggal_masuk || ''}" class="form-input text-sm w-full">
        </div>
        <div class="col-span-2">
            <label class="block text-xs font-medium text-slate-600 mb-1">Alamat</label>
            <textarea name="alamat" rows="2" class="form-input text-sm w-full">${escapeHtml(staff.alamat || '')}</textarea>
        </div>
    `;
    openModal('editStaffModal');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
