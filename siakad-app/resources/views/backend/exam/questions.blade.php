@extends('layouts.backend')

@section('title', 'Kelola Soal')
@section('page_title', 'Kelola Soal')

@push('styles')
<style>
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:none;align-items:center;justify-content:center;padding:20px}.modal-overlay.show{display:flex}.modal-box{background:#fff;border-radius:16px;width:100%;max-width:600px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)}
.chevron-icon{transition:transform .2s}.chevron-icon.rotate-180{transform:rotate(180deg)}
.group-body{transition:max-height .3s ease,opacity .3s ease}.group-body.hidden{display:none}
</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <form method="GET" class="flex gap-2 flex-wrap filter-form">
        <select name="question_bank_id" onchange="this.form.submit()" class="px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200"><option value="">Semua Bank</option>@foreach($banks as $b)<option value="{{ $b->id }}" {{ request('question_bank_id')==$b->id?'selected':'' }}>{{ $b->name }}</option>@endforeach</select>
        <input name="search" value="{{ request('search') }}" placeholder="Cari soal..." class="px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200 w-40">
        <button class="px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="search" class="w-4 h-4 inline"></i></button>
        @if(request()->anyFilled(['question_bank_id','search']))
        <a href="{{ route('exam.questions') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-600 hover:bg-slate-50"><i data-lucide="x" class="w-4 h-4 inline mr-1"></i>Reset</a>
        @endif
    </form>
    <div class="flex items-center gap-3">
        <span class="text-xs text-slate-500"><span class="font-semibold text-slate-700">{{ $totalQuestions }}</span> soal · <span class="font-semibold text-slate-700">{{ $groupedQuestions->count() }}</span> mapel</span>
        <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium"><i data-lucide="plus" class="w-4 h-4"></i> Tambah Soal</button>
    </div>
</div>

{{-- GROUPED BY MATA PELAJARAN --}}
@forelse($groupedQuestions as $groupId => $group)
@php
    $subject = $group['subject'];
    $questions = $group['questions'];
    $isFirst = $loop->first;
@endphp
<div class="mb-4 bg-white rounded-2xl border border-slate-100 overflow-hidden question-group">
    {{-- Subject Header --}}
    <button onclick="toggleGroup(this)" class="w-full flex items-center gap-4 px-5 py-3.5 hover:bg-slate-50/50 transition text-left group-header">
        <div class="w-10 h-10 rounded-xl bg-accent-100 flex items-center justify-center shrink-0">
            <i data-lucide="book-open" class="w-5 h-5 text-accent-600"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <h3 class="font-semibold text-slate-800">{{ $subject->name ?? 'Tanpa Mapel' }}</h3>
                <span class="text-xs text-slate-400">({{ $subject->code ?? '-' }})</span>
            </div>
            <div class="flex items-center gap-3 text-xs text-slate-400 mt-0.5">
                <span><span class="font-medium text-slate-600">{{ $group['count'] }}</span> soal</span>
                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                <span>{{ $group['pg'] }} PG</span>
                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                <span>{{ $group['esai'] }} Esai</span>
            </div>
        </div>
        <i data-lucide="chevron-down" class="w-5 h-5 text-slate-400 transition-transform chevron-icon {{ $isFirst ? '' : 'rotate-180' }}"></i>
    </button>

    {{-- Questions Table --}}
    <div class="border-t border-slate-50 group-body {{ $isFirst ? '' : 'hidden' }}">
    <div class="table-responsive">
    <table class="w-full text-sm">
    <thead><tr class="bg-slate-50/50 text-left">
        <th class="px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase w-8">#</th>
        <th class="px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Bank</th>
        <th class="px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Pertanyaan</th>
        <th class="px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Tipe</th>
        <th class="px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Skor</th>
        <th class="px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Kognitif</th>
        <th class="px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase w-20">Aksi</th>
    </tr></thead>
    <tbody class="divide-y divide-slate-50">
    @foreach($questions as $q)
    <tr class="hover:bg-slate-50/30">
        <td class="px-4 py-2.5 font-mono text-xs text-slate-400">{{ $loop->iteration }}</td>
        <td class="px-4 py-2.5 text-xs text-slate-500 max-w-[120px] truncate" title="{{ $q->questionBank->name ?? '-' }}">{{ $q->questionBank->name ?? '-' }}</td>
        <td class="px-4 py-2.5">
            <span class="text-slate-700 line-clamp-2 text-sm">{{ $q->content }}</span>
            @if($q->type === 'audio' && !empty($q->media['audio']))
            <div class="mt-1">
                <audio controls class="h-7 w-full max-w-[200px]">
                    <source src="{{ asset('storage/'.$q->media['audio']) }}" type="audio/mpeg">
                </audio>
            </div>
            @endif
        </td>
        <td class="px-4 py-2.5"><span class="px-2 py-1 text-xs rounded-full {{
            $q->type=='pg' ? 'bg-sky-50 text-sky-600' :
            ($q->type=='bs' ? 'bg-emerald-50 text-emerald-600' :
            ($q->type=='jodoh' ? 'bg-amber-50 text-amber-600' :
            ($q->type=='audio' ? 'bg-rose-50 text-rose-600' :
            'bg-purple-50 text-purple-600')))}}">{{
            $q->type=='pg' ? 'PG' :
            ($q->type=='bs' ? 'B/S' :
            ($q->type=='jodoh' ? 'Jodoh' :
            ($q->type=='audio' ? 'Audio' :
            'Esai')))}}</span></td>
        <td class="px-4 py-2.5 font-mono text-xs text-center text-slate-500">{{ $q->score ?? 1 }}</td>
        <td class="px-4 py-2.5 text-xs">
            @if($q->level_kognitif)
            <span class="px-1.5 py-0.5 rounded text-[10px] font-medium {{
                $q->level_kognitif=='L3' ? 'bg-red-50 text-red-600' :
                ($q->level_kognitif=='L2' ? 'bg-amber-50 text-amber-600' :
                'bg-emerald-50 text-emerald-600')}}">{{ $q->level_kognitif }}</span>
            @else
            <span class="text-slate-300">-</span>
            @endif
        </td>
        <td class="px-4 py-2.5">
            <div class="flex items-center gap-1">
                <button onclick="openEditModal('{{ $q->id }}')" class="p-1 rounded-lg hover:bg-sky-50 text-slate-400 hover:text-sky-600"><i data-lucide="pencil" class="w-3.5 h-3.5"></i></button>
                <form method="POST" action="{{ route('exam.questions.delete',$q->id) }}" onsubmit="event.preventDefault(); showConfirm('Hapus soal ini?', 'Hapus Soal', 'Ya, Hapus', () => this.submit());">@csrf<button class="p-1 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button></form>
            </div>
        </td>
    </tr>
    @endforeach
    </tbody></table>
    </div>
    </div>
</div>
@empty
<div class="text-center py-16 bg-white rounded-2xl border border-slate-100">
    <i data-lucide="help-circle" class="w-12 h-12 mx-auto mb-3 text-slate-200"></i>
    <p class="text-slate-400">Belum ada soal.</p>
    <p class="text-xs text-slate-300 mt-1">Tambahkan soal melalui bank soal terlebih dahulu.</p>
</div>
@endforelse

{{-- MODAL --}}
<div class="modal-overlay" id="mod"><div class="modal-box"><div class="p-5 border-b flex justify-between"><h3 class="font-semibold">Tambah Soal</h3><button onclick="closeMod()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form method="POST" action="{{ route('exam.questions.store') }}" class="p-5 space-y-4" enctype="multipart/form-data">
    @csrf
    <div class="grid grid-cols-3 gap-3">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Bank Soal</label><select name="question_bank_id" required class="w-full px-3 py-2.5 rounded-xl border text-xs focus:ring-2 focus:ring-accent-200">@foreach($banks as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">TP</label><select name="learning_objective_id" class="w-full px-3 py-2.5 rounded-xl border text-xs focus:ring-2 focus:ring-accent-200"><option value="">-</option>@foreach($learningObjectives as $lo)<option value="{{ $lo->id }}">{{ $lo->code }}</option>@endforeach</select></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Skor</label><input name="score" type="number" min="0" max="100" value="1" class="w-full px-3 py-2.5 rounded-xl border text-xs focus:ring-2 focus:ring-accent-200"></div>
    </div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Tipe</label><select name="type" id="qType" onchange="toggleOptions()" class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">
        <option value="pg">Pilihan Ganda</option>
        <option value="bs">Benar / Salah</option>
        <option value="jodoh">Menjodohkan</option>
        <option value="esai">Essay</option>
        <option value="audio">Audio</option>
    </select></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Pertanyaan</label><textarea name="content" rows="3" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></textarea></div>
    <div id="audioSection" style="display:none;">
        <label class="block text-xs font-semibold text-slate-500 mb-1.5">File Audio <span class="text-slate-400 font-normal">(MP3, WAV, OGG — maks 10MB)</span></label>
        <input type="file" name="audio_file" accept="audio/*" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-accent-200 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
        <p class="text-xs text-slate-400 mt-1">Upload file audio untuk soal listening</p>
    </div>
    <div id="optionsSection">
        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Opsi Jawaban</label>
        @foreach(['A','B','C','D','E'] as $l)
        <div class="option-row flex items-center gap-2 mb-2"><span class="text-xs font-bold w-5 text-slate-400">{{ $l }}</span><input name="opt_{{ $l }}" class="flex-1 px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-accent-200" placeholder="Opsi {{ $l }}"></div>
        @endforeach
        <div class="mt-2"><label class="block text-xs font-semibold text-slate-500 mb-1">Kunci Jawaban</label><select name="answer_key" class="px-3 py-2 rounded-lg border text-xs focus:ring-2 focus:ring-accent-200"><option value="">-</option>@foreach(['A','B','C','D','E'] as $l)<option value="{{ $l }}">{{ $l }}</option>@endforeach</select></div>
    </div>
    <input type="hidden" name="options" id="optionsJson" value="">
    <input type="hidden" name="difficulty" value="sedang">
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan</button><button type="button" onclick="closeMod()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600">Batal</button></div>
</form></div></div>

{{-- EDIT MODAL --}}
<div class="modal-overlay" id="editMod"><div class="modal-box"><div class="p-5 border-b flex justify-between"><h3 class="font-semibold">Edit Soal</h3><button onclick="closeEditMod()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button></div>
<form method="POST" id="editForm" class="p-5 space-y-4" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-3 gap-3">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Bank Soal</label><select name="question_bank_id" id="editBankId" required class="w-full px-3 py-2.5 rounded-xl border text-xs focus:ring-2 focus:ring-accent-200">@foreach($banks as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">TP</label><select name="learning_objective_id" id="editLoId" class="w-full px-3 py-2.5 rounded-xl border text-xs focus:ring-2 focus:ring-accent-200"><option value="">-</option>@foreach($learningObjectives as $lo)<option value="{{ $lo->id }}">{{ $lo->code }}</option>@endforeach</select></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Skor</label><input name="score" id="editScore" type="number" min="0" max="100" value="1" class="w-full px-3 py-2.5 rounded-xl border text-xs focus:ring-2 focus:ring-accent-200"></div>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Level Kognitif</label><select name="level_kognitif" id="editLevelKognitif" class="w-full px-3 py-2.5 rounded-xl border text-xs focus:ring-2 focus:ring-accent-200"><option value="">-</option><option value="L1">L1 - Pengetahuan</option><option value="L2">L2 - Aplikasi</option><option value="L3">L3 - Penalaran</option></select></div>
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Tingkat Kesulitan</label><select name="difficulty" id="editDifficulty" class="w-full px-3 py-2.5 rounded-xl border text-xs focus:ring-2 focus:ring-accent-200"><option value="mudah">Mudah</option><option value="sedang">Sedang</option><option value="sulit">Sulit</option></select></div>
    </div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Tipe</label><select name="type" id="editQType" onchange="toggleEditOptions()" class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200">
        <option value="pg">Pilihan Ganda</option>
        <option value="bs">Benar / Salah</option>
        <option value="jodoh">Menjodohkan</option>
        <option value="esai">Essay</option>
        <option value="audio">Audio</option>
    </select></div>
    <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Pertanyaan</label><textarea name="content" id="editContent" rows="3" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:ring-2 focus:ring-accent-200"></textarea></div>
    <div id="editAudioSection" style="display:none;">
        <label class="block text-xs font-semibold text-slate-500 mb-1.5">File Audio <span class="text-slate-400 font-normal">(MP3, WAV, OGG — maks 10MB)</span></label>
        <div id="editCurrentAudio" class="mb-2 text-xs text-slate-500" style="display:none;"></div>
        <input type="file" name="audio_file" accept="audio/*" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-accent-200 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
        <p class="text-xs text-slate-400 mt-1">Kosongkan jika tidak ingin mengganti audio</p>
    </div>
    <div id="editOptionsSection">
        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Opsi Jawaban</label>
        @foreach(['A','B','C','D','E'] as $l)
        <div class="option-row flex items-center gap-2 mb-2"><span class="text-xs font-bold w-5 text-slate-400">{{ $l }}</span><input name="edit_opt_{{ $l }}" id="editOpt{{ $l }}" class="flex-1 px-3 py-2 rounded-lg border border-slate-200 text-xs focus:ring-2 focus:ring-accent-200" placeholder="Opsi {{ $l }}"></div>
        @endforeach
        <div class="mt-2"><label class="block text-xs font-semibold text-slate-500 mb-1">Kunci Jawaban</label><select name="answer_key" id="editAnswerKey" class="px-3 py-2 rounded-lg border text-xs focus:ring-2 focus:ring-accent-200"><option value="">-</option>@foreach(['A','B','C','D','E'] as $l)<option value="{{ $l }}">{{ $l }}</option>@endforeach</select></div>
    </div>
    <input type="hidden" name="options" id="editOptionsJson" value="">
    <div class="flex gap-3 pt-2"><button class="flex-1 px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan Perubahan</button><button type="button" onclick="closeEditMod()" class="px-4 py-2.5 rounded-xl border text-sm text-slate-600">Batal</button></div>
</form></div></div>
@endsection

@push('scripts')
<script>
const questionData = @json($questionData);

// Accordion toggle: buka satu, tutup lainnya
function toggleGroup(btn) {
    var group = btn.parentElement;
    var body = group.querySelector('.group-body');
    var chevron = btn.querySelector('.chevron-icon');
    var isOpening = body.classList.contains('hidden');

    // Tutup semua grup lain
    document.querySelectorAll('.question-group').forEach(function(g) {
        if (g !== group) {
            g.querySelector('.group-body').classList.add('hidden');
            g.querySelector('.chevron-icon').classList.add('rotate-180');
        }
    });

    // Toggle grup yang diklik
    body.classList.toggle('hidden');
    chevron.classList.toggle('rotate-180');
}

function openModal(){document.getElementById('mod').classList.add('show')}
function closeMod(){document.getElementById('mod').classList.remove('show')}
function toggleOptions(){
    var t = document.getElementById('qType').value;
    var optSec = document.getElementById('optionsSection');
    var audSec = document.getElementById('audioSection');
    // Sembunyikan semua dulu
    optSec.style.display = 'none';
    audSec.style.display = 'none';

    if (t === 'audio') {
        audSec.style.display = 'block';
    } else if (t === 'esai') {
        // tanpa opsi & tanpa audio
    } else if (t === 'bs') {
        optSec.style.display = 'block';
        var fields = { opt_A: 'Benar', opt_B: 'Salah', opt_C: '', opt_D: '', opt_E: '' };
        for (var k in fields) {
            var inp = document.querySelector('[name="' + k + '"]');
            if (inp) { inp.value = fields[k]; inp.readOnly = true; inp.disabled = true; }
        }
    } else {
        optSec.style.display = 'block';
        document.querySelectorAll('#optionsSection input').forEach(function(i) {
            i.readOnly = false; i.disabled = false;
        });
    }
}
document.getElementById('mod').addEventListener('click',function(e){if(e.target===this)closeMod()})
document.querySelector('#mod form').addEventListener('submit',function(){
    var opts={};document.querySelectorAll('[name^="opt_"]').forEach(function(i){
        var k=i.name.replace('opt_','');if(i.value.trim())opts[k]=i.value.trim();
    });document.getElementById('optionsJson').value=JSON.stringify(opts);
})

// ─── EDIT MODAL ─────────────────────────────────────────────────
document.getElementById('editMod').addEventListener('click',function(e){if(e.target===this)closeEditMod()})
document.querySelector('#editMod form').addEventListener('submit',function(){
    var opts={};document.querySelectorAll('[name^="edit_opt_"]').forEach(function(i){
        var k=i.name.replace('edit_opt_','');if(i.value.trim())opts[k]=i.value.trim();
    });document.getElementById('editOptionsJson').value=JSON.stringify(opts);
})

function openEditModal(id) {
    var d = questionData[id];
    if (!d) return;
    var form = document.getElementById('editForm');
    form.action = '{{ route('exam.questions.update', '__ID__') }}'.replace('__ID__', id);

    document.getElementById('editBankId').value = d.question_bank_id;
    document.getElementById('editLoId').value = d.learning_objective_id || '';
    document.getElementById('editScore').value = d.score || 1;
    document.getElementById('editLevelKognitif').value = d.level_kognitif || '';
    document.getElementById('editDifficulty').value = d.difficulty || 'sedang';
    document.getElementById('editQType').value = d.type;
    document.getElementById('editContent').value = d.content;

    // Clear options
    ['A','B','C','D','E'].forEach(function(l){
        var el = document.getElementById('editOpt'+l);
        if (el) { el.value = ''; el.readOnly = false; el.disabled = false; }
    });
    document.getElementById('editAnswerKey').value = '';

    // Populate options
    if (d.options && typeof d.options === 'object') {
        Object.keys(d.options).forEach(function(k){
            var el = document.getElementById('editOpt'+k);
            if (el) el.value = d.options[k];
        });
    }
    if (d.answer_key) document.getElementById('editAnswerKey').value = d.answer_key;

    // Audio section
    var audSec = document.getElementById('editAudioSection');
    var curAud = document.getElementById('editCurrentAudio');
    if (d.type === 'audio') {
        audSec.style.display = 'block';
        if (d.has_audio) {
            curAud.style.display = 'block';
            curAud.innerHTML = '<audio controls class="h-7 w-full max-w-[200px]"><source src="'+d.audio_url+'" type="audio/mpeg"></audio><span class="text-slate-400">Audio saat ini</span>';
        } else {
            curAud.style.display = 'none';
        }
    } else {
        audSec.style.display = 'none';
    }

    toggleEditOptions();
    document.getElementById('editMod').classList.add('show');
}

function closeEditMod(){document.getElementById('editMod').classList.remove('show')}

function toggleEditOptions(){
    var t = document.getElementById('editQType').value;
    var optSec = document.getElementById('editOptionsSection');
    var audSec = document.getElementById('editAudioSection');
    optSec.style.display = 'none';
    audSec.style.display = 'none';

    if (t === 'audio') {
        audSec.style.display = 'block';
    } else if (t === 'esai') {
        // tanpa opsi & tanpa audio
    } else if (t === 'bs') {
        optSec.style.display = 'block';
        ['A','B','C','D','E'].forEach(function(l){
            var el = document.getElementById('editOpt'+l);
            if (!el) return;
            if (l === 'A') el.value = 'Benar';
            else if (l === 'B') el.value = 'Salah';
            else el.value = '';
            el.readOnly = true; el.disabled = true;
        });
    } else {
        optSec.style.display = 'block';
        document.querySelectorAll('#editOptionsSection input').forEach(function(i) {
            i.readOnly = false; i.disabled = false;
        });
    }
}
</script>
@endpush
