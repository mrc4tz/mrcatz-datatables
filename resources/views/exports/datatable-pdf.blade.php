@php
    $c = \MrCatz\DataTable\MrCatzExport::colors();
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #1f2937; padding: 24px 28px; }

        .header { margin-bottom: 20px; padding-bottom: 12px; border-bottom: 3px solid #{{ $c['header_bg'] }}; }
        .header h2 { font-size: 18px; font-weight: 700; color: #{{ $c['title_text'] }}; margin-bottom: 4px; }
        .header .meta { font-size: 9px; color: #{{ $c['subtitle_text'] }}; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th {
            background: #{{ $c['header_bg'] }}; color: #{{ $c['header_text'] }}; font-weight: 600;
            text-align: left; padding: 7px 10px; font-size: 9px;
            text-transform: uppercase; letter-spacing: 0.5px;
            border: 1px solid #{{ $c['header_border'] }};
        }
        td {
            padding: 5px 10px; border: 1px solid #{{ $c['border'] }};
            vertical-align: top; font-size: 10px;
        }
        tr:nth-child(even) td { background: #{{ $c['stripe'] }}; }
        tr:nth-child(odd) td { background: #ffffff; }

        td:first-child, th:first-child { text-align: center; width: 30px; }

        .footer {
            margin-top: 12px; padding-top: 8px;
            border-top: 1px solid #{{ $c['border'] }};
            font-size: 8px; color: #{{ $c['subtitle_text'] }};
        }
        .footer-left { float: left; }
        .footer-right { float: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $title }}</h2>
        <div class="meta">{{ mrcatz_lang('export_banner_exported') }}: {{ now()->translatedFormat('d F Y, H:i') }} &mdash; {{ mrcatz_lang('export_banner_total') }}: {{ count($rows) }} {{ mrcatz_lang('export_banner_rows') }}</div>
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
                    <td colspan="{{ count($headers) }}" style="text-align:center;color:#9ca3af;padding:20px;">{{ mrcatz_lang('no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <span class="footer-left">{{ $title }}</span>
        <span class="footer-right">{{ now()->translatedFormat('d/m/Y H:i') }}</span>
    </div>
</body>
</html>
