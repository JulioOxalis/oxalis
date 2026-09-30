<x-auth.layout title="Verify your email" subtitle="Open the verification link we sent to your inbox.">
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="btn btn-primary btn-lg w-100" type="submit">Resend verification email</button>
    </form>
    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button class="btn btn-link w-100" type="submit">Sign out</button>
    </form>
</x-auth.layout>
