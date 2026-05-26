@extends('layouts.backend')

@section('title', 'Pengaturan Sekolah')
@section('page_title', 'Pengaturan Sekolah')

@section('content')
<div class="max-w-5xl mx-auto">

    @if(session('success'))
    <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-xl text-emerald-600 text-sm font-medium flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4"></i>
        {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('backend.settings.update') }}" method="POST" enctype="multipart/form-data" x-data="{ tab: 'identitas' }">
        @csrf
        @method('PUT')

        {{-- Tab Navigation --}}
        <div class="flex gap-1 mb-6 bg-white rounded-xl p-1 border border-slate-200 shadow-sm">
            <button type="button" @click="tab = 'identitas'" :class="tab === 'identitas' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200 text-center">
                Identitas Sekolah
            </button>
            <button type="button" @click="tab = 'tampilan'" :class="tab === 'tampilan' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200 text-center">
                Tampilan Login
            </button>
            <button type="button" @click="tab = 'visi'" :class="tab === 'visi' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200 text-center">
                Visi & Misi
            </button>
            <button type="button" @click="tab = 'rapor'" :class="tab === 'rapor' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                class="flex-1 py-2 px-4 rounded-lg text-sm font-medium transition-all duration-200 text-center">
                Rapor
            </button>
        </div>

        {{-- TAB 1: Identitas Sekolah --}}
        <div x-show="tab === 'identitas'" x-transition>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1">Informasi Dasar</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Nama Sekolah <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $school->name) }}"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition"
                            placeholder="SMK Telkom Sidoarjo" required>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">NPSN</label>
                        <input type="text" name="npsn" value="{{ old('npsn', $school->npsn) }}"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition"
                            placeholder="12345678">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Alamat</label>
                        <textarea name="address" rows="2"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition"
                            placeholder="Jl. Pendidikan No. 1, Sidoarjo">{{ old('address', $school->address) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone', $school->phone) }}"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition"
                            placeholder="031-1234567">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email', $school->email) }}"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition"
                            placeholder="info@sekolah.sch.id">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Website</label>
                        <input type="text" name="website" value="{{ old('website', $school->website) }}"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition"
                            placeholder="https://sekolah.sch.id">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Nama Kepala Sekolah</label>
                        <input type="text" name="principal_name" value="{{ old('principal_name', $school->principal_name) }}"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition"
                            placeholder="Drs. Nama Kepala Sekolah">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Akreditasi</label>
                        <select name="accreditation"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition">
                            <option value="">-- Pilih --</option>
                            @foreach(['A', 'B', 'C', 'Unggul', 'Baik Sekali', 'Baik', 'Belum Terakreditasi'] as $akr)
                                <option value="{{ $akr }}" {{ old('accreditation', $school->accreditation) === $akr ? 'selected' : '' }}>{{ $akr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tahun Berdiri</label>
                        <input type="number" name="established_year" value="{{ old('established_year', $school->established_year) }}"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition"
                            placeholder="1990" min="1900" max="2100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tempat Cetak Rapor</label>
                        <input type="text" name="tempat_cetak" value="{{ old('tempat_cetak', $school->tempat_cetak) }}"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition"
                            placeholder="Jakarta">
                        <p class="text-xs text-slate-400 mt-1">Nama kota/kabupaten yang muncul di rapor, misal: "Probolinggo, 25 Mei 2026"</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Kurikulum Aktif</label>
                        <div class="space-y-3 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="sparkles" class="w-5 h-5 text-emerald-600"></i>
                                    <div>
                                        <p class="text-sm font-medium text-slate-700">Kurikulum Merdeka</p>
                                        <p class="text-xs text-slate-400">P5, PKL, TP/ATP, Fase E/F</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="kurikulum_kurmer_enabled" value="0">
                                    <input type="checkbox" name="kurikulum_kurmer_enabled" value="1"
                                        {{ old('kurikulum_kurmer_enabled', $school->kurikulum_kurmer_enabled ?? true) ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <div class="w-10 h-5 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="book-marked" class="w-5 h-5 text-blue-600"></i>
                                    <div>
                                        <p class="text-sm font-medium text-slate-700">Kurikulum 2013 (K13)</p>
                                        <p class="text-xs text-slate-400">KI-3, KI-4, KKM per mapel</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="kurikulum_k13_enabled" value="0">
                                    <input type="checkbox" name="kurikulum_k13_enabled" value="1"
                                        {{ old('kurikulum_k13_enabled', $school->kurikulum_k13_enabled ?? false) ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <div class="w-10 h-5 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">
                            <strong>Superadmin/Admin:</strong> aktifkan kurikulum yang tersedia. User biasa hanya dapat mengakses kurikulum yang sudah diaktifkan.
                            @php $single = \App\Enums\CurriculumType::autoSelectIfSingle(); @endphp
                            @if($single)
                                <br><span class="text-amber-600">Saat ini hanya <strong>{{ $single->label() }}</strong> yang aktif — user akan langsung ke dashboard tanpa pilih.</span>
                            @else
                                <br><span class="text-emerald-600">Kedua kurikulum aktif — user akan melihat halaman pilihan saat login.</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 2: Tampilan Login --}}
        <div x-show="tab === 'tampilan'" x-transition>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1">Kustomisasi Halaman Login</h3>
                <p class="text-xs text-slate-400">Pengaturan ini akan tampil di halaman login portal siswa, orang tua, dan backend.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Judul Portal</label>
                        <input type="text" name="portal_title" value="{{ old('portal_title', $school->portal_title) }}"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition"
                            placeholder="Sistem Informasi Akademik">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Teks Selamat Datang</label>
                        <input type="text" name="welcome_text" value="{{ old('welcome_text', $school->welcome_text) }}"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition"
                            placeholder="Selamat Datang! di Sistem Informasi Akademik">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tagline</label>
                        <input type="text" name="tagline" value="{{ old('tagline', $school->tagline) }}"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition"
                            placeholder="Akses nilai, presensi, ujian, dan pembayaran dalam satu portal.">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Teks Footer</label>
                        <input type="text" name="footer_text" value="{{ old('footer_text', $school->footer_text) }}"
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition"
                            placeholder="Created By SMK Telkom Sidoarjo 2022">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Warna Utama (Primary)</label>
                        <div class="flex gap-2 items-center">
                            <input type="color" name="primary_color" value="{{ old('primary_color', $school->primary_color ?? '#2563eb') }}"
                                class="w-10 h-10 rounded-lg border border-slate-200 cursor-pointer">
                            <input type="text" value="{{ old('primary_color', $school->primary_color ?? '#2563eb') }}"
                                class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-500" readonly>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Warna Sekunder (Light)</label>
                        <div class="flex gap-2 items-center">
                            <input type="color" name="primary_color_light" value="{{ old('primary_color_light', $school->primary_color_light ?? '#3b82f6') }}"
                                class="w-10 h-10 rounded-lg border border-slate-200 cursor-pointer">
                            <input type="text" value="{{ old('primary_color_light', $school->primary_color_light ?? '#3b82f6') }}"
                                class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-500" readonly>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Logo Sekolah</label>
                        @if($school->logo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/'.$school->logo) }}" alt="Logo" class="h-16 w-auto rounded-lg border border-slate-200 object-contain p-1">
                            </div>
                        @endif
                        <input type="file" name="logo" accept="image/png,image/jpg,image/jpeg,image/svg"
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                        <p class="text-xs text-slate-400 mt-1">Format: PNG, JPG, SVG. Maks: 2MB.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Gambar Ilustrasi (Halaman Login)</label>
                        @if($school->landing_image)
                            <div class="mb-2">
                                <img src="{{ asset('storage/'.$school->landing_image) }}" alt="Illustration" class="h-20 w-auto rounded-lg border border-slate-200 object-contain p-1">
                            </div>
                        @endif
                        <input type="file" name="landing_image" accept="image/png,image/jpg,image/jpeg,image/svg"
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                        <p class="text-xs text-slate-400 mt-1">Format: PNG, JPG, SVG. Maks: 5MB. Rekomendasi: ilustrasi landscape.</p>
                    </div>
                </div>

                {{-- Preview --}}
                <div class="mt-6 p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Pratinjau Warna</p>
                    <div class="flex gap-3">
                        <div class="w-16 h-16 rounded-xl" style="background: {{ old('primary_color', $school->primary_color ?? '#2563eb') }};"></div>
                        <div class="w-16 h-16 rounded-xl" style="background: {{ old('primary_color_light', $school->primary_color_light ?? '#3b82f6') }};"></div>
                        <div class="flex-1 rounded-xl flex items-center justify-center text-white font-bold text-xs" style="background: linear-gradient(135deg, {{ old('primary_color', $school->primary_color ?? '#2563eb') }}, {{ old('primary_color_light', $school->primary_color_light ?? '#3b82f6') }});">
                            Gradient
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 3: Visi & Misi --}}
        <div x-show="tab === 'visi'" x-transition>
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1">Visi & Misi Sekolah</h3>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Visi</label>
                    <textarea name="vision" rows="4"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition"
                        placeholder="Menjadi sekolah unggulan yang menghasilkan lulusan berkarakter...">{{ old('vision', $school->vision) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5">Misi</label>
                    <textarea name="mission" rows="6"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition"
                        placeholder="1. Menyelenggarakan pendidikan yang berkualitas...&#10;2. ...">{{ old('mission', $school->mission) }}</textarea>
                </div>
            </div>
        </div>

        {{-- TAB 4: Pengaturan Rapor --}}
        <div x-show="tab === 'rapor'" x-transition>
            <div class="space-y-6">

                {{-- Tanggal Cetak Rapor --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1">Tanggal Cetak Rapor</h3>
                        <p class="text-xs text-slate-400">Atur tempat dan tanggal yang muncul di bagian tanda tangan rapor.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tempat Cetak</label>
                            <input type="text" name="tempat_cetak" value="{{ old('tempat_cetak', $school->tempat_cetak) }}"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition"
                                placeholder="Jakarta">
                            <p class="text-xs text-slate-400 mt-1">Nama kota/kabupaten, misal: Probolinggo</p>
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="hidden" name="rapor_tgl_otomatis" value="0">
                                <input type="checkbox" name="rapor_tgl_otomatis" value="1" {{ old('rapor_tgl_otomatis', $school->rapor_tgl_otomatis ?? true) ? 'checked' : '' }}
                                    class="w-5 h-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="text-sm text-slate-600">Gunakan tanggal hari ini (otomatis)</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" id="tglCustomFields">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tanggal</label>
                            <select name="rapor_tanggal"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition">
                                <option value="">-- Otomatis --</option>
                                @for($i=1; $i<=31; $i++)
                                <option value="{{ $i }}" {{ old('rapor_tanggal', $school->rapor_tanggal) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Bulan</label>
                            <select name="rapor_bulan"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition">
                                <option value="">-- Otomatis --</option>
                                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $idx => $nama)
                                <option value="{{ $idx+1 }}" {{ old('rapor_bulan', $school->rapor_bulan) == ($idx+1) ? 'selected' : '' }}>{{ $nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Tahun</label>
                            <select name="rapor_tahun"
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400 transition">
                                <option value="">-- Otomatis --</option>
                                @for($y=date('Y')-2; $y<=date('Y')+2; $y++)
                                <option value="{{ $y }}" {{ old('rapor_tahun', $school->rapor_tahun) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Preview tanggal --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                        <p class="text-xs text-slate-400 mb-1">Pratinjau di Rapor:</p>
                        <p class="text-base font-semibold text-slate-700">
                            {{ old('tempat_cetak', $school->tempat_cetak ?? '_______________') }},
                            @php
                                $t = old('rapor_tanggal', $school->rapor_tanggal);
                                $b = old('rapor_bulan', $school->rapor_bulan);
                                $y = old('rapor_tahun', $school->rapor_tahun);
                                $bulanNames = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                if ($t && $b && $y) {
                                    echo $t . ' ' . $bulanNames[$b] . ' ' . $y;
                                } elseif ($school->rapor_tgl_otomatis ?? true) {
                                    echo now()->locale('id')->translatedFormat('d F Y');
                                } else {
                                    echo ($t ?: '__') . ' ' . ($b ? $bulanNames[$b] : '______') . ' ' . ($y ?: '____');
                                }
                            @endphp
                        </p>
                    </div>
                </div>

                {{-- Urutan & Tampil Modul --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1">Modul Rapor</h3>
                        <p class="text-xs text-slate-400">Atur urutan dan tampilkan/sembunyikan modul di rapor.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 text-left">
                                    <th class="py-2 px-2 text-xs font-semibold text-slate-500 uppercase w-12">Urut</th>
                                    <th class="py-2 px-2 text-xs font-semibold text-slate-500 uppercase">Modul</th>
                                    <th class="py-2 px-2 text-xs font-semibold text-slate-500 uppercase text-center w-20">Tampil</th>
                                    <th class="py-2 px-2 text-xs font-semibold text-slate-500 uppercase">Label Kustom</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @php
                                $moduls = [
                                    ['key' => 'identitas', 'label' => 'Identitas Siswa', 'order' => 'rapor_order_identitas', 'show' => null, 'labelKey' => null],
                                    ['key' => 'nilai', 'label' => 'Nilai Intrakurikuler', 'order' => 'rapor_order_nilai', 'show' => 'rapor_show_nilai', 'labelKey' => 'rapor_label_nilai'],
                                    ['key' => 'p5', 'label' => 'Projek P5', 'order' => 'rapor_order_p5', 'show' => 'rapor_show_p5', 'labelKey' => 'rapor_label_p5'],
                                    ['key' => 'presensi', 'label' => 'Kehadiran / Presensi', 'order' => 'rapor_order_presensi', 'show' => 'rapor_show_presensi', 'labelKey' => 'rapor_label_presensi'],
                                    ['key' => 'catatan', 'label' => 'Catatan Wali Kelas', 'order' => 'rapor_order_catatan', 'show' => 'rapor_show_catatan', 'labelKey' => 'rapor_label_catatan'],
                                    ['key' => 'ttd', 'label' => 'Tanda Tangan', 'order' => 'rapor_order_ttd', 'show' => null, 'labelKey' => 'rapor_label_ttd'],
                                ];
                                @endphp
                                @foreach($moduls as $m)
                                <tr class="hover:bg-slate-50/30">
                                    <td class="py-3 px-2">
                                        <input type="number" name="{{ $m['order'] }}" value="{{ old($m['order'], $school->{$m['order']} ?? $loop->iteration) }}"
                                            class="w-14 px-2 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-emerald-200"
                                            min="1" max="6">
                                    </td>
                                    <td class="py-3 px-2 font-medium text-slate-700">{{ $m['label'] }}</td>
                                    <td class="py-3 px-2 text-center">
                                        @if($m['show'])
                                        <input type="hidden" name="{{ $m['show'] }}" value="0">
                                        <input type="checkbox" name="{{ $m['show'] }}" value="1" {{ old($m['show'], $school->{$m['show']} ?? true) ? 'checked' : '' }}
                                            class="w-5 h-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                        @else
                                        <span class="text-xs text-slate-300">tetap</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-2">
                                        @if($m['labelKey'])
                                        <input type="text" name="{{ $m['labelKey'] }}" value="{{ old($m['labelKey'], $school->{$m['labelKey']}) }}"
                                            class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-200"
                                            placeholder="Bawaan: {{ $m['label'] }}">
                                        @else
                                        <span class="text-xs text-slate-300">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tampil Tanda Tangan --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-1">Tanda Tangan</h3>
                        <p class="text-xs text-slate-400">Pilih pihak yang tanda tangannya muncul di rapor.</p>
                    </div>
                    <div class="flex flex-wrap gap-6">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="rapor_show_ttd_ortu" value="0">
                            <input type="checkbox" name="rapor_show_ttd_ortu" value="1" {{ old('rapor_show_ttd_ortu', $school->rapor_show_ttd_ortu ?? true) ? 'checked' : '' }}
                                class="w-5 h-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm text-slate-600">Orang Tua / Wali</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="rapor_show_ttd_walikelas" value="0">
                            <input type="checkbox" name="rapor_show_ttd_walikelas" value="1" {{ old('rapor_show_ttd_walikelas', $school->rapor_show_ttd_walikelas ?? true) ? 'checked' : '' }}
                                class="w-5 h-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm text-slate-600">Wali Kelas</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="rapor_show_ttd_kepsek" value="0">
                            <input type="checkbox" name="rapor_show_ttd_kepsek" value="1" {{ old('rapor_show_ttd_kepsek', $school->rapor_show_ttd_kepsek ?? true) ? 'checked' : '' }}
                                class="w-5 h-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm text-slate-600">Kepala Sekolah</span>
                        </label>
                    </div>
                </div>

            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end mt-6">
            <button type="submit"
                class="px-8 py-3 bg-emerald-600 text-white font-semibold rounded-xl text-sm hover:bg-emerald-700 transition shadow-lg shadow-emerald-500/25 flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i>
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>

{{-- Alpine.js for tabs --}}
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    // Sync color picker with text input
    document.querySelectorAll('input[type="color"]').forEach(picker => {
        picker.addEventListener('input', function() {
            this.nextElementSibling.value = this.value;
        });
    });

    // Toggle custom date fields based on auto checkbox
    (function() {
        const autoCb = document.querySelector('input[name=\"rapor_tgl_otomatis\"]');
        const customFields = document.getElementById('tglCustomFields');
        if (autoCb && customFields) {
            const toggle = () => {
                customFields.style.opacity = autoCb.checked ? '0.4' : '1';
                customFields.querySelectorAll('select').forEach(s => s.disabled = autoCb.checked);
            };
            autoCb.addEventListener('change', toggle);
            toggle();
        }
    })();

    lucide.createIcons();
</script>
@endsection
