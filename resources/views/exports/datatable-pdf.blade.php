<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #1f2937; padding: 24px 28px; }

        /* Header */
        .header { margin-bottom: 20px; padding-bottom: 12px; border-bottom: 3px solid #1B3A5C; }
        .header h2 { font-size: 18px; font-weight: 700; color: #1B3A5C; margin-bottom: 4px; }
        .header .meta { font-size: 9px; color: #6b7280; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th {
            background: #1B3A5C; color: #ffffff; font-weight: 600;
            text-align: left; padding: 7px 10px; font-size: 9px;
            text-transform: uppercase; letter-spacing: 0.5px;
            border: 1px solid #0F2942;
        }
        td {
            padding: 5px 10px; border: 1px solid #d1d5db;
            vertical-align: top; font-size: 10px;
        }
        tr:nth-child(even) td { background: #f0f4f8; }
        tr:nth-child(odd) td { background: #ffffff; }

        /* First column (No) center */
        td:first-child, th:first-child { text-align: center; width: 30px; }

        /* Footer */
        .footer {
            margin-top: 12px; padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px; color: #9ca3af;
            display: flex; justify-content: space-between;
        }
        .footer-left { float: left; }
        .footer-right { float: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <div class="meta">Diekspor: {{ now()->translatedFormat('d F Y, H:i') }} &mdash; Total: {{ count($rows) }} data</div>
    </div>

    <table>
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" style="text-align:center;color:#9ca3af;padding:20px;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <span class="footer-left">{{ $title }}</span>
        <span class="footer-right">Halaman 1 &mdash; {{ now()->translatedFormat('d/m/Y H:i') }}</span>
    </div>
</body>
</html>
