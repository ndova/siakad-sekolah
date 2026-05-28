@extends('layouts.backend')

@section('title', 'Data Orang Tua / Wali')
@section('page_title', 'Data Orang Tua / Wali')

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
    <form method="GET" class="flex gap-2 flex-wrap filter-form">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama wali..." class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200 w-52">
        <select name="hubungan" class="px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
            <option value="">Semua Hubungan</option>
            <option value="Ayah" {{ request('hubungan')=='Ayah'?'selected':'' }}>Ayah</option>
            <option value="Ibu" {{ request('hubungan')=='Ibu'?'selected':'' }}>Ibu</option>
            <option value="Wali" {{ request('hubungan')=='Wali'?'selected':'' }}>Wali</option>
            <option value="Kakek" {{ request('hubungan')=='Kakek'?'selected':'' }}>Kakek</option>
            <option value="Nenek" {{ request('hubungan')=='Nenek'?'selected':'' }}>Nenek</option>
        </select>
        <button type="submit" class="px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium hover:brightness-110"><i data-lucide="search" class="w-4 h-4 inline"></i></button>
    </form>
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium hover:brightness-110 transition">
        <i data-lucide="plus" class="w-4 h-4"></i> Tambah Orang Tua/Wali
    </button>
</div>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden table-responsive">
    <table class="w-full text-sm">
        <thead><tr class="bg-slate-50 text-left">
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Nama</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">JK</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Hubungan</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">No. HP</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Anak (Siswa)</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase w-20">Aksi</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-50">
        @forelse($guardians as $guardian)
        <tr class="hover:bg-slate-50/50 transition">
            <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-accent-100 flex items-center justify-center text-accent font-bold text-xs">{{ strtoupper(substr($guardian->nama_lengkap,0,1)) }}</div>
                    <span class="font-medium text-slate-800">{{ $guardian->nama_lengkap }}</span>
                </div>
            </td>
            <td class="px-5 py-3.5">{{ $guardian->jk == 'L' ? 'L' : 'P' }}</td>
            <td class="px-5 py-3.5"><span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-600">{{ $guardian->hubungan }}</span></td>
            <td class="px-5 py-3.5 text-slate-500">{{ $guardian->phone ?? '-' }}</td>
            <td class="px-5 py-3.5">
                @if($guardian->students->isNotEmpty())
                    @foreach($guardian->students as $siswa)
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 mr-1 mb-0.5">{{ $siswa->nama_lengkap }}</span>
                    @endforeach
                @else
                    <span class="text-slate-300">-</span>
                @endif
            </td>
            <td class="px-5 py-3.5">
                <div class="flex gap-1">
                    <button data-edit-id="{{ $guardian->id }}"
                        data-edit-nama="{{ $guardian->nama_lengkap }}"
                        data-edit-jk="{{ $guardian->jk }}"
                        data-edit-hubungan="{{ $guardian->hubungan }}"
                        data-edit-pekerjaan="{{ $guardian->pekerjaan }}"
                        data-edit-phone="{{ $guardian->phone }}"
                        data-edit-alamat="{{ $guardian->alamat }}"
                        data-edit-student-id="{{ $guardian->students->first()?->id }}"
                        data-edit-student-name="{{ $guardian->students->first()?->nama_lengkap }}"
                        class="p-1.5 rounded-lg hover:bg-accent-50 text-slate-400 hover:text-accent js-edit-btn" title="Edit">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                    </button>
                    <form method="POST" action="{{ route('master.guardians.delete', $guardian->id) }}" onsubmit="event.preventDefault(); showConfirm('Hapus data orang tua/wali ini?', 'Hapus', 'Ya, Hapus', () => this.submit());" class="inline">
                        @csrf
                        <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400">Belum ada data orang tua/wali.</td></tr>
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
    {{ $guardians->links() }}
</div>

{{-- MODAL --}}
<div class="modal-overlay" id="guardianModal">
    <div class="modal-box">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800" id="modalTitle">Tambah Orang Tua/Wali</h3>
            <button onclick="closeModal()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="guardianForm" method="POST" action="{{ route('master.guardians.store') }}" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                <input type="text" name="guardian_nama" id="inputNama" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Jenis Kelamin <span class="text-red-400">*</span></label>
                    <select name="guardian_jk" id="inputJk" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                        <option value="">Pilih</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Hubungan <span class="text-red-400">*</span></label>
                    <select name="guardian_hubungan" id="inputHubungan" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
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
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Pekerjaan</label>
                    <input type="text" name="guardian_pekerjaan" id="inputPekerjaan" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">No. Telepon</label>
                    <input type="text" name="guardian_phone" id="inputPhone" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">Alamat</label>
                <textarea name="guardian_alamat" id="inputAlamat" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200"></textarea>
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
                <p class="text-xs text-slate-400 mt-1">Belum ada data siswa.</p>
                @endif
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium hover:brightness-110">Simpan</button>
                <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 hover:bg-slate-50">Batal</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 50; display: none; align-items: center; justify-content: center; padding: 20px; }
.modal-overlay.show { display: flex; }
.modal-box { background: white; border-radius: 16px; width: 100%; max-width: 560px; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
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

@push('scripts')
<script>
function openModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Orang Tua/Wali';
    document.getElementById('guardianForm').action = '{{ route("master.guardians.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('inputNama').value = '';
    document.getElementById('inputJk').value = '';
    document.getElementById('inputHubungan').value = '';
    document.getElementById('inputPekerjaan').value = '';
    document.getElementById('inputPhone').value = '';
    document.getElementById('inputAlamat').value = '';
    var ss = document.getElementById('studentSearchSelect');
    if (ss && ss._reset) ss._reset();
    document.getElementById('guardianModal').classList.add('show');
}
function editGuardian(id, nama, jk, hubungan, pekerjaan, phone, alamat, studentId, studentName) {
    document.getElementById('modalTitle').textContent = 'Edit Orang Tua/Wali';
    document.getElementById('guardianForm').action = '{{ url("/backend/master/guardians") }}/' + id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('inputNama').value = nama;
    document.getElementById('inputJk').value = jk;
    document.getElementById('inputHubungan').value = hubungan;
    document.getElementById('inputPekerjaan').value = pekerjaan || '';
    document.getElementById('inputPhone').value = phone || '';
    document.getElementById('inputAlamat').value = alamat || '';
    var ss = document.getElementById('studentSearchSelect');
    if (ss && ss._setValue) ss._setValue(studentId || '', studentName || '');
    document.getElementById('guardianModal').classList.add('show');
}
function closeModal(){ document.getElementById('guardianModal').classList.remove('show'); }

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
            if (!opt.dataset.value) return;
            var isSelected = opt.classList.contains('selected');
            var match = q === '' || opt.dataset.text.indexOf(q) !== -1;
            if (hideSelected && isSelected && opt.dataset.value !== selectedValue) {
                opt.style.display = 'none';
            } else {
                opt.style.display = match ? '' : 'none';
                if (match) hasResult = true;
            }
        });
        var placeholder = dropdown.querySelector('.searchable-select__option[data-value=""]');
        if (placeholder) placeholder.style.display = (q === '') ? '' : 'none';
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
        if (opt.classList.contains('selected')) { closeDropdown(); return; }
        input.value = opt.dataset.value ? opt.textContent.replace(/\s*\(.*\)\s*$/, '').trim() : '';
        hidden.value = opt.dataset.value;
        options.forEach(function(o) { o.classList.remove('selected'); });
        if (opt.dataset.value) opt.classList.add('selected');
        closeDropdown();
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
    input.addEventListener('input', function() { openDropdown(); });
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
    document.addEventListener('click', function(e) {
        if (!container.contains(e.target)) closeDropdown();
    });
    container._setValue = function(value, text) {
        hidden.value = value || '';
        input.value = text || '';
        options.forEach(function(o) {
            o.classList.remove('selected');
            if (o.dataset.value === value) o.classList.add('selected');
        });
    };
    container._reset = function() {
        hidden.value = '';
        input.value = '';
        options.forEach(function(o) { o.classList.remove('selected'); });
    };
}

document.addEventListener('DOMContentLoaded', function() {
    initSearchableSelect('studentSearchSelect');
    // Event delegation edit
    document.querySelector('table').addEventListener('click', function(e) {
        var btn = e.target.closest('.js-edit-btn');
        if (!btn) return;
        editGuardian(
            btn.dataset.editId,
            btn.dataset.editNama,
            btn.dataset.editJk,
            btn.dataset.editHubungan,
            btn.dataset.editPekerjaan,
            btn.dataset.editPhone,
            btn.dataset.editAlamat,
            btn.dataset.editStudentId,
            btn.dataset.editStudentName
        );
    });
    var modal = document.getElementById('guardianModal');
    if (modal) modal.addEventListener('click', function(e) { if (e.target === this) closeModal(); });
    try { if (typeof lucide !== 'undefined') lucide.createIcons(); } catch(e) {}
});
</script>
@endpush
