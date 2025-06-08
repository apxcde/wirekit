<x-mail::message>
# Login to {{ config('app.name') }}

Click the button below to finish logging into {{ config('app.name') }}:

<x-mail::button :url="$url">
Complete Login
</x-mail::button>

Cheers,<br>
{{ config('app.name') }}

Alternatively you can also copy and paste the link into your browser:

`{{ $url }}`

</x-mail::message>
