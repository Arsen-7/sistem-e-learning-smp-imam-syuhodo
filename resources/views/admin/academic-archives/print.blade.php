<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $sectionLabel }} - {{ $selectedAcademicYear->nama ?? 'Arsip Akademik' }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #0f172a; margin: 28px; }
        h1 { font-size: 22px; margin: 0 0 6px; }
        p { margin: 0 0 18px; color: #475569; }
        table { border-collapse: collapse; width: 100%; font-size: 12px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; text-transform: uppercase; font-size: 11px; }
        .toolbar { margin-bottom: 18px; }
        .button { border: 0; border-radius: 8px; background: #0e7490; color: #fff; cursor: pointer; font-weight: 700; padding: 10px 14px; }
        @media print { .toolbar { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <h1>{{ $sectionLabel }}</h1>
    <p>Arsip Akademik Tahun {{ $selectedAcademicYear->nama ?? '-' }}</p>

    <table>
        <thead>
            <tr>
                @foreach($table['headers'] as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($table['rows'] as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max(count($table['headers']), 1) }}">Tidak ada data untuk filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.addEventListener('load', () => window.print());
    </script>
</body>
</html>
