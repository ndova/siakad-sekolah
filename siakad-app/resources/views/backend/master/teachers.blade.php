@extends('layouts.backend')

@section('title', 'Data Guru')
@section('page_title', 'Data Guru')

@section('content')
@if(session('success'))
<div class="mb-4 p-4 rounded-xl bg-accent-50 border border-accent-100 text-accent text-sm flex items-center gap-2">
    <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
</div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <form method="GET" class="flex gap-2 filter-form">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari guru..." class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-accent-200 w-56">
        <button type="submit" class="px-4 py-2.5 rounded-xl btn-accent text-white text-sm font-medium hover:brightness-110"><i data-lucide="search" class="w-4 h-4 inline"></i></button>
    </form>
</div>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden table-responsive">
    <table class="w-full text-sm">
        <thead><tr class="bg-slate-50 text-left">
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">NIP</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Nama</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Email</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Role</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Wali Kelas</th>
            <th class="px-5 py-3 text-xs font-semibold text-slate-500 uppercase">Status</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-50">
        @forelse($teachers as $teacher)
        <tr class="hover:bg-slate-50/50 transition">
            <td class="px-5 py-3.5 font-mono text-sm">{{ $teacher->nip ?? '-' }}</td>
            <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-accent-100 flex items-center justify-center text-accent font-bold text-xs">{{ strtoupper(substr($teacher->name,0,1)) }}</div>
                    <span class="font-medium text-slate-800">{{ $teacher->name }}</span>
                </div>
            </td>
            <td class="px-5 py-3.5 text-slate-500">{{ $teacher->email }}</td>
            <td class="px-5 py-3.5">
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-accent-50 text-accent">
                    {{ str_replace('_',' ',$teacher->role) }}
                </span>
            </td>
            <td class="px-5 py-3.5">
                @if($teacher->homeroomClass)
                <span class="px-2 py-1 rounded-full text-xs font-medium bg-accent-50 text-accent">{{ $teacher->homeroomClass->code }}</span>
                @else <span class="text-slate-300">-</span> @endif
            </td>
            <td class="px-5 py-3.5">
                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $teacher->is_active?'bg-accent-50 text-accent':'bg-red-50 text-red-600' }}">{{ $teacher->is_active?'Aktif':'Nonaktif' }}</span>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="px-5 py-12 text-center text-slate-400">Belum ada data guru.</td></tr>
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
    {{ $teachers->links() }}
</div>
@endsection
