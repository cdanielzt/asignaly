<table class="block">
    <tbody>
        <tr>
            <td class="block-header" colspan="2">{{ $dateLabel }}</td>
        </tr>
        @foreach ($roles as $key => $label)
        <tr>
            <td class="role-label">{{ $label }}</td>
            <td class="role-name {{ empty($meeting[$key]['attendant']) ? 'empty' : '' }}">
                {{ $meeting[$key]['attendant']['name'] ?? '—' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
