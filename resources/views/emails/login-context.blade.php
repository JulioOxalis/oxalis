<x-mail::message>
# New sign-in to {{ config('app.name') }}

We noticed a sign-in to your account from a device or location we haven't seen before.

<x-mail::panel>
**When:** {{ $timestamp }}
**Method:** {{ $method }}
**IP address:** {{ $ip }}
**Device:** {{ Str::limit($userAgent, 80) }}
</x-mail::panel>

**If this was you**, no action is needed.

**If this wasn't you**, please secure your account immediately:
<x-mail::button :url="route('oxalis.account')" color="red">
Review Account Security
</x-mail::button>

You can revoke any sessions you don't recognise on your [account security page]({{ route('oxalis.sessions') }}).

Thanks,<br>
{{ config('app.name') }}

<x-mail::subcopy>
You're receiving this because login context alerts are enabled on your account.
</x-mail::subcopy>
</x-mail::message>
