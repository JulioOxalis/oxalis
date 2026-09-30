<x-auth.layout title="Two-factor verification" subtitle="Enter your authenticator code or a recovery code.">
    <form method="POST" action="{{ route('two-factor.login.store') }}">
        @csrf
        <x-auth.field name="code" label="Authenticator code" inputmode="numeric" autocomplete="one-time-code" autofocus />
        <x-auth.field name="recovery_code" label="Recovery code (alternative)" autocomplete="one-time-code" />
        <button class="btn btn-primary btn-lg w-100" type="submit">Verify</button>
    </form>
</x-auth.layout>
