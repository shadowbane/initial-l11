<div class="pt-2">
    <h5>{{ ucfirst($key) }}</h5>
</div>
<table class="table" style="table-layout: fixed">
    @foreach ($props as $k => $prop)
        <tr>
            <th>{{ $k }}</th>
            @if (is_array($prop))
                <td>
                    @foreach ($prop as $key=>$val)
                        {{ $key }} = {{ $val }},<br/>
                    @endforeach
                </td>
            @else
                <td style="word-wrap: break-word;word-break: break-all;">{{ $prop }}</td>
            @endif
        </tr>
    @endforeach
</table>