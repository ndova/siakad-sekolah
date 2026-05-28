<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ujian — SIAKAD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #059669;
            --accent-light: #34d399;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f1f5f9; }
        .btn-accent { background: var(--accent); color: #fff; }
        .btn-accent:hover { filter: brightness(1.1); }
        .btn-outline { border: 1.5px solid #e2e8f0; color: #64748b; }
        .btn-outline:hover { background: #f8fafc; }
        .q-nav-btn {
            width: 36px; height: 36px;
            border-radius: 8px;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
        }
        .q-nav-btn:hover { border-color: var(--accent); color: var(--accent); }
        .q-nav-btn.active { background: var(--accent); color: #fff; border-color: var(--accent); }
        .q-nav-btn.answered { background: #dcfce7; border-color: #86efac; color: #166534; }
        .q-nav-btn.flagged::after {
            content: '';
            position: absolute;
            top: 2px; right: 2px;
            width: 8px; height: 8px;
            background: #f59e0b;
            border-radius: 50%;
        }
        .q-nav-btn.answered.flagged::after { background: #f59e0b; }
        .option-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.15s;
            text-align: left;
            width: 100%;
            font-family: inherit;
            font-size: 14px;
            background: #fff;
            color: #334155;
        }
        .option-btn:hover { border-color: #94a3b8; background: #f8fafc; }
        .option-btn.selected { border-color: var(--accent); background: #ecfdf5; }
        .option-indicator {
            width: 22px; height: 22px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
        }
        .selected .option-indicator { border-color: var(--accent); background: var(--accent); color: #fff; }
        .checkbox-indicator {
            width: 22px; height: 22px;
            border-radius: 6px;
            border: 2px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .selected .checkbox-indicator { border-color: var(--accent); background: var(--accent); }
        .selected .checkbox-indicator::after { content: '✓'; color: #fff; font-size: 12px; font-weight: 700; }
        #timerDisplay { font-variant-numeric: tabular-nums; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        #confirmModal { display: none; }
        #confirmModal.show { display: flex; }
    </style>
</head>
<body class="h-screen flex flex-col">

    {{-- Top Bar --}}
    <header class="bg-white border-b border-slate-200 px-6 py-3 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-4">
            <h1 class="text-base font-semibold text-slate-800" id="examTitle">Memuat ujian...</h1>
            <span class="text-xs text-slate-400" id="examSubject"></span>
        </div>
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-3 text-xs text-slate-500">
                <span id="answeredInfo">0/0 dijawab</span>
                <span class="text-slate-200">|</span>
                <span class="flex items-center gap-1" id="flaggedInfo" style="display:none;">🚩 <span id="flaggedCount">0</span> ditandai</span>
            </div>
            <div class="flex items-center gap-2" id="timerContainer">
                <i data-lucide="clock" class="w-4 h-4 text-slate-400"></i>
                <span id="timerDisplay" class="text-lg font-bold text-slate-700">--:--</span>
            </div>
            <button onclick="confirmFinish()" class="btn-accent px-5 py-2 rounded-lg text-sm font-semibold flex items-center gap-1.5">
                <i data-lucide="check-circle" class="w-4 h-4"></i> Selesai
            </button>
        </div>
    </header>

    {{-- Main Content --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- Question Navigator Sidebar --}}
        <aside class="w-72 bg-white border-r border-slate-200 flex flex-col shrink-0">
            <div class="p-4 border-b border-slate-100">
                <h2 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Navigasi Soal</h2>
                <div class="flex gap-2 text-xs text-slate-500 mb-2">
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-white border border-slate-300 inline-block"></span> Belum</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-200 border border-green-400 inline-block"></span> Sudah</span>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <div class="grid grid-cols-5 gap-2" id="questionGrid"></div>
            </div>
        </aside>

        {{-- Question Area --}}
        <main class="flex-1 overflow-y-auto p-6 flex flex-col">
            {{-- Loading --}}
            <div id="loadingState" class="flex-1 flex items-center justify-center">
                <div class="text-center">
                    <div class="animate-spin w-10 h-10 border-3 border-slate-200 border-t-accent rounded-full mx-auto mb-4"></div>
                    <p class="text-slate-500 font-medium">Memuat soal ujian...</p>
                    <p class="text-sm text-slate-400 mt-1">Mohon tunggu sebentar</p>
                </div>
            </div>

            {{-- Question Content --}}
            <div id="questionArea" style="display:none;" class="flex-1 flex flex-col">
                <div class="mb-4">
                    <span id="questionNumber" class="text-xs font-semibold text-slate-400">Soal 1</span>
                    <span id="questionType" class="ml-2 px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600"></span>
                    <span id="questionScore" class="ml-2 text-xs text-slate-400"></span>
                </div>
                <div id="questionContent" class="text-slate-800 leading-relaxed text-[15px] mb-6"></div>
                <div id="optionsContainer" class="space-y-3 mb-6"></div>
                <div id="essayContainer" style="display:none;" class="mb-6">
                    <textarea id="essayInput" rows="6" class="w-full border border-slate-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent resize-none" placeholder="Tulis jawaban Anda di sini..."></textarea>
                </div>

                {{-- Navigation --}}
                <div class="flex items-center justify-between mt-auto pt-4 border-t border-slate-100">
                    <button id="flagBtn" onclick="toggleFlag()" class="flex items-center gap-1.5 text-sm text-slate-500 hover:text-amber-500 transition-colors bg-transparent border-none cursor-pointer font-medium">
                        🚩 <span id="flagLabel">Tandai</span>
                    </button>
                    <div class="flex gap-3">
                        <button id="prevBtn" onclick="navigateQuestion(-1)" class="btn-outline px-5 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-1.5">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i> Sebelumnya
                        </button>
                        <button id="nextBtn" onclick="navigateQuestion(1)" class="btn-outline px-5 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-1.5">
                            Selanjutnya <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Error State --}}
            <div id="errorState" style="display:none;" class="flex-1 flex items-center justify-center text-center">
                <div>
                    <p class="text-4xl mb-3">😞</p>
                    <p class="text-slate-700 font-semibold" id="errorMessage">Gagal memuat ujian</p>
                    <a href="/portal/siswa/exams" class="text-sm text-accent font-medium hover:underline mt-2 inline-block">← Kembali ke daftar ujian</a>
                </div>
            </div>
        </main>
    </div>

    {{-- Overlay Mulai Ujian (wajib klik user untuk fullscreen) --}}
    <div id="startOverlay" class="fixed inset-0 z-50 bg-white flex items-center justify-center" style="display:none;">
        <div class="text-center px-6 max-w-md">
            <div class="text-6xl mb-6">📝</div>
            <h2 class="text-xl font-bold text-slate-800 mb-3" id="startExamTitle">Ujian Siap Dimulai</h2>
            <p class="text-sm text-slate-500 mb-2">Ujian akan berjalan dalam mode <strong>layar penuh</strong> untuk keamanan.</p>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-6 text-xs text-amber-700 text-left">
                <p class="font-semibold mb-1">⚠️ Aturan Ujian:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    <li>Jangan keluar dari mode layar penuh</li>
                    <li>Jangan membuka tab atau aplikasi lain</li>
                    <li>Tombol Esc, Ctrl, Alt, Tab, dan Windows dinonaktifkan</li>
                    <li>Pelanggaran akan menutup ujian otomatis</li>
                </ul>
            </div>
            <button onclick="beginExam()" id="beginExamBtn" class="btn-accent w-full py-3.5 rounded-xl text-base font-bold flex items-center justify-center gap-2 hover:brightness-110 transition">
                <i data-lucide="play" class="w-5 h-5"></i> Mulai Ujian
            </button>
            <p class="text-xs text-slate-400 mt-3">Klik tombol di atas untuk masuk mode layar penuh</p>
        </div>
    </div>

    {{-- Confirm Finish Modal --}}
    <div id="confirmModal" class="fixed inset-0 z-50 bg-black/50 items-center justify-center">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md mx-4 shadow-xl">
            <h3 class="text-lg font-bold text-slate-800 mb-2">Selesaikan Ujian?</h3>
            <p class="text-sm text-slate-500 mb-1" id="confirmAnsweredInfo">Anda telah menjawab 0 dari 0 soal.</p>
            <p class="text-sm text-slate-500 mb-6">Setelah dikumpulkan, jawaban tidak dapat diubah lagi.</p>
            <div class="flex gap-3">
                <button onclick="closeConfirmModal()" class="flex-1 btn-outline py-2.5 rounded-lg text-sm font-semibold">Batal</button>
                <button onclick="doFinish()" id="finishBtn" class="flex-1 btn-accent py-2.5 rounded-lg text-sm font-semibold">Ya, Kumpulkan</button>
            </div>
        </div>
    </div>

    {{-- Result Modal (shown after finish) --}}
    <div id="resultModal" class="fixed inset-0 z-50 bg-black/50 items-center justify-center" style="display:none;">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md mx-4 shadow-xl text-center">
            <p class="text-5xl mb-3">🎉</p>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Ujian Selesai!</h3>
            <p class="text-sm text-slate-500 mb-4">Jawaban Anda telah dikumpulkan.</p>
            <div id="resultScore" class="mb-4"></div>
            <a href="/portal/siswa/exams" class="btn-accent w-full py-2.5 rounded-lg text-sm font-semibold inline-block">Kembali ke Daftar Ujian</a>
        </div>
    </div>

    <script>
        const API_BASE = '/api/v1';
        const pathParts = window.location.pathname.split('/');
        // Gunakan examId dari server jika tersedia, jika tidak parse dari URL
        const examId = "{{ $examId ?? '' }}" || pathParts[pathParts.length - 2];
        const currentToken = localStorage.getItem('sia_token');

        let examData = null;
        let questions = [];
        let currentIndex = 0;
        let answers = {}; // key: exam_question_id, value: { selected_options, text_answer }
        let flaggedQuestions = new Set();
        let sessionEndTime = null; // ISO timestamp — absolute waktu habis dari server
        let serverTimeOffset = 0;   // selisih client vs server (detik)
        let remainingSeconds = 0;
        let timerInterval = null;
        let isSubmitting = false;
        let isSecureMode = false; // mode keamanan ujian aktif

        // ─── ENTER SECURE EXAM MODE ──────────────────────────
        function enterSecureMode() {
            // Hanya skip jika SUDAH dalam fullscreen aktif
            if (document.fullscreenElement || document.webkitFullscreenElement) return;
            var el = document.documentElement;
            var promise = null;
            if (el.requestFullscreen) {
                promise = el.requestFullscreen();
            } else if (el.webkitRequestFullscreen) {
                el.webkitRequestFullscreen();
            } else if (el.msRequestFullscreen) {
                el.msRequestFullscreen();
            }
            if (promise) {
                promise.catch(function(err) {
                    console.warn('Fullscreen gagal:', err);
                    alert('Gagal masuk mode layar penuh. Periksa pengaturan browser Anda. Klik OK untuk mencoba lagi.');
                    isSecureMode = true;
                });
            }
        }

        function showStartOverlay() {
            document.getElementById('startOverlay').style.display = 'flex';
            document.getElementById('startExamTitle').textContent = examData ? 'Ujian: ' + examData.title : 'Ujian Siap Dimulai';
            lucide.createIcons();
        }

        function beginExam() {
            // Klik user = valid gesture untuk requestFullscreen
            enterSecureMode();
            // Overlay akan disembunyikan otomatis oleh onFullscreenChange saat fullscreen aktif
            // Fallback: jika fullscreen gagal/tidak didukung, sembunyikan overlay setelah 2 detik
            setTimeout(function() {
                var overlay = document.getElementById('startOverlay');
                if (overlay && overlay.style.display !== 'none') {
                    overlay.style.display = 'none';
                    isSecureMode = true; // tetap aktifkan mode aman meski tanpa fullscreen
                }
            }, 2000);
        }

        function exitSecureMode() {
            isSecureMode = false;
            if (document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
                if (document.exitFullscreen) {
                    document.exitFullscreen().catch(function(){});
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
            }
        }

        // ─── BLOCK DANGEROUS KEYS ────────────────────────────
        function blockKeys(e) {
            if (!isSecureMode) return;
            // Deteksi key secara akurat
            var key = e.key || '';
            var code = e.code || '';
            // Block: Escape, Ctrl, Alt, Meta (Windows), Tab, F1-F12 (except F5)
            var blocked = (
                key === 'Escape' ||
                key === 'Tab' ||
                key === 'Control' || key === 'Alt' || key === 'Meta' ||
                code === 'ControlLeft' || code === 'ControlRight' ||
                code === 'AltLeft' || code === 'AltRight' ||
                code === 'MetaLeft' || code === 'MetaRight' ||
                code === 'OSLeft' || code === 'OSRight' ||
                e.ctrlKey || e.altKey || e.metaKey ||
                (e.keyCode >= 112 && e.keyCode <= 123 && e.keyCode !== 116) || // F1-F12 kecuali F5
                (e.keyCode === 9) // Tab keycode fallback
            );
            if (blocked) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                return false;
            }
            // Allow: arrow keys, F for flag, space, enter
            return true;
        }

        function onFullscreenChange() {
            var isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement);
            if (isFullscreen) {
                // Fullscreen aktif — mode aman ON
                isSecureMode = true;
                document.getElementById('startOverlay').style.display = 'none';
                var warn = document.getElementById('securityWarning');
                if (warn && warn.parentNode) warn.parentNode.removeChild(warn);
                if (window._secInterval) { clearInterval(window._secInterval); window._secInterval = null; }
                var flash = document.getElementById('fsFlash');
                if (flash && flash.parentNode) flash.parentNode.removeChild(flash);
            } else {
                // User keluar fullscreen (Esc/F11) — reset flag agar bisa re-enter
                isSecureMode = false;
                if (!isSubmitting) {
                    showSecurityWarning();
                }
            }
        }

        function showSecurityWarning() {
            // Hapus warning lama jika ada
            var existing = document.getElementById('securityWarning');
            if (existing && existing.parentNode) existing.parentNode.removeChild(existing);
            if (window._secInterval) { clearInterval(window._secInterval); window._secInterval = null; }

            var div = document.createElement('div');
            div.id = 'securityWarning';
            div.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.92);display:flex;align-items:center;justify-content:center;flex-direction:column;color:white;text-align:center;padding:20px;';
            div.innerHTML = '<div style="font-size:64px;margin-bottom:16px;">⚠️</div>' +
                '<h2 style="font-size:24px;font-weight:700;margin-bottom:8px;">Mode Layar Penuh Diperlukan!</h2>' +
                '<p style="font-size:14px;margin-bottom:4px;max-width:420px;">Anda keluar dari mode layar penuh. Ujian harus dikerjakan dalam mode fullscreen.</p>' +
                '<p style="font-size:14px;color:#f59e0b;margin-bottom:20px;">Ujian akan <strong>ditutup otomatis</strong> dalam <strong id="secCountdown" style="font-size:20px;">10</strong> detik.</p>' +
                '<button onclick="reEnterFullscreen()" style="padding:14px 40px;background:#059669;color:white;border:none;border-radius:14px;font-size:17px;font-weight:700;cursor:pointer;box-shadow:0 4px 15px rgba(5,150,105,0.5);">🔓 Kembali ke Fullscreen</button>';
            document.body.appendChild(div);

            var seconds = 10;
            var interval = setInterval(function() {
                seconds--;
                var cd = document.getElementById('secCountdown');
                if (cd) cd.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(interval);
                    window._secInterval = null;
                    if (div.parentNode) div.parentNode.removeChild(div);
                    doFinishUnsafe();
                }
            }, 1000);

            window._secInterval = interval;
        }

        function reEnterFullscreen() {
            var div = document.getElementById('securityWarning');
            if (div && div.parentNode) div.parentNode.removeChild(div);
            if (window._secInterval) { clearInterval(window._secInterval); window._secInterval = null; }
            enterSecureMode(); // Valid karena dipicu oleh klik user
        }

        function doFinishUnsafe() {
            // Finish without confirmation — called on security violation
            if (isSubmitting) return;
            isSubmitting = true;
            clearInterval(timerInterval);
            syncTime().then(function() {
                apiFetch(API_BASE + '/student/exam/' + examId + '/finish', { method: 'POST' })
                    .then(function(r) { return r?.json(); })
                    .then(function(data) {
                        exitSecureMode();
                        var rm = document.getElementById('resultModal');
                        if (rm) {
                            rm.style.display = 'flex';
                            document.getElementById('resultScore').innerHTML = '<p style="font-size:16px;color:#64748b;margin:20px;">Ujian telah ditutup karena pelanggaran keamanan.</p>';
                        }
                    })
                    .catch(function() {
                        window.location.href = '/portal/siswa/exams';
                    });
            });
        }

        async function apiFetch(url, opts = {}) {
            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                ...(opts.headers || {})
            };
            if (currentToken) headers['Authorization'] = `Bearer ${currentToken}`;
            const res = await fetch(url, { ...opts, headers });
            if (res.status === 401) { localStorage.clear(); window.location.href = '/portal/siswa/login'; return null; }
            return res;
        }

        function formatTime(seconds) {
            if (seconds <= 0) return '00:00';
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        }

        // ─── Init ──────────────────────────────────────────────
        async function init() {
            try {
                // Sinkronkan waktu server dulu
                await syncServerTime();

                // Mulai sesi ujian
                const startRes = await apiFetch(`${API_BASE}/student/exam/${examId}/start`, { method: 'POST' });
                if (!startRes || !startRes.ok) {
                    const err = await startRes?.json().catch(() => ({}));
                    throw new Error(err.message || 'Gagal memulai ujian');
                }
                const startData = await startRes.json();
                if (!startData.success) throw new Error(startData.message);

                // Ambil soal
                const qRes = await apiFetch(`${API_BASE}/student/exam/${examId}/questions`);
                if (!qRes || !qRes.ok) {
                    const err = await qRes?.json().catch(() => ({}));
                    throw new Error(err.message || 'Gagal memuat soal ujian');
                }
                const qData = await qRes.json();
                if (!qData.success) throw new Error(qData.message);

                examData = qData.data.exam;
                questions = qData.data.questions || [];

                // Validasi soal tersedia
                if (!questions.length) {
                    throw new Error('Ujian ini belum memiliki soal. Hubungi guru Anda.');
                }

                // Timer berbasis absolute end_time dari server
                if (qData.data.session?.end_time) {
                    sessionEndTime = new Date(qData.data.session.end_time).getTime();
                } else if (qData.data.session?.remaining_seconds !== undefined) {
                    sessionEndTime = getServerNow() + (qData.data.session.remaining_seconds * 1000);
                }
                remainingSeconds = calcRemaining();

                // Inisialisasi jawaban dari yang sudah ada
                questions.forEach(q => {
                    if (q.my_answer?.selected_options || q.my_answer?.text_answer) {
                        answers[q.id] = {
                            selected_options: q.my_answer.selected_options,
                            text_answer: q.my_answer.text_answer
                        };
                    }
                });

                // Render
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('questionArea').style.display = 'flex';
                document.getElementById('examTitle').textContent = examData.title;
                document.getElementById('examSubject').textContent = examData.subject || '';

                renderQuestionGrid();
                showQuestion(0);
                startTimer();
                updateAnswerInfo();

                // Tampilkan overlay "Mulai Ujian" — wajib klik untuk fullscreen
                showStartOverlay();

                lucide.createIcons();
            } catch (e) {
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('errorState').style.display = 'flex';
                document.getElementById('errorMessage').textContent = e.message;
            }
        }

        // ─── Question Grid ─────────────────────────────────────
        function renderQuestionGrid() {
            const grid = document.getElementById('questionGrid');
            grid.innerHTML = questions.map((q, i) => {
                const hasAnswer = !!answers[q.id];
                const isFlagged = flaggedQuestions.has(q.id);
                const isActive = i === currentIndex;
                let cls = 'q-nav-btn';
                if (isActive) cls += ' active';
                else if (hasAnswer) cls += ' answered';
                return `<button onclick="navigateTo(${i})" class="${cls}" style="position:relative;" id="navBtn${i}">
                    ${i + 1}
                    ${isFlagged ? '<span style="position:absolute;top:2px;right:2px;width:8px;height:8px;background:#f59e0b;border-radius:50%;"></span>' : ''}
                </button>`;
            }).join('');
        }

        function updateNavHighlight() {
            document.querySelectorAll('.q-nav-btn').forEach((btn, i) => {
                btn.classList.remove('active');
                if (i === currentIndex) btn.classList.add('active');
            });
        }

        function updateAnswerInfo() {
            const answered = Object.keys(answers).length;
            document.getElementById('answeredInfo').textContent = `${answered}/${questions.length} dijawab`;
            document.getElementById('confirmAnsweredInfo').textContent = `Anda telah menjawab ${answered} dari ${questions.length} soal.`;
        }

        // Helper: konversi options (array/object) ke array
        function optsToArray(opts) {
            if (!opts) return [];
            if (Array.isArray(opts)) {
                // Jika array of objects dengan key "left"/"right", itu format jodoh khusus
                if (opts.length > 0 && typeof opts[0] === 'object' && opts[0].left !== undefined) {
                    return opts;
                }
                return opts;
            }
            // Object → array (preserve order)
            return Object.values(opts);
        }

        // ─── Show Question ─────────────────────────────────────
        function showQuestion(index) {
            if (index < 0 || index >= questions.length) return;
            currentIndex = index;
            const q = questions[index];

            document.getElementById('questionNumber').textContent = `Soal ${index + 1} dari ${questions.length}`;
            document.getElementById('questionType').textContent = q.type === 'pg' ? 'Pilihan Ganda' : q.type === 'bs' ? 'Benar/Salah' : q.type === 'jodoh' ? 'Menjodohkan' : q.type === 'esai' ? 'Esai' : q.type === 'audio' ? 'Audio' : q.type.toUpperCase();
            document.getElementById('questionScore').textContent = `Bobot: ${q.score} poin`;
            document.getElementById('questionContent').innerHTML = q.content.replace(/\n/g, '<br>');

            // Options container
            const optContainer = document.getElementById('optionsContainer');
            const essayContainer = document.getElementById('essayContainer');

            if (q.type === 'pg') {
                essayContainer.style.display = 'none';
                optContainer.style.display = 'block';
                const currentAnswer = answers[q.id];
                const selected = currentAnswer?.selected_options?.[0] || null;
                const labels = ['A', 'B', 'C', 'D', 'E'];
                optContainer.innerHTML = optsToArray(q.options).map((opt, i) => {
                    const isSelected = selected === labels[i];
                    return `<button onclick="selectOption('${q.id}', '${labels[i]}')" class="option-btn ${isSelected ? 'selected' : ''}" id="opt_${q.id}_${labels[i]}">
                        <div class="option-indicator">${labels[i]}</div>
                        <span>${opt}</span>
                    </button>`;
                }).join('');
            } else if (q.type === 'bs') {
                essayContainer.style.display = 'none';
                optContainer.style.display = 'block';
                const currentAnswer = answers[q.id];
                const selected = currentAnswer?.selected_options?.[0] || null;
                const bsOptions = ['Benar', 'Salah'];
                const bsValues = ['benar', 'salah'];
                optContainer.innerHTML = bsOptions.map((opt, i) => {
                    const isSelected = selected === bsValues[i];
                    return `<button onclick="selectOption('${q.id}', '${bsValues[i]}')" class="option-btn ${isSelected ? 'selected' : ''}" id="opt_${q.id}_${bsValues[i]}">
                        <div class="option-indicator">${['B', 'S'][i]}</div>
                        <span>${opt}</span>
                    </button>`;
                }).join('');
            } else if (q.type === 'jodoh') {
                essayContainer.style.display = 'none';
                optContainer.style.display = 'block';
                const currentAnswer = answers[q.id];
                const selectedPairs = currentAnswer?.selected_options || [];
                // options is object: {left1:right1, left2:right2, ...}
                const entries = Object.entries(q.options || {});
                // Left items dengan label A, B, C...
                const leftItems = entries.map(([left, right], i) => ({
                    label: String.fromCharCode(65 + i),
                    text: left
                }));
                // Right items (pasangan) — semua teks kanan dikumpulkan lalu di-shuffle
                const rightItems = entries.map(([left, right], i) => ({
                    label: String.fromCharCode(65 + i),
                    text: right
                }));
                const shuffledRight = [...rightItems].sort(() => Math.random() - 0.5);

                // Render dua kolom: kiri label A-E, kanan pilihan jawaban
                optContainer.innerHTML = `
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <p class="text-xs font-semibold text-slate-400 uppercase mb-2">Pernyataan</p>
                            ${leftItems.map(item => `
                                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg border border-slate-100">
                                    <span class="w-7 h-7 rounded-full bg-accent-100 text-accent-700 flex items-center justify-center text-xs font-bold shrink-0">${item.label}</span>
                                    <span class="text-sm text-slate-700 font-medium">${item.text}</span>
                                </div>
                            `).join('')}
                        </div>
                        <div class="space-y-2">
                            <p class="text-xs font-semibold text-slate-400 uppercase mb-2">Jawaban</p>
                            ${shuffledRight.map(rItem => `
                                <div class="flex items-center gap-3 p-3 bg-white rounded-lg border border-slate-200 hover:border-accent-300 transition-colors cursor-pointer select-none"
                                     onclick="toggleJodohRight('${q.id}', '${rItem.label}', \`${rItem.text.replace(/`/g,'\\`')}\`)"
                                     id="jodohRight_${q.id}_${rItem.label}">
                                    <span class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-xs font-bold shrink-0">${rItem.label}</span>
                                    <span class="text-sm text-slate-600">${rItem.text}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="mt-4 p-3 bg-accent-50 rounded-lg border border-accent-100" id="jodohSummary_${q.id}">
                        <p class="text-xs font-semibold text-accent-700 mb-2">Pasangan Anda:</p>
                        <div id="jodohPairs_${q.id}" class="space-y-1 text-xs text-slate-600">
                            ${leftItems.map((item, i) => {
                                const sel = selectedPairs[i] || '';
                                const matchText = sel ? (rightItems.find(r => r.label === sel)?.text || sel) : '...';
                                return `<div class="flex items-center gap-1"><span class="font-bold text-accent-600">${item.label}</span> → <span class="${sel ? 'text-emerald-600 font-medium' : 'text-slate-300'}">${matchText}</span></div>`;
                            }).join('')}
                        </div>
                    </div>
                `;
            } else if (q.type === 'audio') {
                essayContainer.style.display = 'none';
                optContainer.style.display = 'block';
                const currentAnswer = answers[q.id];
                const selected = currentAnswer?.selected_options?.[0] || null;
                optContainer.innerHTML = `
                    <div class="mb-4">
                        <audio controls class="w-full" id="audioPlayer_${q.id}">
                            <source src="${q.media?.audio || ''}" type="audio/mpeg">
                            Browser Anda tidak mendukung pemutar audio.
                        </audio>
                    </div>
                ` + optsToArray(q.options).map((opt, i) => {
                    const label = String.fromCharCode(65 + i);
                    const isSelected = selected === label;
                    return `<button onclick="selectOption('${q.id}', '${label}')" class="option-btn ${isSelected ? 'selected' : ''}" id="opt_${q.id}_${label}">
                        <div class="option-indicator">${label}</div>
                        <span>${opt}</span>
                    </button>`;
                }).join('');
            } else {
                optContainer.style.display = 'none';
                essayContainer.style.display = 'block';
                const currentAnswer = answers[q.id];
                document.getElementById('essayInput').value = currentAnswer?.text_answer || '';
                document.getElementById('essayInput').dataset.questionId = q.id;
            }

            // Flag button state
            updateFlagButton();
            updateNavHighlight();

            // Nav buttons
            document.getElementById('prevBtn').style.visibility = index === 0 ? 'hidden' : 'visible';
            document.getElementById('nextBtn').style.visibility = index === questions.length - 1 ? 'hidden' : 'visible';
        }

        // ─── Select Option (PG / BS / Audio) ─────────────────
        async function selectOption(questionId, value) {
            answers[questionId] = { selected_options: [value], text_answer: null };

            // Update UI
            const q = questions[currentIndex];
            if (q.id === questionId) {
                document.querySelectorAll('#optionsContainer .option-btn').forEach(b => b.classList.remove('selected'));
                const targetBtn = document.getElementById(`opt_${questionId}_${value}`);
                if (targetBtn) targetBtn.classList.add('selected');
            }

            // Auto-save
            await saveAnswer(questionId);
            updateAnswerInfo();
            refreshGridButton(questionId);
        }

        // ─── Select Menjodohkan ──────────────────────────────
        // State: index item kiri mana yang sedang dipilih (0-4)
        let jodohActiveLeft = {};

        async function toggleJodohRight(questionId, rightLabel, rightText) {
            const q = questions[currentIndex];
            if (q.id !== questionId) return;

            // Cari item kiri yang sedang aktif untuk soal ini
            const activeIdx = jodohActiveLeft[questionId];
            if (activeIdx === undefined) {
                return;
            }

            // Simpan pasangan — simpan TEKS jawaban (bukan label), untuk auto-koreksi server
            if (!answers[questionId]) {
                answers[questionId] = { selected_options: [], text_answer: null };
            }
            answers[questionId].selected_options[activeIdx] = rightText || rightLabel;

            // Update visual: highlight kanan yang dipilih, reset semua kiri
            const allRight = document.querySelectorAll(`[id^="jodohRight_${questionId}_"]`);
            allRight.forEach(el => {
                el.classList.remove('border-accent-500', 'bg-accent-50');
                if (el.id === `jodohRight_${questionId}_${rightLabel}`) {
                    el.classList.add('border-accent-500', 'bg-accent-50');
                }
            });

            // Reset semua kiri
            const leftItems = document.querySelectorAll(`#optionsContainer [class*="rounded-full"][class*="bg-accent"]`);
            document.querySelectorAll(`#optionsContainer .grid > div:first-child > div`).forEach(el => {
                el.classList.remove('ring-2', 'ring-accent-400');
            });

            // Reset jodohActiveLeft
            jodohActiveLeft[questionId] = undefined;

            // Update ringkasan
            updateJodohSummary(questionId);

            await saveAnswer(questionId);
            updateAnswerInfo();
            refreshGridButton(questionId);

            // Setelah 300ms, enable klik kiri lagi
            setTimeout(() => {
                jodohActiveLeft[questionId] = undefined;
                document.querySelectorAll(`#optionsContainer .grid > div:first-child > div`).forEach(el => {
                    el.style.pointerEvents = 'auto';
                    el.style.cursor = 'pointer';
                });
                document.querySelectorAll(`[id^="jodohRight_${questionId}_"]`).forEach(el => {
                    el.classList.remove('border-accent-500', 'bg-accent-50');
                });
            }, 300);
        }

        function updateJodohSummary(questionId) {
            const summaryDiv = document.getElementById(`jodohPairs_${questionId}`);
            if (!summaryDiv) return;
            const currentAnswer = answers[questionId];
            const selectedPairs = currentAnswer?.selected_options || [];
            const entries = Object.entries(questions[currentIndex]?.options || {});

            summaryDiv.innerHTML = entries.map(([left, right], i) => {
                const label = String.fromCharCode(65 + i);
                const sel = selectedPairs[i] || '';
                return `<div class="flex items-center gap-1"><span class="font-bold text-accent-600">${label}</span> → <span class="${sel ? 'text-emerald-600 font-medium' : 'text-slate-300'}">${sel || '...'}</span></div>`;
            }).join('');
        }

        // Klik item kiri untuk memilih pasangan
        document.addEventListener('click', function(e) {
            const leftItem = e.target.closest('[id]') || e.target;
            // Deteksi klik pada item kiri (dalam grid kolom pertama)
            const gridCol = e.target.closest('.grid > div:first-child > div');
            if (gridCol && gridCol.querySelector('[class*="rounded-full"]')) {
                const q = questions[currentIndex];
                if (!q || q.type !== 'jodoh') return;
                const label = gridCol.querySelector('[class*="rounded-full"]').textContent.trim();
                const idx = label.charCodeAt(0) - 65;
                if (idx < 0 || idx > 4) return;

                // Highlight item kiri yang dipilih
                document.querySelectorAll(`#optionsContainer .grid > div:first-child > div`).forEach(el => {
                    el.classList.remove('ring-2', 'ring-accent-400');
                });
                gridCol.classList.add('ring-2', 'ring-accent-400');

                // Set active left
                jodohActiveLeft[q.id] = idx;

                // Highlight right items as clickable
                document.querySelectorAll(`[id^="jodohRight_${q.id}_"]`).forEach(el => {
                    el.style.cursor = 'pointer';
                    el.style.opacity = '1';
                });
            }
        });

        // ─── Save Essay ───────────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            const essayInput = document.getElementById('essayInput');
            if (essayInput) {
                let essayTimer = null;
                essayInput.addEventListener('input', function() {
                    const qId = this.dataset.questionId;
                    if (!qId) return;
                    answers[qId] = { selected_options: null, text_answer: this.value };
                    clearTimeout(essayTimer);
                    essayTimer = setTimeout(async () => {
                        await saveAnswer(qId);
                        updateAnswerInfo();
                        refreshGridButton(qId);
                    }, 800);
                });
            }
        });

        // ─── Save Answer API ───────────────────────────────────
        async function saveAnswer(questionId) {
            const answer = answers[questionId];
            if (!answer) return;
            try {
                const res = await apiFetch(`${API_BASE}/student/exam/${examId}/answer`, {
                    method: 'POST',
                    body: JSON.stringify({
                        exam_question_id: questionId,
                        selected_options: answer.selected_options,
                        text_answer: answer.text_answer
                    })
                });
                if (res && res.ok) {
                    remainingSeconds = calcRemaining();
                }
            } catch (e) {
                console.warn('Gagal menyimpan jawaban:', e);
            }
        }

        function refreshGridButton(questionId) {
            const idx = questions.findIndex(q => q.id === questionId);
            if (idx === -1) return;
            const btn = document.getElementById(`navBtn${idx}`);
            if (!btn) return;
            if (answers[questionId]) btn.classList.add('answered');
            else btn.classList.remove('answered');
        }

        // ─── Navigation ───────────────────────────────────────
        async function navigateTo(index) {
            // Save current answer first
            const currentQ = questions[currentIndex];
            if (currentQ && currentQ.type === 'esai' && answers[currentQ.id]) {
                await saveAnswer(currentQ.id);
                updateAnswerInfo();
                refreshGridButton(currentQ.id);
            }
            showQuestion(index);
        }

        async function navigateQuestion(delta) {
            // Save current answer first
            const currentQ = questions[currentIndex];
            if (currentQ && currentQ.type === 'esai' && answers[currentQ.id]) {
                await saveAnswer(currentQ.id);
                updateAnswerInfo();
                refreshGridButton(currentQ.id);
            }
            const newIndex = currentIndex + delta;
            if (newIndex >= 0 && newIndex < questions.length) {
                showQuestion(newIndex);
            }
        }

        // ─── Flag ──────────────────────────────────────────────
        function toggleFlag() {
            const q = questions[currentIndex];
            if (flaggedQuestions.has(q.id)) {
                flaggedQuestions.delete(q.id);
            } else {
                flaggedQuestions.add(q.id);
            }
            updateFlagButton();
            renderQuestionGrid();
            updateNavHighlight();
            document.getElementById('flaggedCount').textContent = flaggedQuestions.size;
            document.getElementById('flaggedInfo').style.display = flaggedQuestions.size > 0 ? 'flex' : 'none';
        }

        function updateFlagButton() {
            const q = questions[currentIndex];
            const btn = document.getElementById('flagBtn');
            const label = document.getElementById('flagLabel');
            if (!q) return;
            if (flaggedQuestions.has(q.id)) {
                label.textContent = 'Hapus Tanda';
                btn.style.color = '#f59e0b';
            } else {
                label.textContent = 'Tandai';
                btn.style.color = '#64748b';
            }
        }

        // ─── Timer ─────────────────────────────────────────────
        function getServerNow() {
            return Date.now() + serverTimeOffset;
        }

        function calcRemaining() {
            if (!sessionEndTime) return 0;
            return Math.max(0, Math.ceil((sessionEndTime - getServerNow()) / 1000));
        }

        async function syncServerTime() {
            try {
                const clientBefore = Date.now();
                const res = await fetch(`${API_BASE}/server-time`);
                const clientAfter = Date.now();
                if (res.ok) {
                    const data = await res.json();
                    const serverTime = new Date(data.server_time).getTime();
                    const rtt = (clientAfter - clientBefore) / 2;
                    serverTimeOffset = serverTime - (clientBefore + rtt);
                }
            } catch (e) {
                // fallback: gunakan offset yg sudah ada
            }
        }

        function startTimer() {
            updateTimerDisplay();
            timerInterval = setInterval(async () => {
                remainingSeconds = calcRemaining();
                if (remainingSeconds <= 0) {
                    remainingSeconds = 0;
                    clearInterval(timerInterval);
                    updateTimerDisplay();
                    autoSubmit();
                } else {
                    updateTimerDisplay();
                    // Sinkronkan waktu server setiap 30 detik
                    if (remainingSeconds % 30 === 0) {
                        await syncServerTime();
                        remainingSeconds = calcRemaining();
                        updateTimerDisplay();
                    }
                }
            }, 1000);
        }

        function updateTimerDisplay() {
            const display = document.getElementById('timerDisplay');
            display.textContent = formatTime(remainingSeconds);
            if (remainingSeconds <= 60) {
                display.classList.add('text-red-600');
                display.classList.remove('text-slate-700');
            }
            if (remainingSeconds <= 300) {
                display.classList.add('text-orange-500');
            }
        }

        async function syncTime() {
            try {
                await syncServerTime();
                const rem = calcRemaining();
                const res = await apiFetch(`${API_BASE}/student/exam/${examId}/time`, {
                    method: 'PUT',
                    body: JSON.stringify({ remaining_seconds: rem })
                });
                if (res && res.ok) {
                    const data = await res.json();
                    // Gunakan end_time dari server (otoritatif)
                    if (data.data?.end_time) {
                        sessionEndTime = new Date(data.data.end_time).getTime();
                    } else if (data.data?.remaining_seconds !== undefined) {
                        sessionEndTime = getServerNow() + (data.data.remaining_seconds * 1000);
                    }
                    remainingSeconds = calcRemaining();
                }
            } catch (e) {}
        }

        function autoSubmit() {
            if (isSubmitting) return;
            alert('Waktu ujian telah habis. Jawaban Anda akan otomatis dikumpulkan.');
            doFinish();
        }

        // ─── Finish ────────────────────────────────────────────
        function confirmFinish() {
            const answered = Object.keys(answers).length;
            document.getElementById('confirmAnsweredInfo').textContent = `Anda telah menjawab ${answered} dari ${questions.length} soal.`;
            document.getElementById('confirmModal').classList.add('show');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.remove('show');
        }

        async function doFinish() {
            if (isSubmitting) return;
            isSubmitting = true;
            document.getElementById('confirmModal').classList.remove('show');
            document.getElementById('finishBtn').disabled = true;
            document.getElementById('finishBtn').textContent = 'Mengumpulkan...';

            try {
                // Save remaining time first
                await syncTime();

                const res = await apiFetch(`${API_BASE}/student/exam/${examId}/finish`, { method: 'POST' });
                if (!res || !res.ok) throw new Error('Gagal mengumpulkan');

                const data = await res.json();
                if (!data.success) throw new Error(data.message);

                clearInterval(timerInterval);
                exitSecureMode(); // Matikan mode keamanan

                // Show result
                const rd = data.data;
                let resultHtml = '';
                if (rd.show_result) {
                    resultHtml = `
                        <div class="bg-slate-50 rounded-xl p-4 mb-2">
                            <div class="text-3xl font-bold text-slate-800">${rd.total_score ?? '-'}</div>
                            <div class="text-xs text-slate-400">Total Skor</div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mb-2">
                            <div class="bg-emerald-50 rounded-xl p-3">
                                <div class="text-lg font-bold text-emerald-700">${rd.correct_count}</div>
                                <div class="text-xs text-emerald-500">Benar</div>
                            </div>
                            <div class="bg-red-50 rounded-xl p-3">
                                <div class="text-lg font-bold text-red-700">${rd.wrong_count}</div>
                                <div class="text-xs text-red-500">Salah</div>
                            </div>
                        </div>
                        <div class="text-sm font-medium ${rd.is_passed ? 'text-emerald-600' : 'text-red-600'}">
                            ${rd.is_passed ? '✅ Lulus' : '❌ Tidak Lulus'} (KKM: ${rd.minimum_score})
                        </div>
                    `;
                } else {
                    resultHtml = `
                        <div class="text-4xl mb-3">📝</div>
                        <p class="text-sm text-slate-700 font-medium mb-1">Jawaban telah dikumpulkan!</p>
                        <p class="text-xs text-slate-400">${rd.needs_grading ? 'Hasil ujian akan diumumkan oleh guru setelah dikoreksi.' : 'Hasil akan diumumkan oleh guru.'}</p>
                    `;
                }
                document.getElementById('resultScore').innerHTML = resultHtml;
                document.getElementById('resultModal').style.display = 'flex';

            } catch (e) {
                alert('Gagal mengumpulkan: ' + e.message);
                isSubmitting = false;
                document.getElementById('finishBtn').disabled = false;
                document.getElementById('finishBtn').textContent = 'Ya, Kumpulkan';
            }
        }

        // ─── Prevent Accidental Leave ──────────────────────────
        window.addEventListener('beforeunload', function(e) {
            if (Object.keys(answers).length > 0 && !isSubmitting) {
                e.preventDefault();
                e.returnValue = 'Anda memiliki jawaban yang belum dikumpulkan. Yakin ingin meninggalkan halaman?';
            }
        });

        // ─── Fullscreen Change & Security ──────────────────────
        document.addEventListener('fullscreenchange', onFullscreenChange);
        document.addEventListener('webkitfullscreenchange', onFullscreenChange);
        document.addEventListener('msfullscreenchange', onFullscreenChange);

        // ─── Block unwanted keys during exam ─────────────────
        document.addEventListener('keydown', blockKeys, true);
        document.addEventListener('keyup', blockKeys, true);
        window.addEventListener('keydown', blockKeys, true);
        window.addEventListener('keyup', blockKeys, true);
        document.addEventListener('contextmenu', function(e) { if (isSecureMode) e.preventDefault(); });

        // ─── Keyboard Navigation ────────────────────────────────
        document.addEventListener('keydown', function(e) {
            if (!isSecureMode) return;
            if (e.key === 'ArrowLeft') navigateQuestion(-1);
            else if (e.key === 'ArrowRight') navigateQuestion(1);
            else if (e.key === 'f' && !e.ctrlKey && !e.metaKey) toggleFlag();
        });

        // ─── Start ──────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>
