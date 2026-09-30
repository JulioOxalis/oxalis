<x-auth.layout title="Create your account" subtitle="Start with a secure password; add a passkey after verification.">
    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <x-auth.field name="name" label="Full name" autocomplete="name" autofocus />
        <x-auth.field name="email" label="Email address" type="email" autocomplete="email" />
        <x-auth.field name="password" label="Password" type="password" autocomplete="new-password" />
        <x-auth.field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" />
        <p class="form-text mb-4">Use at least 12 characters with upper/lowercase letters, a number, and a symbol.</p>
        <button class="btn btn-primary btn-lg w-100" type="submit">Create account</button>
    </form>
    <p class="auth-secondary">Already registered? <a href="{{ route('login') }}">Sign in</a></p>
</x-auth.layout>
