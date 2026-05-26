@extends('layouts.backend')

@section('title', 'Setup Tahun & Semester')
@section('page_title', 'Setup Tahun Ajaran & Semester')

@push('styles')
<style>.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:none;align-items:center;justify-content:center;padding:20px}.modal-overlay.show{display:flex}.modal-box{background:#fff;border-radius:16px;width:100%;max-width:460px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)}</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-accent-50 border-accent-100 text-accent text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
@endif

<div class="flex justify-between items-center mb-5">
    <h2 class="text-sm text-slate-500">Atur periode akademik aktif</h2>
    <button onclick="openYearModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="plus" class="w-4 h-4"></i> Tambah Tahun</button>
</div>

@forelse($years as $year)
<div class="bg-white rounded-2xl border border-slate-100 mb-4 overflow-hidden">
    <div class="p-5 flex items-center justify-between border-b border-slate-50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl {{ $year->is_active ? 'bg-accent-100' : 'bg-slate-100' }} flex items-center justify-center">
                <i data-lucide="{{ $year->is_active ? 'check-circle' : 'circle' }}" class="w-5 h-5 {{ $year->is_active ? 'text-accent' : 'text-slate-400' }}"></i>
            </div>
            <div>
                <h3 class="font-semibold text-slate-800">{{ $year->name }}</h3>
                <span class="text-xs text-slate-400">{{ $year->code }}</span>
                <span class="text-xs text-slate-400 ml-2">{{ \Carbon\Carbon::parse($year->start_date)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($year->end_date)->format('d/m/Y') }}</span>
                @if($year->is_active)<span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-accent-50 text-accent">Aktif</span>@endif
            </div>
        </div>
        <div class="flex gap-2">
            @if(!$year->is_active)
            <form method="POST" action="{{ route('master.academic-years.toggle', $year->id) }}">@csrf<button class="px-3 py-1.5 rounded-lg bg-accent-50 text-xs font-medium text-accent hover:bg-accent-100">Aktifkan</button></form>
            @endif
            <button onclick="openSemesterModal('{{ $year->id }}')" class="px-3 py-1.5 rounded-lg bg-accent-50 text-xs font-medium text-accent hover:bg-accent-100"><i data-lucide="plus" class="w-3.5 h-3.5 inline"></i> Semester</button>
            @if(in_array(auth()->user()->role, ['superadmin', 'admin']))
            <button onclick="editYear('{{ $year->id }}','{{ $year->code }}','{{ $year->start_date->format('Y-m-d') }}','{{ $year->end_date->format('Y-m-d') }}')" class="px-3 py-1.5 rounded-lg bg-accent-50 text-xs font-medium text-accent hover:bg-accent-100"><i data-lucide="pencil" class="w-3.5 h-3.5 inline"></i></button>
            <form method="POST" action="{{ route('master.academic-years.delete', $year->id) }}" onsubmit="event.preventDefault(); showConfirm('Hapus tahun ajaran ini? Semua semester terkait juga akan terhapus.', 'Hapus Tahun Ajaran', 'Ya, Hapus', () => this.submit());">@csrf<button class="px-3 py-1.5 rounded-lg bg-red-50 text-xs font-medium text-red-600 hover:bg-red-100"><i data-lucide="trash-2" class="w-3.5 h-3.5 inline"></i></button></form>
            @endif
        </div>
    </div>
    @if($year->semesters->count())
    <div class="p-5 bg-slate-50/30">
        <div class="text-xs font-semibold text-slate-400 uppercase mb-3">Daftar Semester</div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @foreach($year->semesters as $sem)
        <div class="flex items-center justify-between bg-white rounded-xl p-3 border border-slate-100">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full {{ $sem->is_active ? 'bg-accent' : 'bg-slate-300' }}"></span>
                <span class="text-sm font-medium text-slate-700">{{ $sem->name }}</span>
                <span class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($sem->start_date)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($sem->end_date)->format('d/m/Y') }}</span>
                @if($sem->is_active)<span class="px-1.5 py-0.5 text-[10px] rounded bg-accent-50 text-accent font-medium">Aktif</span>@endif
            </div>
            <div class="flex gap-1.5">
            @if(!$sem->is_active)
            <form method="POST" action="{{ route('master.semesters.toggle', $sem->id) }}">@csrf<button class="text-[11px] px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 hover:bg-accent-50 hover:text-accent">Aktifkan</button></form>
            @endif
            @if(in_array(auth()->user()->role, ['superadmin', 'admin']))
            <button onclick="editSemesterModal('{{ $sem->id }}','{{ $sem->semester_number }}','{{ $sem->start_date->format('Y-m-d') }}','{{ $sem->end_date->format('Y-m-d') }}')" class="text-[11px] px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 hover:bg-accent-50 hover:text-accent"><i data-lucide="pencil" class="w-3 h-3 inline"></i></button>
            <form method="POST" action="{{ route('master.semesters.delete', $sem->id) }}" onsubmit="event.preventDefault(); showConfirm('Hapus semester ini?', 'Hapus Semester', 'Ya, Hapus', () => this.submit());">@csrf<button class="text-[11px] px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 hover:bg-red-50 hover:text-red-600">Hapus</button></form>
            @endif
            </div>
        </div>
        @endforeach
        </div>
    </div>
    @endif
</div>
@empty
<div class="text-center py-16 bg-white rounded-2xl border border-slate-100">
    <i data-lucide="calendar" class="w-12 h-12 mx-auto mb-3 text-slate-200"></i>
    <p class="text-slate-400">Belum ada tahun ajaran.</p>
</div>
@endforelse

{{-- MODAL TAHUN --}}
<div class="modal-overlay" id="yearMod"><div class="modal-box">
<div class="p-5 border-b flex justify-between"><h3 class="font-semibold" id="yearModalTitle">Tambah Tahun Ajaran</h3><button onclick="closeYear()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form id="yearForm" method="POST" action="{{ route('master.academic-years.store') }}" class="p-5 space-y-4">
    @csrf
    <input type="hidden" name="_method" id="yearMethod" value="POST">
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Kode</label><input name="code" id="yearCode" placeholder="2025/2026" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-accent-200"></div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Mulai</label><input name="start_date" id="yearStart" type="date" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-accent-200"></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Selesai</label><input name="end_date" id="yearEnd" type="date" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-accent-200"></div>
    </div>
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeYear()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600 hover:bg-slate-50">Batal</button></div>
</form>
</div></div>

{{-- MODAL SEMESTER --}}
<div class="modal-overlay" id="semMod"><div class="modal-box">
<div class="p-5 border-b flex justify-between"><h3 class="font-semibold" id="semModalTitle">Tambah Semester</h3><button onclick="closeSem()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form id="semForm" method="POST" action="" class="p-5 space-y-4">
    @csrf
    <input type="hidden" name="_method" id="semMethod" value="POST">
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Semester</label>
        <select name="semester_number" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-accent-200">
            <option value="">Pilih Semester</option>
            <option value="1">Semester 1 (Ganjil)</option>
            <option value="2">Semester 2 (Genap)</option>
        </select>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Mulai</label><input name="start_date" type="date" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-accent-200"></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Selesai</label><input name="end_date" type="date" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-accent-200"></div>
    </div>
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeSem()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600 hover:bg-slate-50">Batal</button></div>
</form>
</div></div>
@endsection

@push('scripts')
<script>
function openYearModal(){
    document.getElementById('yearModalTitle').textContent='Tambah Tahun Ajaran';
    document.getElementById('yearForm').action='{{ route("master.academic-years.store") }}';
    document.getElementById('yearMethod').value='POST';
    document.getElementById('yearCode').value='';
    document.getElementById('yearStart').value='';
    document.getElementById('yearEnd').value='';
    document.getElementById('yearMod').classList.add('show');
}
function editYear(id, code, start, end){
    document.getElementById('yearModalTitle').textContent='Edit Tahun Ajaran';
    document.getElementById('yearForm').action='{{ url("/backend/master/academic-years") }}/'+id;
    document.getElementById('yearMethod').value='PUT';
    document.getElementById('yearCode').value=code;
    document.getElementById('yearStart').value=start;
    document.getElementById('yearEnd').value=end;
    document.getElementById('yearMod').classList.add('show');
}
function closeYear(){document.getElementById('yearMod').classList.remove('show');document.getElementById('yearMethod').value='POST';}
function openSemesterModal(yearId){
    document.querySelector('#semForm h3') && (document.querySelector('#semForm h3').textContent = 'Tambah Semester');
    document.getElementById('semForm').action='{{ url("/backend/master/semesters") }}/'+yearId;
    document.getElementById('semForm').querySelector('#semMethod') && (document.getElementById('semForm').querySelector('#semMethod').value = 'POST');
    document.getElementById('semForm').querySelector('select[name=semester_number]').value='';
    document.getElementById('semForm').querySelector('input[name=start_date]').value='';
    document.getElementById('semForm').querySelector('input[name=end_date]').value='';
    document.getElementById('semMod').classList.add('show');
}
function editSemesterModal(id, num, start, end){
    document.querySelector('#semForm h3') && (document.querySelector('#semForm h3').textContent = 'Edit Semester');
    document.getElementById('semForm').action='{{ url("/backend/master/semesters") }}/'+id;
    document.getElementById('semForm').querySelector('#semMethod').value='PUT';
    document.getElementById('semForm').querySelector('select[name=semester_number]').value=num;
    document.getElementById('semForm').querySelector('input[name=start_date]').value=start;
    document.getElementById('semForm').querySelector('input[name=end_date]').value=end;
    document.getElementById('semMod').classList.add('show');
}
function closeSem(){
    document.getElementById('semMod').classList.remove('show');
    // Reset method back to POST for next add
    document.getElementById('semForm').querySelector('#semMethod').value='POST';
}
document.getElementById('yearMod').addEventListener('click',function(e){if(e.target===this)closeYear()});
document.getElementById('semMod').addEventListener('click',function(e){if(e.target===this)closeSem()});
</script>
@endpush
