@props(['url'])
<tr>
<td class="header" align="center">
<a href="{{ $url }}">
@if (trim($slot) === 'Laravel' || trim($slot) === config('app.name'))
<img src="https://via.placeholder.com/150x50/1E2B2E/ffffff?text=YOUR+LOGO" width="150" alt="{{ config('app.name') }} Logo">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
