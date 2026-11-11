<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900">Alumni/Lulusan</h1>
            <p class="text-sm text-slate-500">Daftar siswa yang sudah diproses lulus.</p>
        </div>
    </x-slot>

    <form method="GET" class="mb-5 grid gap-3 rounded-2xl bg-white/90 p-4 shadow-sm md:grid-cols-[1fr_auto]">
        <input name="q" value="{{ request('q') }}" class="rounded-xl border-slate-200 text-sm" placeholder="Cari nama atau NIS/NISN">
        <button class="rounded-xl bg-cyan-700 px-5 py-2 text-sm font-bold text-white">Cari</button>
    </form>

    <div class="overflow-hidden rounded-3xl bg-white/90 shadow-sm">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                <tr><th class="px-5 py-3">Nama</th><th class="px-5 py-3">NIS</th><th class="px-5 py-3">Kelas Terakhir</th><th class="px-5 py-3">Tahun Ajaran</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($alumni as $item)
                    <tr>
                        <td class="px-5 py-4 font-semibold text-slate-900">{{ $item->student->nama ?? '-' }}</td>
                        <td class="px-5 py-4">{{ $item->student->nis ?? '-' }}</td>
                        <td class="px-5 py-4">{{ $item->student->schoolClass->nama_kelas ?? '-' }}</td>
                        <td class="px-5 py-4">{{ $item->academicYear->nama ?? '-' }}</td>
                        <td class="px-5 py-4">Alumni - {{ $item->tanggal_lulus?->format('d M Y') ?? '-' }}</td>
                        <td class="px-5 py-4 text-right"><a href="{{ route('admin.alumni.show', $item) }}" class="app-action-button app-action-button--detail">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada alumni.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4">{{ $alumni->links() }}</div>
    </div>
</x-app-layout>
