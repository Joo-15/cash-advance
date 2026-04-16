<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pinjaman untuk Kerja</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Arial, sans-serif;
            background: #fff;
            padding: 0;
            margin: 0;
        }

        /* Container utama untuk A4 */
        .a4-container {
            width: 600px;
            max-width: 210mm;
            margin: 0 auto;
            background: #fff;
            padding: 15mm 15mm 15mm 15mm;
        }

        .receipt {
            width: 100%;
            margin: 0 auto;
            background: #fff;
        }

        .receipt-header {
            text-align: center;
            padding: 0 0 25px 0;
            border-bottom: 2px solid #000;
            margin-bottom: 25px;
        }

        .receipt-header h1 {
            font-size: 24px;
            margin: 0 0 8px 0;
            letter-spacing: 2px;
        }

        .receipt-header p {
            font-size: 14px;
            margin: 0;
        }

        .receipt-body {
            padding: 0;
        }

        /* Informasi Peminjam dengan Table - Modern Style */
        .info-section {
            margin-bottom: 35px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table tr {
            margin-bottom: 5px;
        }

        .info-label {
            width: 100px;
            font-weight: bold;
            font-size: 13px;
            padding: 20px 0;
            vertical-align: middle;
            /* background: #f8f9fa; */
            padding-left: 10px;
        }

        .info-value {
            border-bottom: 1px dotted #a8a6a6;
            padding: 12px 8px;
            font-size: 13px;
            background: #fff;
        }

        /* Tanda Tangan - Modern Style */
        .signature {
            margin-top: 40px;
        }

        .signature h2 {
            text-align: center;
            font-size: 16px;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .signature table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature th {
            border: 1px solid #000;
            padding: 14px 5px;
            font-size: 12px;
            background: #f8f9fa;
            text-align: center;
            font-weight: bold;
        }

        .signature td {
            border: 1px solid #000;
            padding: 15px 8px;
            vertical-align: top;
            background: #fff;
        }

        .signature-content {
            text-align: center;
        }

        .signature-name {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 8px;
            word-wrap: break-word;
            min-height: 35px;
        }

        .signature-position {
            font-size: 10px;
            color: #666;
            margin-bottom: 10px;
            height: 30px;
        }

        .signature-status {
            font-size: 10px;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #ddd;
        }

        .status-approved {
            color: #28a745;
            font-weight: bold;
        }

        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }

        .status-rejected {
            color: #dc3545;
            font-weight: bold;
        }

        .signature-line {
            font-size: 10px;
            color: #999;
            margin-top: 12px;
        }

        /* Catatan - Modern Style */
        .note {
            margin-top: 5px;
            font-size: 11px;
            font-style: italic;
            color: #666;
            line-height: 1.5;
        }

        /* Print styling untuk A4 */
        @media print {
            body {
                padding: 0;
                margin: 0;
            }

            .a4-container {
                padding: 15mm;
                margin: 0;
                width: 100%;
            }

            @page {
                size: A4;
                margin: 0;
            }

            .info-label {
                background: #f8f9fa;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .signature th {
                background: #f8f9fa;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <div class="a4-container">
        <div class="receipt">
            <div class="receipt-header">
                <h1>PINJAMAN UNTUK KERJA</h1>
                <p>PT. Batang Apparel Indonesia</p>
            </div>

            <div class="receipt-body">
                <!-- Informasi Peminjam dengan Table - Modern Style -->
                <div class="info-section">
                    <table class="info-table">
                        <tr>
                            <td class="info-label">NAMA</td>
                            <td style="width:20px">:</td>
                            <td class="info-value">
                                <strong>{{ $data->user->name ?? '_________________________' }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">DEPT</td>
                            <td>:</td>

                            <td class="info-value">
                                {{ $data->user->department->name ?? '_________________________' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">TANGGAL</td>
                            <td>:</td>

                            <td class="info-value">
                                {{ $tanggal ?? date('d/m/Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">JUMLAH</td>
                            <td>:</td>

                            <td class="info-value">
                                <strong style="color: #28a745; font-size: 14px;">Rp
                                    {{ number_format($data->amount, 0, ',', '.') }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">TUJUAN</td>
                            <td>:</td>

                            <td class="info-value">
                                {{ $data->purpose ?? '_________________________' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label">DITERIMA</td>
                            <td>:</td>

                            <td class="info-value">
                                {{ $data->user->name ?? '_________________________' }}
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Tanda Tangan - Modern Style -->
                <div class="signature">
                    <h2>TANDA TANGAN</h2>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 20%">SPV/MGR</th>
                                <th style="width: 20%">FINANCE</th>
                                <th style="width: 20%">M.ACC</th>
                                <th style="width: 20%">G.MANAGER</th>

                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <!-- SPV/MGR -->
                                <td class="signature-content">
                                    @php
                                        $approval = $data->approvals->where('approval_step_id', 1)->first();
                                    @endphp
                                    <div class="signature-position">
                                        {{ $approval->user->name }}</div>
                                    <div class="signature-status">

                                        @if ($approval)
                                            @if ($approval->status == 'approved')
                                                <span class="status-approved">Disetujui</span>
                                            @elseif($approval->status == 'rejected')
                                                <span class="status-rejected">✗ Ditolak</span>
                                            @else
                                                <span class="status-pending">◉ Menunggu</span>
                                            @endif
                                            <div class="signature-line">
                                                {{ $approval->approved_at ? date('d/m/Y', strtotime($approval->approved_at)) : '' }}
                                            </div>
                                        @else
                                            <span class="status-pending">◉ Menunggu</span>
                                            <div class="signature-line">_________________</div>
                                        @endif
                                    </div>
                                </td>

                                <!-- FINANCE -->
                                <td class="signature-content">
                                    @php
                                        $approval = $data->approvals->where('approval_step_id', 4)->first();
                                    @endphp
                                    <div class="signature-position">
                                        {{ $approval->user->name }}</div>
                                    <div class="signature-status">
                                        @if ($approval)
                                            @if ($approval->status == 'approved')
                                                <span class="status-approved">Disetujui</span>
                                            @elseif($approval->status == 'rejected')
                                                <span class="status-rejected">Ditolak</span>
                                            @else
                                                <span class="status-pending">Menunggu</span>
                                            @endif
                                            <div class="signature-line">
                                                {{ $approval->approved_at ? date('d/m/Y', strtotime($approval->approved_at)) : '' }}
                                            </div>
                                        @else
                                            <span class="status-pending">Menunggu</span>
                                            <div class="signature-line">_________________</div>
                                        @endif
                                    </div>
                                </td>

                                <!-- M.ACC -->
                                <td class="signature-content">
                                    @php
                                        $approval = $data->approvals->where('approval_step_id', 3)->first();
                                    @endphp
                                    <div class="signature-position">
                                        {{ $approval->user->name }}</div>
                                    <div class="signature-status">
                                        @if ($approval)
                                            @if ($approval->status == 'approved')
                                                <span class="status-approved">Disetujui</span>
                                            @elseif($approval->status == 'rejected')
                                                <span class="status-rejected">Ditolak</span>
                                            @else
                                                <span class="status-pending">Menunggu</span>
                                            @endif
                                            <div class="signature-line">
                                                {{ $approval->approved_at ? date('d/m/Y', strtotime($approval->approved_at)) : '' }}
                                            </div>
                                        @else
                                            <span class="status-pending">◉ Menunggu</span>
                                            <div class="signature-line">_________________</div>
                                        @endif
                                    </div>
                                </td>

                                <!-- G.MANAGER -->
                                <td class="signature-content">
                                    @php
                                        $approval = $data->approvals->where('approval_step_id', 2)->first();
                                    @endphp
                                    <div class="signature-position">
                                        {{ $approval->user->name }}</div>
                                    <div class="signature-status">

                                        @if ($approval)
                                            @if ($approval->status == 'approved')
                                                <span class="status-approved">Disetujui</span>
                                            @elseif($approval->status == 'rejected')
                                                <span class="status-rejected">Ditolak</span>
                                            @else
                                                <span class="status-pending">Menunggu</span>
                                            @endif
                                            <div class="signature-line">
                                                {{ $approval->approved_at ? date('d/m/Y', strtotime($approval->approved_at)) : '' }}
                                            </div>
                                        @else
                                            <span class="status-pending">◉ Menunggu</span>
                                            <div class="signature-line">_________________</div>
                                        @endif
                                    </div>
                                </td>

                                {{-- <!-- DIRECTOR -->
                                <td class="signature-content">
                                    <div class="signature-name">
                                        {{ $data->approvals->where('position', 'director')->first()->name ?? '_________________' }}
                                    </div>
                                    <div class="signature-position">Director</div>
                                    <div class="signature-status">
                                        @php
                                            $approval = $data->approvals->where('position', 'director')->first();
                                        @endphp
                                        @if ($approval)
                                            @if ($approval->status == 'approved')
                                                <span class="status-approved">✓ Disetujui</span>
                                            @elseif($approval->status == 'rejected')
                                                <span class="status-rejected">✗ Ditolak</span>
                                            @else
                                                <span class="status-pending">◉ Menunggu</span>
                                            @endif
                                            <div class="signature-line">
                                                {{ $approval->approved_at ? date('d/m/Y', strtotime($approval->approved_at)) : '' }}
                                            </div>
                                        @else
                                            <span class="status-pending">◉ Menunggu</span>
                                            <div class="signature-line">_________________</div>
                                        @endif
                                    </div>
                                </td> --}}
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Catatan - Modern Style -->
                <div class="note">
                    <strong>NOTE:</strong> Penyelesaian / settle pinjaman kerja max. 5 hari kerja
                </div>
            </div>
        </div>
    </div>
</body>

</html>
