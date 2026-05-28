@extends('layouts.backend')

@section('title', 'Hasil Ujian')
@section('page_title', 'Hasil Ujian')

@push('styles')
<style>
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:none;align-items:center;justify-content:center;padding:20px}.modal-overlay.show{display:flex}.modal-box{background:#fff;border-radius:16px;width:100%;max-width:750px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,.25)}
.answer-correct{border-left:3px solid #10b981;background:#ecfdf5}.answer-wrong{border-left:3px solid #ef4444;background:#fef2f2}.answer-pending{border-left:3px solid #f59e0b;background:#fffbeb}
</style>
@endpush

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm flex items-center gap-2"><i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}</div>
@endif

<form method="GET" class="bg-white rounded-2xl border border-slate-100 p-5 mb-5">
    <div class="flex items-end gap-4">
        <div><label class="block text-xs font-semibold text-slate-500 mb-1.5">Pilih Ujian</label><select name="exam_id" onchange="this.form.submit()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-accent-200"><option value="">Pilih</option>@foreach($exams as $e)<option value="{{ $e->id }}" {{ $examId==$e->id?'selected':'' }}>{{ $e->code }} - {{ $e->title }}</option>@endforeach</select></div>
    </div>
</form>

@if($examId)
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden table-responsive">
    <div class="p-5 border-b flex justify-between items-center">
        <h3 class="font-semibold text-slate-800">Hasil Ujian</h3>
        <div class="flex gap-3 text-xs text-slate-400">
            <span>Total: {{ $results->count() }} peserta</span>
            @php
            $avg = $results->avg('total_score');
            $selectedExam = $exams->firstWhere('id', $examId);
            $kkm = $selectedExam->minimum_score ?? ($selectedExam->total_score * 0.7);
            $pass = $results->filter(fn($r)=>($r->total_score??0)>=$kkm)->count();
            @endphp
            <span>Rata²: {{ number_format($avg,1) }}</span>
            <span title="KKM: {{ number_format($kkm, 1) }}">Lulus: {{ $pass }}</span>
        </div>
    </div>
    <table class="w-full text-sm"><thead><tr class="bg-slate-50 text-left">
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">#</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Siswa</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">NIS</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-center">Benar</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-center">Nilai</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase text-center">Status</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase">Waktu</th>
        <th class="px-4 py-3 text-xs font-semibold text-slate-500 uppercase w-10">Aksi</th>
    </tr></thead>
    <tbody class="divide-y divide-slate-50">
    @forelse($results as $r)
    @php $sess = $sessions[$r->exam_session_id] ?? null; @endphp
    <tr class="hover:bg-slate-50/30">
        <td class="px-4 py-3 text-xs text-slate-400">{{ $loop->iteration }}</td>
        <td class="px-4 py-3 font-medium text-slate-800">{{ $r->student->nama_lengkap ?? '-' }}</td>
        <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $r->student->nis ?? '-' }}</td>
        <td class="px-4 py-3 text-center font-mono">{{ $r->correct_count ?? '-' }}/{{ $r->exam->total_questions ?? '-' }}</td>
        <td class="px-4 py-3 text-center">
            <span class="font-mono font-bold {{ ($r->total_score??0)>=$kkm?'text-emerald-600':'text-red-500' }}">{{ number_format($r->total_score??0,1) }}</span>
        </td>
        <td class="px-4 py-3 text-center">
            <span class="px-2 py-1 text-xs rounded-full {{ ($r->total_score??0)>=$kkm?'bg-emerald-50 text-emerald-600':'bg-red-50 text-red-600' }}">{{ ($r->total_score??0)>=$kkm?'Lulus':'Remidi' }}</span>
        </td>
        <td class="px-4 py-3 text-xs text-slate-400">{{ $sess ? \Carbon\Carbon::parse($sess->finished_at ?? $sess->updated_at)->format('d/m H:i') : '-' }}</td>
        <td class="px-4 py-3">
            <div class="flex items-center gap-1">
                <button onclick="openAnswerPreview('{{ $r->student->id }}')" class="p-1.5 rounded-lg hover:bg-purple-50 text-slate-400 hover:text-purple-600 transition" title="Lihat Jawaban"><i data-lucide="eye" class="w-3.5 h-3.5"></i></button>
                <button onclick="openGradingModal('{{ $r->student->id }}', '{{ $r->id }}')" class="p-1.5 rounded-lg hover:bg-amber-50 text-slate-400 hover:text-amber-600 transition" title="Koreksi Jawaban"><i data-lucide="check-check" class="w-3.5 h-3.5"></i></button>
                <form method="POST" action="{{ route('exam.results.delete', $r->id) }}" onsubmit="event.preventDefault(); showConfirm('Hapus hasil ujian {{ $r->student->nama_lengkap ?? 'siswa' }}? Data sesi & jawaban juga akan dihapus.', 'Hapus Hasil', 'Ya, Hapus', () => this.submit());" class="inline">@csrf @method('DELETE')<button class="p-1.5 rounded-lg hover:bg-red-50 text-slate-300 hover:text-red-500 transition" title="Hapus hasil"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button></form>
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="8" class="px-4 py-12 text-center text-slate-400">Belum ada peserta ujian.</td></tr>
    @endforelse
    </tbody></table>
</div>
@else
<div class="text-center py-16 bg-white rounded-2xl border border-slate-100"><i data-lucide="check-circle-2" class="w-12 h-12 mx-auto mb-3 text-slate-200"></i><p class="text-slate-400">Pilih ujian untuk melihat hasil.</p></div>
@endif

{{-- PREVIEW JAWABAN MODAL --}}
<div class="modal-overlay" id="answerPreviewMod"><div class="modal-box"><div class="p-5 border-b flex justify-between items-center sticky top-0 bg-white z-10">
    <h3 class="font-semibold flex items-center gap-2"><i data-lucide="file-text" class="w-4 h-4 text-purple-500"></i> <span id="apStudentName">Jawaban Siswa</span></h3>
    <button onclick="closeAnswerPreview()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button>
</div>
<div id="apContent" class="p-5 space-y-4"></div>
</div></div>

{{-- KOREKSI JAWABAN MODAL --}}
<div class="modal-overlay" id="gradingMod"><div class="modal-box"><div class="p-5 border-b flex justify-between items-center sticky top-0 bg-white z-10">
    <h3 class="font-semibold flex items-center gap-2"><i data-lucide="check-check" class="w-4 h-4 text-amber-500"></i> <span id="gmStudentName">Koreksi Jawaban</span></h3>
    <button onclick="closeGradingModal()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"><i data-lucide="x" class="w-5 h-5"></i></button>
</div>
<form method="POST" id="gradingForm" class="p-5 space-y-4">
    @csrf
    <div id="gmContent"></div>
    <div class="pt-3 border-t flex justify-between items-center">
        <span class="text-xs text-slate-400">Skor otomatis akan disimpan</span>
        <div class="flex gap-2">
            <button type="button" onclick="closeGradingModal()" class="px-4 py-2 rounded-xl border text-sm text-slate-600">Batal</button>
            <button type="submit" class="px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium">Simpan Koreksi</button>
        </div>
    </div>
</form>
</div></div>

@endsection

@push('scripts')
<script>
const sessionData = @json($sessionData);

function openAnswerPreview(studentId) {
    const data = sessionData[studentId];
    if (!data) { alert('Data jawaban tidak ditemukan.'); return; }

    // Cari nama siswa dari tabel
    const nameEl = document.querySelector(`[onclick*="${studentId}"]`);
    const row = nameEl?.closest('tr');
    const studentName = row?.querySelector('td:nth-child(2)')?.textContent?.trim() || 'Siswa';
    document.getElementById('apStudentName').textContent = 'Jawaban: ' + studentName;

    const answers = data.answers || [];
    let html = '';

    if (answers.length === 0) {
        html = '<div class="text-center py-8 text-slate-400">Tidak ada data jawaban.</div>';
    } else {
        html = answers.map((a, i) => {
            const typeLabel = a.type === 'pg' ? 'Pilihan Ganda' :
                a.type === 'bs' ? 'Benar/Salah' :
                a.type === 'jodoh' ? 'Menjodohkan' :
                a.type === 'esai' ? 'Esai' :
                a.type === 'audio' ? 'Audio' : (a.type || '-').toUpperCase();

            const statusClass = a.is_correct === true ? 'answer-correct' :
                a.is_correct === false ? 'answer-wrong' :
                'answer-pending';

            const statusLabel = a.is_correct === true ? '✅ Benar' :
                a.is_correct === false ? '❌ Salah' :
                '⏳ Belum Dinilai';

            // Jawaban siswa
            let studentAnswerHtml = '';
            if (a.type === 'pg') {
                const sel = a.selected_options?.[0] || '-';
                const opts = a.options || {};
                const optText = opts[sel] || sel;
                studentAnswerHtml = `<span class="font-mono font-bold">${sel}</span> — ${optText}`;
            } else if (a.type === 'bs') {
                studentAnswerHtml = a.selected_options?.[0] === 'benar' ? '✅ Benar' : (a.selected_options?.[0] === 'salah' ? '❌ Salah' : '<span class="text-slate-300">Belum dijawab</span>');
            } else if (a.type === 'jodoh') {
                const opts = a.options || {};
                const entries = Object.entries(opts);
                const selOpts = a.selected_options || [];
                studentAnswerHtml = '<div class="space-y-1">' + entries.map((e, j) => {
                    const leftLabel = String.fromCharCode(65 + j);
                    const sel = selOpts[j] || '-';
                    const rightText = entries.find(en => {
                        const rl = String.fromCharCode(65 + entries.indexOf(en));
                        return rl === sel;
                    })?.[1] || sel;
                    return `<div>${leftLabel}. ${e[0]} → <span class="font-mono">${rightText}</span></div>`;
                }).join('') + '</div>';
            } else {
                studentAnswerHtml = a.text_answer
                    ? `<div class="text-sm whitespace-pre-wrap">${a.text_answer}</div>`
                    : '<span class="text-slate-300 italic">Belum dijawab</span>';
            }

            // Kunci jawaban
            let keyHtml = '';
            if (a.type === 'pg') {
                const opts = a.options || {};
                keyHtml = `<span class="font-mono font-bold text-emerald-600">${a.answer_key || '-'}</span> — ${opts[a.answer_key] || '-'}`;
            } else if (a.type === 'bs') {
                keyHtml = a.answer_key === 'benar' ? 'Benar' : 'Salah';
            } else if (a.type === 'jodoh') {
                const opts = a.options || {};
                const entries = Object.entries(opts);
                keyHtml = '<div class="space-y-1">' + entries.map((e, j) => {
                    return `<div>${String.fromCharCode(65 + j)}. ${e[0]} → <span class="font-mono text-emerald-600">${e[1]}</span></div>`;
                }).join('') + '</div>';
            } else {
                keyHtml = a.answer_key ? `<div class="text-sm whitespace-pre-wrap text-slate-500">${a.answer_key}</div>` : '<span class="text-slate-300">-</span>';
            }

            return `<div class="${statusClass} rounded-xl p-4">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <span class="text-xs font-bold text-slate-400">Soal ${i + 1}</span>
                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600">${typeLabel}</span>
                        <span class="ml-2 text-xs text-slate-400">Bobot: ${a.score ?? '-'} poin</span>
                    </div>
                    <span class="text-xs font-medium">${statusLabel}</span>
                </div>
                <p class="text-sm text-slate-700 mb-3">${a.content || '-'}</p>
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <p class="font-semibold text-slate-500 uppercase mb-1">Jawaban Siswa</p>
                        ${studentAnswerHtml}
                    </div>
                    <div>
                        <p class="font-semibold text-slate-500 uppercase mb-1">Kunci Jawaban</p>
                        ${keyHtml}
                    </div>
                </div>
            </div>`;
        }).join('');
    }

    document.getElementById('apContent').innerHTML = html;
    document.getElementById('answerPreviewMod').classList.add('show');
    lucide.createIcons();
}

function closeAnswerPreview() {
    document.getElementById('answerPreviewMod').classList.remove('show');
}
document.getElementById('answerPreviewMod')?.addEventListener('click', function(e) {
    if (e.target === this) closeAnswerPreview();
});

// ─── GRADING MODAL ──────────────────────────────────────────
let gradingResultId = null;

function openGradingModal(studentId, resultId) {
    const data = sessionData[studentId];
    if (!data) { alert('Data jawaban tidak ditemukan.'); return; }

    gradingResultId = resultId;
    const nameEl = document.querySelector(`[onclick*="${studentId}"]`);
    const row = nameEl?.closest('tr');
    const studentName = row?.querySelector('td:nth-child(2)')?.textContent?.trim() || 'Siswa';
    document.getElementById('gmStudentName').textContent = 'Koreksi: ' + studentName;

    const form = document.getElementById('gradingForm');
    form.action = '/backend/exam/results/' + resultId + '/grade-answers';

    const answers = data.answers || [];
    // Filter: hanya esai dan jodoh yang perlu koreksi manual
    const gradable = answers.filter(a => a.type === 'esai' || a.type === 'jodoh');

    if (gradable.length === 0) {
        document.getElementById('gmContent').innerHTML = '<div class="text-center py-8 text-slate-400">Tidak ada soal yang perlu dikoreksi manual.</div>';
    } else {
        document.getElementById('gmContent').innerHTML = gradable.map((a, i) => {
            const typeLabel = a.type === 'jodoh' ? 'Menjodohkan' : 'Esai';
            const typeColor = a.type === 'jodoh' ? 'bg-amber-50 text-amber-600' : 'bg-purple-50 text-purple-600';
            const maxScore = a.score || 10;

            let answerDisplay = '';
            let correctDisplay = '';

            if (a.type === 'jodoh') {
                const opts = a.options || {};
                const entries = Object.entries(opts);
                const selOpts = a.selected_options || [];
                const allLabels = selOpts.length > 0 && selOpts.every(s => typeof s === 'string' && s.length === 1 && s >= 'A' && s <= 'E');

                if (allLabels) {
                    // Format label lama — tampilkan sebagai tabel perbandingan
                    answerDisplay = `<div class="p-3 bg-amber-50 rounded-lg border border-amber-100 text-xs">
                        <p class="font-semibold text-amber-700 mb-2">⚠️ Format jawaban lama (label) — bandingkan dengan kunci</p>
                        <table class="w-full border-collapse">
                            <thead><tr class="text-slate-400">
                                <th class="text-left py-1 px-2 w-6">#</th>
                                <th class="text-left py-1 px-2">Pernyataan</th>
                                <th class="text-left py-1 px-2 w-12">Pilih</th>
                                <th class="text-left py-1 px-2 w-12 text-amber-600">Label</th>
                            </tr></thead>
                            <tbody class="divide-y divide-amber-100">
                                ${entries.map((e, j) => `
                                    <tr>
                                        <td class="py-1.5 px-2 font-bold text-slate-500">${String.fromCharCode(65+j)}</td>
                                        <td class="py-1.5 px-2 text-slate-700">${e[0]}</td>
                                        <td class="py-1.5 px-2 text-slate-300">→</td>
                                        <td class="py-1.5 px-2"><span class="font-mono font-bold text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded">${selOpts[j] || '-'}</span></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>`;
                } else {
                    answerDisplay = `<div class="text-xs">
                        <table class="w-full border-collapse">
                            <thead><tr class="text-slate-400">
                                <th class="text-left py-1 px-2 w-6">#</th>
                                <th class="text-left py-1 px-2">Pernyataan</th>
                                <th class="text-left py-1 px-2 w-12"></th>
                                <th class="text-left py-1 px-2">Jawaban</th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                ${entries.map((e, j) => `
                                    <tr>
                                        <td class="py-1.5 px-2 font-bold text-slate-500">${String.fromCharCode(65+j)}</td>
                                        <td class="py-1.5 px-2 text-slate-700">${e[0]}</td>
                                        <td class="py-1.5 px-2 text-slate-300">→</td>
                                        <td class="py-1.5 px-2"><span class="font-medium text-emerald-700">${selOpts[j] || '-'}</span></td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>`;
                }
                correctDisplay = entries.map((e, j) => `
                    <tr>
                        <td class="py-1.5 px-2 font-bold text-slate-500">${String.fromCharCode(65+j)}</td>
                        <td class="py-1.5 px-2 text-slate-700">${e[0]}</td>
                        <td class="py-1.5 px-2 text-slate-300">→</td>
                        <td class="py-1.5 px-2"><span class="font-medium text-emerald-600">${e[1]}</span></td>
                    </tr>
                `).join('');
                correctDisplay = `<div class="text-xs"><table class="w-full border-collapse">
                    <thead><tr class="text-slate-400">
                        <th class="text-left py-1 px-2 w-6">#</th>
                        <th class="text-left py-1 px-2">Pernyataan</th>
                        <th class="text-left py-1 px-2 w-12"></th>
                        <th class="text-left py-1 px-2">Kunci</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">${correctDisplay}</tbody>
                </table></div>`;
            } else {
                answerDisplay = a.text_answer
                    ? `<div class="text-sm whitespace-pre-wrap bg-white p-3 rounded-lg border">${a.text_answer}</div>`
                    : '<span class="text-slate-300 italic">Tidak ada jawaban</span>';
                correctDisplay = a.answer_key
                    ? `<div class="text-sm whitespace-pre-wrap text-slate-500 bg-white p-3 rounded-lg border">${a.answer_key}</div>`
                    : '<span class="text-slate-300">-</span>';
            }

            return `<div class="border rounded-xl p-4 ${a.is_correct === true ? 'border-emerald-200 bg-emerald-50/30' : a.is_correct === false ? 'border-red-200 bg-red-50/30' : 'border-amber-200 bg-amber-50/30'}">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <span class="text-xs font-bold text-slate-400">Soal ${i+1}</span>
                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full ${typeColor}">${typeLabel}</span>
                    </div>
                    <span class="text-xs text-slate-400">Bobot: ${maxScore} poin</span>
                </div>
                <p class="text-sm text-slate-700 mb-3">${a.content || '-'}</p>
                <div class="grid grid-cols-2 gap-3 mb-3 text-xs">
                    <div>
                        <p class="font-semibold text-slate-500 uppercase mb-1">Jawaban Siswa</p>
                        ${answerDisplay}
                    </div>
                    <div>
                        <p class="font-semibold text-slate-500 uppercase mb-1">Kunci Jawaban</p>
                        ${correctDisplay}
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                    <label class="flex items-center gap-1.5 text-xs">
                        <input type="radio" name="scores[${a.answer_id}][is_correct]" value="1" ${a.is_correct === true ? 'checked' : ''} class="text-emerald-600"> Benar
                    </label>
                    <label class="flex items-center gap-1.5 text-xs">
                        <input type="radio" name="scores[${a.answer_id}][is_correct]" value="0" ${a.is_correct === false ? 'checked' : ''} class="text-red-600"> Salah
                    </label>
                    <label class="flex items-center gap-1.5 text-xs ml-auto">
                        Skor:
                        <input type="number" name="scores[${a.answer_id}][score]" value="${a.score ?? 0}" min="0" max="${maxScore}" step="0.5" class="w-16 px-2 py-1 text-xs border rounded-lg text-center">
                        <span class="text-slate-400">/ ${maxScore}</span>
                    </label>
                </div>
            </div>`;
        }).join('');
    }

    document.getElementById('gradingMod').classList.add('show');
    lucide.createIcons();
}

function closeGradingModal() {
    document.getElementById('gradingMod').classList.remove('show');
}
document.getElementById('gradingMod')?.addEventListener('click', function(e) {
    if (e.target === this) closeGradingModal();
});
</script>
@endpush
