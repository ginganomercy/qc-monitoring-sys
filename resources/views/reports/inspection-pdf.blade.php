<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inspeksi QC</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #1E40AF;
            padding-bottom: 15px;
            margin-bottom: 20px;
            background-color: #F8FAFC;
            padding: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            color: #1E40AF;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 12px;
            color: #555;
        }
        .summary {
            margin-bottom: 20px;
            background-color: #F1F5F9;
            padding: 15px;
            border-radius: 5px;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-item {
            display: inline-block;
            width: 24%;
            padding: 10px;
            text-align: center;
            border-right: 1px solid #E2E8F0;
        }
        .summary-item:last-child {
            border-right: none;
        }
        .summary-value {
            font-size: 24px;
            font-weight: bold;
            color: #1E40AF;
        }
        .summary-label {
            font-size: 10px;
            color: #64748B;
            text-transform: uppercase;
        }
        .stats-row {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        .stat-box {
            display: inline-block;
            width: 33%;
            text-align: center;
            padding: 8px;
        }
        .stat-box.pass { color: #15803d; }
        .stat-box.reject { color: #DC2626; }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }
        table.data th, table.data td {
            border: 1px solid #CBD5E1;
            padding: 6px 4px;
        }
        table.data th {
            background-color: #1E40AF;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            text-align: center;
        }
        table.data tbody tr:nth-child(even) {
            background-color: #F8FAFC;
        }
        table.data tbody tr:hover {
            background-color: #F1F5F9;
        }
        .status-pass {
            color: #15803d;
            font-weight: bold;
        }
        .status-reject {
            color: #DC2626;
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 25px;
            text-align: center;
            font-size: 9px;
            color: #94A3B8;
            border-top: 1px solid #E2E8F0;
            padding-top: 8px;
            background-color: #F8FAFC;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Quality Control Produksi</h1>
        <p>Periode:
            @if(isset($filters['start_date']) && isset($filters['end_date']))
                {{ \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') }}
            @else
                Semua Tanggal
            @endif
        </p>
    </div>

    @if(isset($summary))
    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ $summary['total'] }}</div>
                <div class="summary-label">Total Inspeksi</div>
            </div>
            <div class="summary-item">
                <div class="summary-value" style="color: #15803d;">{{ $summary['passed'] }}</div>
                <div class="summary-label">Lolos</div>
            </div>
            <div class="summary-item">
                <div class="summary-value" style="color: #DC2626;">{{ $summary['rejected'] }}</div>
                <div class="summary-label">Ditolak</div>
            </div>
            <div class="summary-item">
                <div class="summary-value" style="color: #1E40AF;">{{ $summary['pass_rate'] }}%</div>
                <div class="summary-label">Pass Rate</div>
            </div>
        </div>
    </div>
    @endif

    <table class="data">
        <thead>
            <tr>
                <th width="5%" style="text-align: center;">No</th>
                <th width="10%">Tanggal</th>
                <th width="15%">Produk</th>
                <th width="10%">Line</th>
                <th width="8%">Status</th>
                <th width="22%">Jenis Defect</th>
                <th width="15%">Komponen</th>
                <th width="15%">Admin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inspections as $index => $inspection)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="text-align: center;">{{ $inspection->inspection_date->format('d/m/Y') }}</td>
                <td>{{ $inspection->product->style_number ?? '-' }}</td>
                <td style="text-align: center;">{{ $inspection->line->name ?? '-' }}</td>
                <td style="text-align: center;" class="{{ $inspection->status === 'pass' ? 'status-pass' : 'status-reject' }}">
                    {{ $inspection->status === 'pass' ? 'Lolos' : 'Ditolak' }}
                </td>
                <td>{{ $inspection->defectType->name ?? '-' }}</td>
                <td>{{ $inspection->component->name ?? '-' }}</td>
                <td>{{ $inspection->user->name ?? 'System' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 30px;">Tidak ada data inspeksi pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ $generatedAt ?? now()->format('d/m/Y H:i') }} | Sistem QC Monitoring
    </div>
</body>
</html>