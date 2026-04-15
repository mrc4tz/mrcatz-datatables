<table>
    <thead>
    @php
        // CSV route: PhpSpreadsheet's HTML reader + CSV writer combo
        // does not preserve cell text when `colspan` is used (anchor
        // cell ends up empty in CSV). For XLSX we still use colspan
        // because `MrCatzExport::styles()` merges A1:lastCol1 etc. —
        // merged cells keep only the anchor's value, so writing the
        // title in the anchor cell and padding the rest with empty
        // cells is both CSV-correct AND XLSX-merge-compatible.
        //
        // Additionally, on CSV with an index/No column as first column,
        // shift the banner one cell to the right so the "No" column
        // (typically narrow) doesn't get auto-sized to the title's width.
        $isCsv   = ($format ?? 'xlsx') === 'csv';
        $colN    = count($headers);
        $shift   = $isCsv && ($hasIndexCol ?? false);
        $leading = $shift ? 1 : 0;
        $padCnt  = max(0, $colN - $leading - 1);
    @endphp
    <tr>
        @for($i = 0; $i < $leading; $i++)<td></td>@endfor
        <td>{{ $title }}</td>
        @for($i = 0; $i < $padCnt; $i++)<td></td>@endfor
    </tr>
    <tr>
        @for($i = 0; $i < $leading; $i++)<td></td>@endfor
        <td>{{ mrcatz_lang('export_banner_exported') }}: {{ now()->translatedFormat('d F Y, H:i') }} | {{ mrcatz_lang('export_banner_total') }}: {{ count($rows) }} {{ mrcatz_lang('export_banner_rows') }}</td>
        @for($i = 0; $i < $padCnt; $i++)<td></td>@endfor
    </tr>
    <tr>
        @for($i = 0; $i < $colN; $i++)<td></td>@endfor
    </tr>
    <tr>
        @foreach($headers as $header)
            <th>{{ $header }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($rows as $row)
        <tr>
            @foreach($row as $cell)
                <td>{{ $cell }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
