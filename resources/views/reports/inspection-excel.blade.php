<table>
    <thead>
        <tr>
            <th colspan="7" style="text-align: center; font-size: 14px; font-weight: bold;">
                Laporan Quality Control - Inspeksi Produksi
            </th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center;">
                Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            </th>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Tanggal</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Produk (Style)</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Line</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Status</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Defect</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Komponen</th>
            <th style="font-weight: bold; border: 1px solid #000; text-align: center;">Inspector</th>
        </tr>
    </thead>
    <tbody>
        @foreach($inspections as $inspection)
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $inspection->inspection_date->format('d/m/Y') }}</td>
            <td style="border: 1px solid #000;">{{ $inspection->product->style_number ?? '-' }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $inspection->line->name ?? '-' }}</td>
            <td style="border: 1px solid #000; text-align: center;">
                {{ $inspection->status === 'pass' ? 'Lolos' : 'Ditolak' }}
            </td>
            <td style="border: 1px solid #000;">{{ $inspection->defectType->name ?? '-' }}</td>
            <td style="border: 1px solid #000;">{{ $inspection->component->name ?? '-' }}</td>
            <td style="border: 1px solid #000;">{{ $inspection->inspector->name ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
