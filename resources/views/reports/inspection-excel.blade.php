<table>
    <thead>
        <tr>
            <th colspan="8" style="text-align: center; font-size: 16px; font-weight: bold; background-color: #1E40AF; color: white; padding: 10px;">
                Laporan Quality Control - Inspeksi Produksi
            </th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: center; font-size: 12px; padding: 5px;">
                Periode: {{ isset($filters['start_date']) && isset($filters['end_date']) ? \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') . ' - ' . \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') : 'Semua Tanggal' }}
            </th>
        </tr>
        @if(isset($summary))
        <tr>
            <th colspan="8" style="text-align: center; font-size: 11px; background-color: #F3F4F6;">
                Total: {{ $summary['total'] }} | Lolos: {{ $summary['passed'] }} | Ditolak: {{ $summary['rejected'] }} | Pass Rate: {{ $summary['pass_rate'] }}%
            </th>
        </tr>
        @endif
        <tr style="background-color: #E5E7EB;">
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; padding: 8px;">No</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; padding: 8px;">Tanggal</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; padding: 8px;">Produk (Style)</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; padding: 8px;">Line</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; padding: 8px;">Status</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; padding: 8px;">Defect</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; padding: 8px;">Komponen</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center; padding: 8px;">Admin</th>
        </tr>
    </thead>
    <tbody>
        @foreach($inspections as $index => $inspection)
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $index + 1 }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $inspection->inspection_date->format('d/m/Y') }}</td>
            <td style="border: 1px solid #000;">{{ $inspection->product->style_number ?? '-' }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $inspection->line->name ?? '-' }}</td>
            <td style="border: 1px solid #000; text-align: center; {{ $inspection->status === 'pass' ? 'color: green;' : 'color: red;' }}">
                {{ $inspection->status === 'pass' ? 'Lolos' : 'Ditolak' }}
            </td>
            <td style="border: 1px solid #000;">{{ $inspection->defectType->name ?? '-' }}</td>
            <td style="border: 1px solid #000;">{{ $inspection->component->name ?? '-' }}</td>
            <td style="border: 1px solid #000;">{{ $inspection->user->name ?? 'System' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>