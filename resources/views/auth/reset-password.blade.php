<x-auth.layout title="Choose a new password">
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-auth.field name="email" label="Email address" type="email" autocomplete="email" :value="$request->email" />
        <x-auth.field name="password" label="New password" type="password" autocomplete="new-password" autofocus />
        <x-auth.field name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" />
        <button class="btn btn-primary btn-lg w-100" type="submit">Reset password</button>
    </form>
</x-auth.layout>
