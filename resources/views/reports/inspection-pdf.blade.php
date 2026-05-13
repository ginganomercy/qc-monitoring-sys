<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inspeksi QC</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #555;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary table {
            width: 50%;
            border-collapse: collapse;
        }
        .summary td {
            padding: 4px;
        }
        .summary td:first-child {
            font-weight: bold;
            width: 120px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data th, table.data td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        table.data th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        .status-pass {
            color: #15803d;
            font-weight: bold;
        }
        .status-reject {
            color: #b91c1c;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 30px;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-number:before {
            content: "Halaman " counter(page);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Quality Control Produksi</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>

    @php
        $total = $inspections->count();
        $passed = $inspections->where('status', 'pass')->count();
        $rejected = $inspections->where('status', 'reject')->count();
        $passRate = $total > 0 ? round(($passed / $total) * 100, 1) : 0;
    @endphp

    <div class="summary">
        <table>
            <tr>
                <td>Total Inspeksi</td>
                <td>: {{ $total }}</td>
            </tr>
            <tr>
                <td>Total Lolos</td>
                <td>: {{ $passed }} ({{ $passRate }}%)</td>
            </tr>
            <tr>
                <td>Total Ditolak</td>
                <td>: {{ $rejected }}</td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th width="10%">Tanggal</th>
                <th width="15%">Produk</th>
                <th width="10%">Line</th>
                <th width="10%">Status</th>
                <th width="20%">Jenis Defect</th>
                <th width="20%">Komponen</th>
                <th width="15%">Inspector</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inspections as $inspection)
            <tr>
                <td>{{ $inspection->inspection_date->format('d/m/Y') }}</td>
                <td>{{ $inspection->product->style_number ?? '-' }}</td>
                <td>{{ $inspection->line->name ?? '-' }}</td>
                <td class="{{ $inspection->status === 'pass' ? 'status-pass' : 'status-reject' }}">
                    {{ $inspection->status === 'pass' ? 'Lolos' : 'Ditolak' }}
                </td>
                <td>{{ $inspection->defectType->name ?? '-' }}</td>
                <td>{{ $inspection->component->name ?? '-' }}</td>
                <td>{{ $inspection->inspector->name ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">Tidak ada data inspeksi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }} | Sistem QC Monitoring | <span class="page-number"></span>
    </div>
</body>
</html>
