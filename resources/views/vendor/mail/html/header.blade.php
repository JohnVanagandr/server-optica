@props(['url'])
<tr>
    <td class="header">
        <a href="{{ config('app.frontend_url') }}" style="display: inline-block;">
            <img src="{{ config('app.logo_url') }}" class="logo" alt="Logo {{ config('app.name') }}">
        </a>
    </td>
</tr>
