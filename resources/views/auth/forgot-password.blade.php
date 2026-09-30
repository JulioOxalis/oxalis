<x-auth.layout title="Reset your password" subtitle="We will email you a short-lived reset link.">
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <x-auth.field name="email" label="Email address" type="email" autocomplete="email" autofocus />
        <button class="btn btn-primary btn-lg w-100" type="submit">Send reset link</button>
    </form>
    <p class="auth-secondary"><a href="{{ route('login') }}">Back to sign in</a></p>
</x-auth.layout>
