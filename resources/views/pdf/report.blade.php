<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Laporan</title>
    <style>
        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 10px;
            padding: 15px 20px;
            background: #fff;
            color: #333;
            line-height: 1.3;
        }

        /* Header */
        .header {
            text-align: center;
            border-bottom: 3px double #1a237e;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            color: #1a237e;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 3px;
        }

        .header .subtitle {
            font-size: 12px;
            color: #555;
        }

        .header .period {
            font-size: 11px;
            color: #666;
            margin-top: 2px;
        }

        /* Company Info */
        .company-info {
            text-align: center;
            margin-bottom: 12px;
            padding: 6px;
            background: #f5f5f5;
            border-radius: 4px;
            font-size: 9px;
            color: #555;
        }

        /* Info Section - 2 columns */
        .info-section {
            margin-bottom: 12px;
            padding: 8px 15px;
            background: #f8f9fa;
            border-radius: 4px;
            /* border-left: 4px solid #1a237e; */
        }

        .info-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-section td {
            padding: 2px 8px;
            font-size: 9px;
        }

        .info-section .label {
            font-weight: bold;
            color: #333;
            width: 100px;
        }

        .info-section .value {
            color: #555;
        }

        /* Table */
        .table-container {
            margin: 10px 0;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }

        table.data-table thead th {
            background: #1a237e;
            color: #fff;
            padding: 5px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #1a237e;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        table.data-table tbody td {
            padding: 4px 4px;
            border: 1px solid #ddd;
            color: #333;
            vertical-align: middle;
        }

        table.data-table tfoot td {
            padding: 4px 4px;
            border: 1px solid #ddd;
            color: #333;
            vertical-align: middle;
        }

        table.data-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        /* Text Alignment */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        /* Status Badge */
        .status-badge {
            padding: 1px 6px;
            border-radius: 10px;
            font-size: 7.5px;
            font-weight: bold;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        .status-success {
            background: #4caf50;
            color: #fff;
        }

        .status-warning {
            background: #ff9800;
            color: #fff;
        }

        .status-error {
            background: #f44336;
            color: #fff;
        }

        .status-info {
            background: #2196f3;
            color: #fff;
        }

        .status-default {
            background: #9e9e9e;
            color: #fff;
        }

        /* Currency */
        .currency {
            font-weight: 600;
            font-size: 8.5px;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7.5px;
            color: #999;
            padding: 6px;
            border-top: 1px solid #e0e0e0;
            background: #fff;
        }

        /* No Data */
        .no-data {
            text-align: center;
            padding: 30px 20px;
            color: #999;
            font-size: 12px;
        }

        /* Page Number */
        .page-number {
            font-size: 7.5px;
        }

        /* Print Styles */
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 10px 15px;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>Laporan Cash Advance</h1>
        <div class="subtitle">PT. BATANG APPAREL INDONESIA</div>
        <div class="period">
            Periode: {{ $start_date }}
            s/d {{ $end_date }}
        </div>
    </div>

    <!-- Company Info -->
    <div class="company-info">
        <strong>PT. Batang Apparel Indonesia</strong> |
        Jl. Sendang - Tulis, Area Sawah, Wringingintung, Kec. Tulis, Kabupaten Batang, Jawa Tengah 51261 |
        Telp: (0285) 3974510
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <table>
            <tr>
                <td class="label">Tanggal Cetak</td>
                <td class="value">: {{ $generated_at }}</td>
                <td class="label" style="padding-left: 30px;">Total Data</td>
                <td class="value">: {{ $summary['total_data'] ?? 0 }}</td>
                <td class="label" style="padding-left: 30px;">Total Pengajuan</td>
                <td class="value">: Rp {{ number_format($summary['total_amount'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Periode</td>
                <td class="value">: {{ $start_date }} s/d
                    {{ $end_date }}</td>
                <td class="label" style="padding-left: 30px;">Total Dicairkan</td>
                <td class="value">: Rp {{ number_format($summary['total_disbursed'] ?? 0, 0, ',', '.') }}</td>
                <td class="label" style="padding-left: 30px;">Total Dikembalikan</td>
                <td class="value">: Rp {{ number_format($summary['total_remaining'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Table Data -->
    <div class="table-container">
        @if (isset($data) && count($data) > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="30" style="text-align: center">No</th>
                        <th width="85" style="text-align: center">Tgl Pengajuan
                        </th>
                        <th width="80" style="text-align: center">Departemen</th>
                        <th width="auto" style="text-align: center">Deskripsi Penggunaan Dana</th>
                        <th width="80" style="text-align: right">Jml Pengajuan</th>
                        <th width="80" style="text-align: right">Jml Dicairkan</th>
                        <th width="80" style="text-align: right">Dikembalikan</th>
                        <th width="80" style="text-align: center">Status Laporan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total_amount = 0;
                        $total_disbursed = 0;
                        $total_remaning = 0;
                        $remaning_amount = 0;
                    @endphp
                    @foreach ($data as $index => $item)
                        @php
                            $remaning_amount = $item->disbursement->amount - $item->disbursement->total_spent;

                            $total_amount += $item->disbursement->amount;
                            $total_disbursed += $item->disbursement->total_spent;
                            $total_remaning += $remaning_amount;
                        @endphp
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="text-center">
                                {{ \Carbon\Carbon::parse($item->created_at)->format('d-m-Y') }}
                            </td>
                            <td>{{ \Illuminate\Support\Str::limit($item->user->department->name ?? '-', 20) }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($item->purpose ?? '-', 35) }}</td>
                            <td class="text-right currency">
                                {{ number_format($item->disbursement->amount ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-right currency">
                                {{ number_format($item->disbursement->total_spent ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-right currency">
                                {{ number_format($remaning_amount ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @php
                                    $status = $item->disbursement->report_status ?? 'default';
                                    $statusMap = [
                                        'approved' => ['type' => 'success', 'label' => 'Selesai'],
                                        'not_submitted' => ['type' => 'warning', 'label' => 'Belum dikirim'],
                                        'submitted' => ['type' => 'error', 'label' => 'Menunggu verifikasi'],
                                    ];
                                    $statusConfig = $statusMap[$status] ?? [
                                        'type' => 'default',
                                        'label' => ucfirst($status),
                                    ];
                                @endphp
                                <span class="status-badge status-{{ $statusConfig['type'] }}">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: #e8eaf6; font-weight: bold;">
                        <td colspan="4" class="text-right">TOTAL</td>
                        <td class="text-right currency">
                            {{ number_format($total_amount ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="text-right currency">
                            {{ number_format($total_disbursed ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="text-right currency">
                            {{ number_format($total_remaning ?? 0, 0, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        @else
            <div class="no-data">
                <p>Tidak ada data pada periode {{ $start_date }} s/d
                    {{ $end_date }}</p>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <span>Dicetak pada: {{ $generated_at }}</span>
        <span> | Oleh: {{ auth()->user()->name ?? 'System' }}</span>
        <span class="page-number"> | Halaman <span class="page"></span></span>
    </div>

    <!-- Script untuk page number -->
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script('
                $font = $fontMetrics->get_font("Arial", "normal");
                $pdf->text(510, 815, "Halaman " . $PAGE_NUM . " dari " . $PAGE_COUNT, $font, 8, array(0,0,0));
            ');
        }
    </script>
</body>

</html>
