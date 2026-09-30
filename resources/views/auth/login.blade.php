<x-auth.layout title="Welcome back" subtitle="Use your password or a passkey to continue.">
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <x-auth.field name="email" label="Email address" type="email" autocomplete="email webauthn" autofocus />
        <x-auth.field name="password" label="Password" type="password" autocomplete="current-password" />
        <div class="d-flex align-items-center justify-content-between mb-4">
            <label class="form-check"><input class="form-check-input" type="checkbox" name="remember"> <span class="form-check-label">Remember me</span></label>
            <a href="{{ route('password.request') }}">Forgot password?</a>
        </div>
        <button class="btn btn-primary btn-lg w-100" type="submit">Sign in</button>
    </form>

    <div class="auth-divider"><span>or</span></div>
    <button class="btn btn-outline-dark btn-lg w-100" type="button" data-passkey-login>Sign in with a passkey</button>
    <p class="auth-feedback" data-passkey-feedback aria-live="polite"></p>
    <p class="auth-secondary">New here? <a href="{{ route('register') }}">Create an account</a></p>
</x-auth.layout>
