<table>
    <thead>
    <tr><td colspan="{{ count($headers) }}">{{ $title }}</td></tr>
    <tr><td colspan="{{ count($headers) }}">Diekspor: {{ now()->translatedFormat('d F Y, H:i') }} | Total: {{ count($rows) }} data</td></tr>
    <tr><td colspan="{{ count($headers) }}"></td></tr>
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
