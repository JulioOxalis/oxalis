<x-auth.layout title="Confirm your password" subtitle="This protects changes to passkeys and other sensitive settings.">
    <form method="POST" action="{{ route('password.confirm.store') }}">
        @csrf
        <x-auth.field name="password" label="Password" type="password" autocomplete="current-password" autofocus />
        <button class="btn btn-primary btn-lg w-100" type="submit">Confirm</button>
    </form>
</x-auth.layout>
