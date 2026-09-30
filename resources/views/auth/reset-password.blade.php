@extends('oxalis::layouts.oxalis')
@section('title','Set new password')
@section('content')
<h5 class="fw-bold text-center mb-4">Set a new password</h5>
<form action="{{ route('oxalis.password.reset') }}" method="POST">
  @csrf
  <input type="hidden" name="token" value="{{ $token }}">
  <div class="form-floating mb-3">
    <input type="password" name="password" class="form-control rounded-3 @error('password') is-invalid @enderror" placeholder="p" autofocus minlength="8">
    <label>New password <span class="text-secondary fw-normal">(min 8)</span></label>
    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="form-floating mb-4">
    <input type="password" name="password_confirmation" class="form-control rounded-3" placeholder="c">
    <label>Confirm new password</label>
  </div>
  <button class="btn btn-ox w-100">Set new password</button>
</form>
@endsection